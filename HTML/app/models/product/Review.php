<?php
/**
 * Review Model
 * UML: reviewId, product_id (FK), rating, comment, createdAt
 */
class Review {
    private string $reviewId;
    private string $productId;
    private string $customerId;
    private int    $rating;      // 1–5
    private string $comment;
    private string $createdAt;

    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Getters ──────────────────────────────
    public function getReviewId(): string    { return $this->reviewId; }
    public function getProductId(): string    { return $this->productId; }
    public function getCustomerId(): string   { return $this->customerId; }
    public function getRating(): int          { return $this->rating; }
    public function getComment(): string      { return $this->comment; }
    public function getCreatedAt(): string    { return $this->createdAt; }

    // ── Setters ──────────────────────────────
    public function setProductId(string $id): void     { $this->productId = $id; }
    public function setCustomerId(string $id): void    { $this->customerId = $id; }
    public function setRating(int $rating): void       { $this->rating = max(1, min(5, $rating)); }
    public function setComment(string $comment): void  { $this->comment = $comment; }

    /**
     * Get all reviews for a product with customer name
     */
    public function getByProductId(string $productId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.full_name 
             FROM reviews r
             JOIN customers c ON r.customer_id = c.customer_id
             JOIN users u ON c.user_id = u.user_id
             WHERE r.product_id = ?
             ORDER BY r.created_at DESC"
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Get average rating for a product
     */
    public function getAverageRating(string $productId): float {
        $stmt = $this->db->prepare(
            "SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = ?"
        );
        $stmt->execute([$productId]);
        $result = $stmt->fetch();
        return round((float)($result['avg_rating'] ?? 0), 1);
    }

    /**
     * Save new review
     */
    public function save(): void {
        $stmt = $this->db->prepare(
            "INSERT INTO reviews (review_id, product_id, customer_id, rating, comment, created_at)
             VALUES (UUID(), ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $this->productId,
            $this->customerId,
            $this->rating,
            $this->comment,
        ]);
    }
}
