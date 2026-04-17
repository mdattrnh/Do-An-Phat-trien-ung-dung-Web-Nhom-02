<?php
/**
 * ProductController
 * Handles product detail pages and reviews
 */
class ProductController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function detail($id) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $productModel = new Product();
            $product = $productModel->getById($id);

            if (!$product) {
                http_response_code(404);
                echo "Product not found";
                return;
            }

            // Get product images
            $stmt = $this->db->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, image_id ASC");
            $stmt->execute([$id]);
            $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Build image URLs
            $productImages = [];
            foreach ($images as $image) {
                $productImages[] = $productModel->buildImageUrl($image);
            }

            // Add images to product array
            $product['images'] = $productImages;
            $product['brand_name'] = $product['brand'];

            // Get variants
            $stmt = $this->db->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY size ASC");
            $stmt->execute([$id]);
            $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get reviews
            $stmt = $this->db->prepare("
                SELECT r.*, u.full_name
                FROM reviews r
                JOIN customers c ON r.customer_id = c.customer_id
                JOIN users u ON c.user_id = u.user_id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$id]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate average rating
            $avgRating = 0;
            if (count($reviews) > 0) {
                $totalRating = array_sum(array_column($reviews, 'rating'));
                $avgRating = $totalRating / count($reviews);
            }

            // Lấy sản phẩm gợi ý: cùng category, loại trừ sp hiện tại
            $stmtSugg = $this->db->prepare("
    SELECT p.product_id AS id,
           p.product_name AS name,
           p.base_price AS price,
           p.category,
           (SELECT image_url FROM product_images
            WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) AS image
    FROM products p
    WHERE p.product_id != :id
    ORDER BY RAND()
    LIMIT 4
");
$stmtSugg->execute([':id' => $id]);
$suggestedProducts = $stmtSugg->fetchAll(PDO::FETCH_ASSOC);

            // Build image URL cho từng sản phẩm gợi ý
            foreach ($suggestedProducts as &$sp) {
                $sp['image'] = $productModel->buildImageUrl($sp['image'] ?? '');
            }
            unset($sp);

            require BASE_PATH . '/app/views/productdetail.php';
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
    }

    public function submitReview($id) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not logged in']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $rating = $data['rating'] ?? 0;
        $comment = $data['comment'] ?? '';

        if ($rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid rating']);
            return;
        }

        try {
            // Get customer_id from user_id
            $stmtCustomer = $this->db->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
            $stmtCustomer->execute([$_SESSION['user_id']]);
            $customer = $stmtCustomer->fetch(PDO::FETCH_ASSOC);

            if (!$customer) {
                http_response_code(404);
                echo json_encode(['error' => 'Customer not found']);
                return;
            }

            $reviewModel = new Review();
            $reviewModel->setProductId($id);
            $reviewModel->setCustomerId($customer['customer_id']);
            $reviewModel->setRating($rating);
            $reviewModel->setComment($comment);
            $reviewModel->save();

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}