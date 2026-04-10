<?php
/**
 * AdminController
 * Quản lý dựa trên cấu trúc CSDL gốc (shop_db) - products, brands...
 */
require_once BASE_PATH . '/config/database.php';

class AdminController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function isAuthenticated() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    private function requireAuth() {
        if (!$this->isAuthenticated()) {
            http_response_code(302);
            header("Location: " . BASE_URL . "/admin/login");
            exit;
        }
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

    public function login() {
        if ($this->isAuthenticated()) {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Sync with documentation: admin@softedge.com / admin123
            if (($username === 'admin' || $username === 'admin@softedge.com') && $password === 'admin123') {
                $_SESSION['admin_logged_in'] = true;
                header("Location: " . BASE_URL . "/admin/dashboard");
                exit;
            } else {
                $error = 'Sai tài khoản hoặc mật khẩu (Sử dụng: admin@softedge.com / admin123)!';
            }
        }
        
        require BASE_PATH . '/app/views/admin/login.php';
    }

    public function logout() {
        session_destroy();
        header("Location: " . BASE_URL . "/admin/login");
        exit;
    }

    public function dashboard() {
        $this->requireAuth();
        
        $db = Database::getInstance()->getConnection();
        $stats = $this->fetchProcedureOne($db, "CALL sp_admin_get_dashboard_stats()");
        $productCount  = (int)($stats['product_count'] ?? 0);
        $orderCount    = (int)($stats['order_count'] ?? 0);
        $customerCount = (int)($stats['customer_count'] ?? 0);
        
        // Get total revenue
        $revenueStmt = $db->prepare(
            "SELECT SUM(final_amount) AS total_revenue FROM orders"
        );
        $revenueStmt->execute();
        $totalRevenue = (int)($revenueStmt->fetch()['total_revenue'] ?? 0);
        
        // Get total user accounts
        $userCountStmt = $db->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'");
        $userCountStmt->execute();
        $userCount = (int)($userCountStmt->fetch()['total'] ?? 0);
        
        require BASE_PATH . '/app/views/admin/dashboard.php';
    }

    public function products() {
        $this->requireAuth();
        
        $db = Database::getInstance()->getConnection();

        // Pagination logic
        $limit = 5; // Results per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        // Fetch brands for dropdown
        $brands = $this->fetchProcedureAll($db, "CALL sp_admin_get_brands()");

        // Get total count for pagination
        $countRow = $this->fetchProcedureOne($db, "CALL sp_admin_count_products()");
        $totalItems = (int)($countRow['total_items'] ?? 0);
        $totalPages = max(1, (int)ceil($totalItems / $limit));

        // Fetch products with their primary image and pagination
        $products = $this->fetchProcedureAll($db, "CALL sp_admin_get_products_paginated(?, ?)", [$limit, $offset]);
        
        require BASE_PATH . '/app/views/admin/products.php';
    }

    public function saveProduct() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $id = $_POST['id'] ?? null;
            $name = $_POST['product_name'] ?? '';
            $brandId = $_POST['brand_id'] ?? '';
            $category = $_POST['category'] ?? '';
            $price = (float)($_POST['base_price'] ?? 0);
            $gender = $_POST['gender_type'] ?? 'unisex';
            $status = $_POST['status'] ?? 'active';
            $desc = $_POST['description'] ?? '';
            $imageUrl = $_POST['image'] ?? '';
            
            // File Upload logic
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = BASE_PATH . '/public/uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = time() . '_' . basename($_FILES['image_file']['name']);
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
                    $imageUrl = 'public/uploads/products/' . $fileName;
                }
            }

            if (empty($id)) {
                $result = $this->fetchProcedureOne(
                    $db,
                    "CALL sp_admin_create_product(?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$brandId, $name, $desc, $price, $category, $gender, $status, $imageUrl, $_POST['color'] ?? 'Trắng']
                );
                $newId = $result['product_id'] ?? null;
            } else {
                $this->fetchProcedureOne(
                    $db,
                    "CALL sp_admin_update_product(?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$id, $brandId, $name, $desc, $price, $category, $gender, $status, $imageUrl]
                );
            }
            
            header("Location: " . BASE_URL . "/admin/products");
            exit;
        }
    }

    public function deleteProduct() {
        $this->requireAuth();
        if (isset($_GET['delete'])) {
            $db = Database::getInstance()->getConnection();
            $id = $_GET['delete']; // UUID is string

            try {
                $result = $this->fetchProcedureOne($db, "CALL sp_admin_delete_product(?)", [$id]);
                $_SESSION['admin_flash_success'] = $result['message'] ?? 'Xóa sản phẩm thành công.';
            } catch (PDOException $e) {
                $_SESSION['admin_flash_error'] = 'Xóa sản phẩm thất bại: ' . $e->getMessage();
            }
        }
        header("Location: " . BASE_URL . "/admin/products");
        exit;
    }

    public function users() {
        $this->requireAuth();
        
        $db = Database::getInstance()->getConnection();
        
        // Get all user accounts (customer role only)
        $stmt = $db->prepare(
            "SELECT u.user_id, u.full_name, u.email, u.created_at, 
                    (SELECT COUNT(*) FROM orders WHERE customer_id = c.customer_id) AS order_count
             FROM users u
             LEFT JOIN customers c ON u.user_id = c.user_id
             WHERE u.role = 'customer'
             ORDER BY u.created_at DESC"
        );
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        // Get total user count
        $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'");
        $countStmt->execute();
        $userCount = $countStmt->fetch()['total'] ?? 0;
        
        // Get total revenue
        $revenueStmt = $db->prepare(
            "SELECT SUM(final_amount) AS total_revenue FROM orders"
        );
        $revenueStmt->execute();
        $totalRevenue = (int)($revenueStmt->fetch()['total_revenue'] ?? 0);
        
        require BASE_PATH . '/app/views/admin/users.php';
    }

    public function deleteUser() {
        $this->requireAuth();
        if (isset($_GET['delete'])) {
            $db = Database::getInstance()->getConnection();
            $userId = $_GET['delete'];

            try {
                $db->beginTransaction();

                // First, get the customer_id for this user
                $customerStmt = $db->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
                $customerStmt->execute([$userId]);
                $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

                if ($customer) {
                    $customerId = $customer['customer_id'];

                    // Delete addresses first (orders reference addresses with SET NULL)
                    $db->prepare("DELETE FROM addresses WHERE customer_id = ?")->execute([$customerId]);

                    // Delete order items, shipments, payments (CASCADE from orders)
                    $db->prepare("DELETE FROM order_items WHERE order_id IN (SELECT order_id FROM orders WHERE customer_id = ?)")->execute([$customerId]);
                    $db->prepare("DELETE FROM shipments WHERE order_id IN (SELECT order_id FROM orders WHERE customer_id = ?)")->execute([$customerId]);
                    $db->prepare("DELETE FROM payments WHERE order_id IN (SELECT order_id FROM orders WHERE customer_id = ?)")->execute([$customerId]);

                    // Delete orders
                    $db->prepare("DELETE FROM orders WHERE customer_id = ?")->execute([$customerId]);
                }

                // Finally, delete the user (CASCADE will handle customers table)
                $stmt = $db->prepare("DELETE FROM users WHERE user_id = ? AND role = 'customer'");
                $stmt->execute([$userId]);

                $db->commit();
                $_SESSION['admin_flash_success'] = 'Xóa tài khoản thành công.';
            } catch (PDOException $e) {
                $db->rollBack();
                $_SESSION['admin_flash_error'] = 'Xóa tài khoản thất bại: ' . $e->getMessage();
            }
        }
        header("Location: " . BASE_URL . "/admin/users");
        exit;
    }
}

