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

    private function ensureCategoriesTable(PDO $db): void {
        $db->exec(
            "CREATE TABLE IF NOT EXISTS categories (
                category_id VARCHAR(36) NOT NULL PRIMARY KEY,
                category_name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                size_mode ENUM('default','numeric_38_42') NOT NULL DEFAULT 'default'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function loadDefaultCategories(PDO $db): void {
        $count = (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        if ($count > 0) {
            return;
        }

        $defaultCategories = [
            ['Hoodie', 'hoodie', 'default'],
            ['T-Shirt', 'tshirt', 'default'],
            ['Cargo', 'cargo', 'default'],
            ['Jacket', 'jacket', 'default'],
            ['Shorts', 'shorts', 'default'],
            ['Shoes', 'shoes', 'numeric_38_42'],
            ['Bottoms', 'bottoms', 'numeric_38_42'],
        ];

        $insert = $db->prepare("INSERT INTO categories (category_id, category_name, slug, size_mode) VALUES (UUID(), ?, ?, ?)");
        foreach ($defaultCategories as [$name, $slug, $mode]) {
            $insert->execute([$name, $slug, $mode]);
        }

        $fallback = $db->query("SELECT DISTINCT category FROM products WHERE category NOT IN ('hoodie','tshirt','cargo','jacket','shorts','shoes','bottoms')")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($fallback as $slug) {
            $name = ucfirst(str_replace(['-', '_'], ' ', $slug));
            $insert->execute([$name, $slug, 'default']);
        }
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
        $this->ensureCategoriesTable($db);
        $this->loadDefaultCategories($db);

        // Get category list for the product form
        $categoryStmt = $db->prepare("SELECT category_id, category_name, slug, size_mode FROM categories ORDER BY category_name ASC");
        $categoryStmt->execute();
        $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

        // Pagination logic
        $limit = 5; // Results per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        // Get total count for pagination
        $countRow = $this->fetchProcedureOne($db, "CALL sp_admin_count_products()");
        $totalItems = (int)($countRow['total_items'] ?? 0);
        $totalPages = max(1, (int)ceil($totalItems / $limit));

        // Fetch products with their primary image and size list
        $productsStmt = $db->prepare(
            "SELECT p.*, 
                    (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) AS image,
                    GROUP_CONCAT(DISTINCT pv.size ORDER BY FIELD(pv.size, 'XS','S','M','L','XL','XXL','38','39','40','41','42') SEPARATOR ', ') AS sizes
             FROM products p
             LEFT JOIN product_variants pv ON pv.product_id = p.product_id
             GROUP BY p.product_id
             ORDER BY p.product_name ASC
             LIMIT :limit OFFSET :offset"
        );
        $productsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $productsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $productsStmt->execute();
        $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        require BASE_PATH . '/app/views/admin/products.php';
    }

    public function saveProduct() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $id = $_POST['id'] ?? null;
            $name = trim($_POST['product_name'] ?? '');
            $brandId = $_POST['brand_id'] ?? '';
            $category = $_POST['category'] ?? '';
            $price = (float)($_POST['base_price'] ?? 0);
            $gender = $_POST['gender_type'] ?? 'unisex';
            $status = $_POST['status'] ?? 'active';
            $desc = trim($_POST['description'] ?? '');
            $imageUrl = trim($_POST['image'] ?? '');
            $sizes = $_POST['sizes'] ?? [];
            if (!is_array($sizes)) {
                $sizes = array_filter(array_map('trim', explode(',', $sizes)));
            }
            $candidateSizes = array_map('strtoupper', $sizes);
            $validSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '38', '39', '40', '41', '42'];
            $sizes = array_values(array_filter($candidateSizes, function ($size) use ($validSizes) {
                return in_array($size, $validSizes, true);
            }));

            // Get category size_mode from database
            $categoryStmt = $db->prepare("SELECT size_mode FROM categories WHERE slug = ?");
            $categoryStmt->execute([$category]);
            $categoryRow = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            $sizeMode = $categoryRow['size_mode'] ?? 'default';

            if ($sizeMode === 'numeric_38_42') {
                $sizes = array_values(array_filter($sizes, function ($size) {
                    return in_array($size, ['38', '39', '40', '41', '42'], true);
                }));
                // Only set default when creating new product
                if (empty($sizes) && empty($id)) {
                    $sizes = ['38', '39', '40', '41', '42'];
                }
            } else {
                $sizes = array_values(array_filter($sizes, function ($size) {
                    return in_array($size, ['XS', 'S', 'M', 'L', 'XL', 'XXL'], true);
                }));
                // Only set default when creating new product
                if (empty($sizes) && empty($id)) {
                    $sizes = ['S', 'M', 'L', 'XL'];
                }
            }

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
                if (empty($brandId)) {
                    $defaultBrand = $this->fetchProcedureOne($db, "SELECT brand_id FROM brands LIMIT 1");
                    $brandId = $defaultBrand['brand_id'] ?? '';
                }
                $productId = $db->query("SELECT UUID() AS uuid")->fetchColumn();
                $insert = $db->prepare("INSERT INTO products (product_id, brand_id, product_name, description, base_price, category, gender_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $insert->execute([$productId, $brandId, $name, $desc, $price, $category, $gender, $status]);
            } else {
                $productId = $id;
                $update = $db->prepare("UPDATE products SET product_name = ?, description = ?, base_price = ?, category = ?, gender_type = ?, status = ? WHERE product_id = ?");
                $update->execute([$name, $desc, $price, $category, $gender, $status, $productId]);
            }

            if (!empty($imageUrl)) {
                $stmt = $db->prepare("SELECT 1 FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
                $stmt->execute([$productId]);
                if ($stmt->fetch()) {
                    $updateImage = $db->prepare("UPDATE product_images SET image_url = ? WHERE product_id = ? AND is_primary = 1");
                    $updateImage->execute([$imageUrl, $productId]);
                } else {
                    $insertImage = $db->prepare("INSERT INTO product_images (image_id, product_id, image_url, is_primary) VALUES (UUID(), ?, ?, 1)");
                    $insertImage->execute([$productId, $imageUrl]);
                }
            }

            if (empty($id)) {
                $insertVariant = $db->prepare("INSERT INTO product_variants (variant_id, product_id, size, stock_quantity, price_adjustment) VALUES (UUID(), ?, ?, 50, 0)");
                foreach ($sizes as $size) {
                    $insertVariant->execute([$productId, $size]);
                }
            } else {
                $stmt = $db->prepare("SELECT size FROM product_variants WHERE product_id = ?");
                $stmt->execute([$productId]);
                $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $existingVariants = array_map(function($v) { return trim((string)$v['size']); }, $variants);
                $sizes = array_map('trim', $sizes);

                $newSizes = array_diff($sizes, $existingVariants);
                $insertVariant = $db->prepare("INSERT INTO product_variants (variant_id, product_id, size, stock_quantity, price_adjustment) VALUES (UUID(), ?, ?, 50, 0)");
                foreach ($newSizes as $size) {
                    $insertVariant->execute([$productId, $size]);
                }

                $removedSizes = array_diff($existingVariants, $sizes);
                $checkOrder = $db->prepare(
                    "SELECT COUNT(*) FROM order_items oi 
                     JOIN product_variants pv ON oi.variant_id = pv.variant_id 
                     WHERE pv.product_id = ? AND pv.size = ?"
                );
                $deleteVariant = $db->prepare("DELETE FROM product_variants WHERE product_id = ? AND size = ?");
                foreach ($removedSizes as $size) {
                    $checkOrder->execute([$productId, $size]);
                    if ((int)$checkOrder->fetchColumn() === 0) {
                        $deleteVariant->execute([$productId, $size]);
                    }
                }
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

    public function categories() {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $this->ensureCategoriesTable($db);
        $this->loadDefaultCategories($db);

        $stmt = $db->prepare("SELECT category_id, category_name, slug, size_mode FROM categories ORDER BY category_name ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require BASE_PATH . '/app/views/admin/categories.php';
    }

    public function saveCategory() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $this->ensureCategoriesTable($db);

            $id = trim($_POST['category_id'] ?? '');
            $name = trim($_POST['category_name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $sizeMode = in_array($_POST['size_mode'] ?? 'default', ['default', 'numeric_38_42'], true) ? $_POST['size_mode'] : 'default';

            if ($name === '') {
                $_SESSION['admin_flash_error'] = 'Tên danh mục không được để trống.';
                header("Location: " . BASE_URL . "/admin/categories");
                exit;
            }

            if ($slug === '') {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            }

            if ($id === '') {
                $stmt = $db->prepare("INSERT INTO categories (category_id, category_name, slug, size_mode) VALUES (UUID(), ?, ?, ?)");
                $stmt->execute([$name, $slug, $sizeMode]);
                $_SESSION['admin_flash_success'] = 'Thêm danh mục mới thành công.';
            } else {
                $stmt = $db->prepare("UPDATE categories SET category_name = ?, slug = ?, size_mode = ? WHERE category_id = ?");
                $stmt->execute([$name, $slug, $sizeMode, $id]);
                $_SESSION['admin_flash_success'] = 'Cập nhật danh mục thành công.';
            }
        }

        header("Location: " . BASE_URL . "/admin/categories");
        exit;
    }

    public function deleteCategory() {
        $this->requireAuth();
        if (isset($_GET['delete'])) {
            $db = Database::getInstance()->getConnection();
            $categoryId = $_GET['delete'];

            $categoryStmt = $db->prepare("SELECT slug FROM categories WHERE category_id = ? LIMIT 1");
            $categoryStmt->execute([$categoryId]);
            $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);

            if ($category) {
                $productStmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category = ?");
                $productStmt->execute([$category['slug']]);
                if ((int)$productStmt->fetchColumn() > 0) {
                    $_SESSION['admin_flash_error'] = 'Không thể xóa danh mục đang có sản phẩm.';
                    header("Location: " . BASE_URL . "/admin/categories");
                    exit;
                }

                $deleteStmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
                $deleteStmt->execute([$categoryId]);
                $_SESSION['admin_flash_success'] = 'Xóa danh mục thành công.';
            }
        }

        header("Location: " . BASE_URL . "/admin/categories");
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

    public function orders() {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        // Pagination
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Get total orders count
        $countStmt = $db->query("SELECT COUNT(*) AS total FROM orders");
        $countRow = $countStmt->fetch(PDO::FETCH_ASSOC);
        $totalOrders = (int)$countRow['total'];
        $totalPages = max(1, ceil($totalOrders / $limit));

        // Fetch orders with customer info
        $ordersStmt = $db->prepare(
            "SELECT o.*, u.full_name, u.phone, u.email,
                    GROUP_CONCAT(DISTINCT p.product_name SEPARATOR ', ') AS product_names,
                    SUM(oi.quantity) AS total_items
             FROM orders o
             LEFT JOIN customers c ON o.customer_id = c.customer_id
             LEFT JOIN users u ON c.user_id = u.user_id
             LEFT JOIN order_items oi ON o.order_id = oi.order_id
             LEFT JOIN product_variants pv ON oi.variant_id = pv.variant_id
             LEFT JOIN products p ON pv.product_id = p.product_id
             GROUP BY o.order_id
             ORDER BY o.order_date DESC
             LIMIT :limit OFFSET :offset"
        );
        $ordersStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $ordersStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $ordersStmt->execute();
        $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

        require BASE_PATH . '/app/views/admin/orders.php';
    }

    public function updateOrderStatus() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $orderId = $_POST['order_id'] ?? '';
            $orderStatus = $_POST['order_status'] ?? '';
            $paymentStatus = $_POST['payment_status'] ?? '';

            $validOrderStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
            $validPaymentStatuses = ['pending', 'paid', 'failed', 'refunded'];

            if (!in_array($orderStatus, $validOrderStatuses) || !in_array($paymentStatus, $validPaymentStatuses)) {
                $_SESSION['admin_flash_error'] = 'Trạng thái không hợp lệ.';
                header("Location: " . BASE_URL . "/admin/orders");
                exit;
            }

            try {
                $stmt = $db->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE order_id = ?");
                $stmt->execute([$orderStatus, $paymentStatus, $orderId]);
                $_SESSION['admin_flash_success'] = 'Cập nhật trạng thái đơn hàng thành công.';
            } catch (PDOException $e) {
                $_SESSION['admin_flash_error'] = 'Cập nhật thất bại: ' . $e->getMessage();
            }
        }
        header("Location: " . BASE_URL . "/admin/orders");
        exit;
    }

    public function deleteOrder() {
        $this->requireAuth();
        if (isset($_GET['delete'])) {
            $db = Database::getInstance()->getConnection();
            $orderId = $_GET['delete'];
            try {
                $db->beginTransaction();

                // Restore stock quantities for deleted order items
                $restoreStmt = $db->prepare(
                    "UPDATE product_variants pv
                     JOIN order_items oi ON pv.variant_id = oi.variant_id
                     SET pv.stock_quantity = pv.stock_quantity + oi.quantity
                     WHERE oi.order_id = ?"
                );
                $restoreStmt->execute([$orderId]);

                $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
                $db->prepare("DELETE FROM payments WHERE order_id = ?")->execute([$orderId]);
                $db->prepare("DELETE FROM shipments WHERE order_id = ?")->execute([$orderId]);
                $db->prepare("DELETE FROM orders WHERE order_id = ?")->execute([$orderId]);

                $db->commit();
                $_SESSION['admin_flash_success'] = 'Xóa đơn hàng thành công.';
            } catch (PDOException $e) {
                $db->rollBack();
                $_SESSION['admin_flash_error'] = 'Xóa đơn hàng thất bại: ' . $e->getMessage();
            }
        }
        header("Location: " . BASE_URL . "/admin/orders");
        exit;
    }
}

