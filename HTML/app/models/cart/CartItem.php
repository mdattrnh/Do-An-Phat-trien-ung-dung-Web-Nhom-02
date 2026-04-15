<?php
/**
 * CartItem Model
 * UML: cartItemId, cartId (FK), variantId (FK), quantity, unitPrice
 */
class CartItem {
    private string $cartItemId;
    private string $cartId;
    private string $variantId;
    private int    $quantity;
    private float  $unitPrice;

    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Getters ──────────────────────────────
    public function getCartItemId(): string { return $this->cartItemId; }
    public function getCartId(): string      { return $this->cartId; }
    public function getVariantId(): string   { return $this->variantId; }
    public function getQuantity(): int       { return $this->quantity; }
    public function getUnitPrice(): float    { return $this->unitPrice; }

    // ── Setters ──────────────────────────────
    public function setCartId(string $id): void      { $this->cartId = $id; }
    public function setVariantId(string $id): void   { $this->variantId = $id; }
    public function setQuantity(int $qty): void       { $this->quantity = $qty; }
    public function setUnitPrice(float $price): void  { $this->unitPrice = $price; }

    /**
     * Get all items for a given cart
     */
    public function getByCartId(string $cartId): array {
        $stmt = $this->db->prepare(
            "SELECT ci.*, pv.size, pv.color, p.product_id, p.product_name,
                    (p.base_price + pv.price_adjustment) AS unit_price,
                    (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_url
             FROM cart_items ci
             JOIN product_variants pv ON ci.variant_id = pv.variant_id
             JOIN products p ON pv.product_id = p.product_id
             WHERE ci.cart_id = ?"
        );
        $stmt->execute([$cartId]);
        return $stmt->fetchAll();
    }

    /**
     * Update quantity of a cart item
     */
    public function updateQuantity(int $quantity): void {
        $this->quantity = $quantity;
        $stmt = $this->db->prepare(
            "UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND variant_id = ?"
        );
        $stmt->execute([$quantity, $this->cartId, $this->variantId]);
    }

    /**
     * Delete this cart item
     */
    public function delete(): void {
        $stmt = $this->db->prepare(
            "DELETE FROM cart_items WHERE cart_id = ? AND variant_id = ?"
        );
        $stmt->execute([$this->cartId, $this->variantId]);
    }
}
