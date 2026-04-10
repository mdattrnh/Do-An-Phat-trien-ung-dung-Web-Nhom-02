<?php
/**
 * ApiController
 * Serves JSON data to the frontend
 */
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/product/Product.php';
require_once BASE_PATH . '/app/models/cart/Cart.php';
require_once BASE_PATH . '/app/models/cart/CartItem.php';

class ApiController {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function getCustomerId(string $userId): ?string {
        $stmt = $this->db->prepare("SELECT customer_id FROM customers WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ? $row['customer_id'] : null;
    }

    public function getCart() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'User not logged in']);
            return;
        }
        $customerId = $this->getCustomerId($_SESSION['user_id']);
        if (!$customerId) {
            http_response_code(404);
            echo json_encode(['error' => 'Customer not found']);
            return;
        }
        try {
            $cartModel = new Cart();
            $cart = $cartModel->getOrCreateForCustomer($customerId);
            $cartItemModel = new CartItem();
            $items = $cartItemModel->getByCartId($cart['cart_id']);
            // Format items to include product_id and size
            $formattedItems = array_map(function($item) {
                return [
                    'product_id' => $item['product_id'] ?? '',
                    'size' => $item['size'],
                    'color' => $item['color'],
                    'product_name' => $item['product_name'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'image_url' => $item['image_url'] ?? null
                ];
            }, $items);
            echo json_encode(['cart' => $cart, 'items' => $formattedItems]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function addToCart() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'User not logged in']);
            return;
        }
        $customerId = $this->getCustomerId($_SESSION['user_id']);
        if (!$customerId) {
            http_response_code(404);
            echo json_encode(['error' => 'Customer not found']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['product_id'] ?? '';
        $size = $data['size'] ?? '';
        $quantity = $data['quantity'] ?? 1;
        if (empty($productId) || empty($size)) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID and size required']);
            return;
        }
        try {
            // Find variant_id
            $stmt = $this->db->prepare("SELECT variant_id FROM product_variants WHERE product_id = ? AND size = ? LIMIT 1");
            $stmt->execute([$productId, $size]);
            $variant = $stmt->fetch();
            if (!$variant) {
                http_response_code(404);
                echo json_encode(['error' => 'Variant not found']);
                return;
            }
            $variantId = $variant['variant_id'];

            $cartModel = new Cart();
            $cart = $cartModel->getOrCreateForCustomer($customerId);
            $cartModel->addItem($variantId, $quantity);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function updateCartItem() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'User not logged in']);
            return;
        }
        $customerId = $this->getCustomerId($_SESSION['user_id']);
        if (!$customerId) {
            http_response_code(404);
            echo json_encode(['error' => 'Customer not found']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['product_id'] ?? '';
        $size = $data['size'] ?? '';
        $quantity = $data['quantity'] ?? 0;
        if (empty($productId) || empty($size)) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID and size required']);
            return;
        }
        try {
            // Find variant_id
            $stmt = $this->db->prepare("SELECT variant_id FROM product_variants WHERE product_id = ? AND size = ? LIMIT 1");
            $stmt->execute([$productId, $size]);
            $variant = $stmt->fetch();
            if (!$variant) {
                http_response_code(404);
                echo json_encode(['error' => 'Variant not found']);
                return;
            }
            $variantId = $variant['variant_id'];

            $cartModel = new Cart();
            $cart = $cartModel->getOrCreateForCustomer($customerId);
            if ($quantity <= 0) {
                $cartModel->removeItem($variantId);
            } else {
                $cartModel->updateItemQuantity($variantId, $quantity);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function removeFromCart() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'User not logged in']);
            return;
        }
        $customerId = $this->getCustomerId($_SESSION['user_id']);
        if (!$customerId) {
            http_response_code(404);
            echo json_encode(['error' => 'Customer not found']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['product_id'] ?? '';
        $size = $data['size'] ?? '';
        if (empty($productId) || empty($size)) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID and size required']);
            return;
        }
        try {
            // Find variant_id
            $stmt = $this->db->prepare("SELECT variant_id FROM product_variants WHERE product_id = ? AND size = ? LIMIT 1");
            $stmt->execute([$productId, $size]);
            $variant = $stmt->fetch();
            if (!$variant) {
                http_response_code(404);
                echo json_encode(['error' => 'Variant not found']);
                return;
            }
            $variantId = $variant['variant_id'];

            $cartModel = new Cart();
            $cart = $cartModel->getOrCreateForCustomer($customerId);
            $cartModel->removeItem($variantId);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    public function getProducts() {
        header('Content-Type: application/json');
        try {
            $model = new Product();
            $data = $model->getAllForSpa();
            echo json_encode($data);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
