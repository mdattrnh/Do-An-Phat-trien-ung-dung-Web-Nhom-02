<?php
require_once __DIR__ . '/User.php';

/**
 * Customer Model
 * UML: customerId, gender, dateOfBirth, loyaltyPoints, defaultAddress
 * Methods: addToCart, placeOrder, search, viewOrderHistory, writeReview
 */
class Customer extends User {
    private string $customerId;
    private string $gender;
    private ?string $dateOfBirth;
    private int $loyaltyPoints;
    private ?string $defaultAddress;

    public function __construct() {
        parent::__construct();
        $this->role = 'customer';
    }

    // ── Getters ──────────────────────────────
    public function getCustomerId(): string      { return $this->customerId; }
    public function getGender(): string           { return $this->gender; }
    public function getDateOfBirth(): ?string     { return $this->dateOfBirth; }
    public function getLoyaltyPoints(): int       { return $this->loyaltyPoints; }
    public function getDefaultAddress(): ?string  { return $this->defaultAddress; }

    // ── Setters ──────────────────────────────
    public function setGender(string $gender): void              { $this->gender = $gender; }
    public function setDateOfBirth(?string $dob): void           { $this->dateOfBirth = $dob; }
    public function setDefaultAddress(?string $addr): void       { $this->defaultAddress = $addr; }

    /**
     * Register new customer
     */
    public function register(string $fullName, string $email, string $password, string $gender): bool {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $userId = $this->generateUUID(); // We need a UUID for the user

        // 1. Insert into 'users' table
        $stmtUser = $this->db->prepare(
            "INSERT INTO users (user_id, full_name, email, password, role, status, created_at)
             VALUES (?, ?, ?, ?, 'customer', 'active', NOW())"
        );
        $success = $stmtUser->execute([$userId, $fullName, $email, $hash]);

        if ($success) {
            // 2. The trigger 'trg_after_insert_user_create_customer' already created a customer record.
            // We just need to update the gender.
            $stmtCust = $this->db->prepare("UPDATE customers SET gender = ? WHERE user_id = ?");
            $stmtCust->execute([$gender, $userId]);
            return true;
        }
        
        return false;
    }

    private function generateUUID(): string {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Add product variant to customer's cart
     */
    public function addToCart(string $variantId, int $quantity): void {
        $stmt = $this->db->prepare(
            "INSERT INTO cart_items (cart_id, variant_id, quantity)
             VALUES ((SELECT cart_id FROM carts WHERE customer_id = ?), ?, ?)
             ON DUPLICATE KEY UPDATE quantity = quantity + ?"
        );
        $stmt->execute([$this->customerId, $variantId, $quantity, $quantity]);
    }

    /**
     * Place a new order from current cart
     */
    public function placeOrder(string $addressId, ?string $promotionId = null): void {
        // Logic handled via OrderController
    }

    /**
     * Search products by keyword
     */
    public function search(string $keyword, int $limit = 20): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE product_name LIKE ? OR description LIKE ? LIMIT ?"
        );
        $stmt->execute(["%$keyword%", "%$keyword%", $limit]);
        return $stmt->fetchAll();
    }

    /**
     * View all orders for this customer
     */
    public function viewOrderHistory(): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC"
        );
        $stmt->execute([$this->customerId]);
        return $stmt->fetchAll();
    }

    /**
     * Write a product review
     */
    public function writeReview(string $productId, int $rating, string $comment): void {
        $stmt = $this->db->prepare(
            "INSERT INTO reviews (customer_id, product_id, rating, comment, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$this->customerId, $productId, $rating, $comment]);
    }
}
