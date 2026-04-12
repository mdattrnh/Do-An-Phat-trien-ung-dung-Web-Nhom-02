<?php
/**
 * OrderController
 * Xử lý đơn hàng từ Frontend (Checkout SPA)
 */
require_once BASE_PATH . '/config/database.php';

class OrderController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Hàm sinh UUID v4 cho PHP (để insert và lấy ID sử dụng ngay lập tức)
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }


    private function fetchProcedureAll(PDO $db, string $sql, array $params = []): array {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        while ($stmt->nextRowset()) {}
        $stmt->closeCursor();
        return $rows;
    }

    private function fetchProcedureOne(PDO $db, string $sql, array $params = []): ?array {
        $rows = $this->fetchProcedureAll($db, $sql, $params);
        return $rows[0] ?? null;
    }

    public function processCheckout() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['cart'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Giỏ hàng trống']);
            return;
        }

        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            $phone = $input['phone'] ?? '0999999999';
            $name = $input['name'] ?? 'Khách Mua Lẻ';
            $address = $input['address'] ?? 'Tại cửa hàng';
            
            // 1. TÌM HOẶC TẠO USER/CUSTOMER bằng stored procedure
            $customerRow = $this->fetchProcedureOne(
                $db,
                "CALL sp_find_or_create_customer_by_phone(?, ?)",
                [$phone, $name]
            );
            $userId = $customerRow['user_id'] ?? null;
            $customerId = $customerRow['customer_id'] ?? null;

            if (!$userId || !$customerId) {
                throw new Exception('Không thể khởi tạo khách hàng cho đơn hàng.');
            }
            
            // 2. TẠO ADDRESS mới cho đơn này
            $addressId = $this->generateUUID();
            $this->fetchProcedureOne(
                $db,
                "CALL sp_create_address(?, ?, ?, ?, ?, ?)",
                [$addressId, $customerId, $name, $phone, $address, 'Chưa xác định']
            );
            
            // 3. TÍNH TỔNG TIỀN ĐƠN HÀNG
            $totalAmount = 0;
            foreach ($input['cart'] as $item) {
                $totalAmount += ($item['price'] * $item['quantity']);
            }
            
            // 4. TẠO ĐƠN HÀNG MỚI (ORDERS)
            $orderId = $this->generateUUID();
            $this->fetchProcedureOne(
                $db,
                "CALL sp_create_order(?, ?, ?, ?, ?)",
                [$orderId, $customerId, $addressId, $totalAmount, 0]
            );
            
            // 5. THÊM CHI TIẾT ĐƠN HÀNG (ORDER_ITEMS)
            foreach ($input['cart'] as $item) {
                $variantRow = $this->fetchProcedureOne(
                    $db,
                    "CALL sp_find_variant_for_checkout(?, ?)",
                    [$item['id'], $item['size'] ?? 'M']
                );
                $variantId = $variantRow['variant_id'] ?? null;

                if ($variantId) {
                    // Trigger DB vẫn kiểm tra tồn kho và tự trừ kho
                    $this->fetchProcedureOne(
                        $db,
                        "CALL sp_create_order_item(?, ?, ?, ?, ?)",
                        [$this->generateUUID(), $orderId, $variantId, (int)$item['quantity'], (float)$item['price']]
                    );
                }
            }
            
            $db->commit();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Đặt hàng thành công!',
                'order_id' => $orderId
            ]);
            
        } catch (PDOException $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Lỗi Database: ' . $e->getMessage()]);
        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function checkout() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/register");
            exit;
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Get customer card
        $stmt = $db->prepare("SELECT customer_id FROM customers WHERE user_id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $customer = $stmt->fetch();
        
        if (!$customer) {
            $_SESSION['error'] = 'Không tìm thấy customer record';
            header("Location: " . BASE_URL . "/");
            exit;
        }
        
        $customerId = $customer['customer_id'];
        
        // Get cart items
        $stmt = $db->prepare(
            "SELECT ci.*, pv.size, p.product_name,
                    (p.base_price + pv.price_adjustment) AS unit_price
             FROM carts c
             JOIN cart_items ci ON c.cart_id = ci.cart_id
             JOIN product_variants pv ON ci.variant_id = pv.variant_id
             JOIN products p ON pv.product_id = p.product_id
             WHERE c.customer_id = ?"
        );
        $stmt->execute([$customerId]);
        $cartItems = $stmt->fetchAll();
        
        // Calculate total
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += ($item['unit_price'] * $item['quantity']);
        }
        
        // Get user info
        $stmt = $db->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        // Get address
        $stmt = $db->prepare(
            "SELECT address_id, receiver_name, phone, street_address, city 
             FROM addresses WHERE customer_id = ? LIMIT 1"
        );
        $stmt->execute([$customerId]);
        $address = $stmt->fetch();
        
        require BASE_PATH . '/app/views/pay.php';
    }

    public function processPayment() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not logged in']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $db = Database::getInstance()->getConnection();
        
        try {
            // Get customer
            $stmt = $db->prepare("SELECT customer_id FROM customers WHERE user_id = ? LIMIT 1");
            $stmt->execute([$_SESSION['user_id']]);
            $customer = $stmt->fetch();
            
            if (!$customer) {
                throw new Exception('Customer not found');
            }
            
            $customerId = $customer['customer_id'];
            
            // Start transaction
            $db->beginTransaction();
            
            // Get cart total
            $stmt = $db->prepare(
                "SELECT SUM((p.base_price + pv.price_adjustment) * ci.quantity) AS total
                 FROM carts c
                 JOIN cart_items ci ON c.cart_id = ci.cart_id
                 JOIN product_variants pv ON ci.variant_id = pv.variant_id
                 JOIN products p ON pv.product_id = p.product_id
                 WHERE c.customer_id = ?"
            );
            $stmt->execute([$customerId]);
            $result = $stmt->fetch();
            $totalAmount = (float)($result['total'] ?? 0);
            
            // Get or create address
            $addressId = $data['address_id'] ?? null;
            if (!$addressId) {
                $addressId = $this->generateUUID();
                $stmtAddr = $db->prepare(
                    "INSERT INTO addresses (address_id, customer_id, receiver_name, phone, street_address, zip_code)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmtAddr->execute([
                    $addressId,
                    $customerId,
                    $data['name'] ?? '',
                    $data['phone'] ?? '',
                    $data['address'] ?? '',
                    '000000'
                ]);
            }
            
            // Create order
            $orderId = $this->generateUUID();
            $stmtOrder = $db->prepare(
                "INSERT INTO orders (order_id, customer_id, address_id, order_date, total_amount, final_amount, order_status, payment_status)
                 VALUES (?, ?, ?, NOW(), ?, ?, 'confirmed', 'paid')"
            );
            $stmtOrder->execute([
                $orderId,
                $customerId,
                $addressId,
                $totalAmount,
                $totalAmount
            ]);
            
            // Copy cart items to order items
            $stmtGetCart = $db->prepare(
                "SELECT ci.variant_id, ci.quantity, (p.base_price + pv.price_adjustment) AS unit_price
                 FROM carts c
                 JOIN cart_items ci ON c.cart_id = ci.cart_id
                 JOIN product_variants pv ON ci.variant_id = pv.variant_id
                 JOIN products p ON pv.product_id = p.product_id
                 WHERE c.customer_id = ?"
            );
            $stmtGetCart->execute([$customerId]);
            $cartItems = $stmtGetCart->fetchAll();
            
            foreach ($cartItems as $item) {
                $stmtOrderItem = $db->prepare(
                    "INSERT INTO order_items (order_item_id, order_id, variant_id, quantity, unit_price)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmtOrderItem->execute([
                    $this->generateUUID(),
                    $orderId,
                    $item['variant_id'],
                    $item['quantity'],
                    $item['unit_price']
                ]);
            }
            
            // Create payment record
            $stmtPayment = $db->prepare(
                "INSERT INTO payments (payment_id, order_id, payment_method, payment_date, amount, payment_status)
                 VALUES (?, ?, 'online', NOW(), ?, 'paid')"
            );
            $stmtPayment->execute([
                $this->generateUUID(),
                $orderId,
                $totalAmount
            ]);
            
            // Clear cart
            $stmtCart = $db->prepare("SELECT cart_id FROM carts WHERE customer_id = ?");
            $stmtCart->execute([$customerId]);
            $cartRow = $stmtCart->fetch();
            if ($cartRow) {
                $db->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cartRow['cart_id']]);
                $db->prepare("UPDATE carts SET total_amount = 0 WHERE cart_id = ?")->execute([$cartRow['cart_id']]);
            }
            
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Thanh toán thành công!',
                'order_id' => $orderId
            ]);
            
        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
