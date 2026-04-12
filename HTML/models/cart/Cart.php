<?php
/**
 * Cart Model
 * UML: cartId, customerId, totalAmount
 * Methods: addItem, removeItem, calculateTotal
 */
class Cart {
    private string $cartId;
    private string $customerId;
    private float  $totalAmount;

    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Getters ──────────────────────────────
    public function getCartId(): string      { return $this->cartId; }
    public function getCustomerId(): string   { return $this->customerId; }
    public function getTotalAmount(): float   { return $this->totalAmount; }

    // ── Setters ──────────────────────────────
    public function setCustomerId(string $id): void { $this->customerId = $id; }

    /**
     * Get or create cart for a customer
     */
    public function getOrCreateForCustomer(string $customerId): array {
        $stmt = $this->db->prepare("SELECT * FROM carts WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $cart = $stmt->fetch();

        if (!$cart) {
            $this->db->prepare(
                "INSERT INTO carts (cart_id, customer_id, total_amount) VALUES (UUID(), ?, 0)"
            )->execute([$customerId]);
            $stmt->execute([$customerId]);
            $cart = $stmt->fetch();
        }
        $this->cartId = $cart['cart_id'];
        return $cart;
    }

    /**
     * Add item to cart (or increment quantity)
     */
    public function addItem(string $variantId, int $quantity): void {
        $stmt = $this->db->prepare(
            "INSERT INTO cart_items (cart_id, variant_id, quantity)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = quantity + ?"
        );
        $stmt->execute([$this->cartId, $variantId, $quantity, $quantity]);
        $this->calculateTotal();
    }

    /**
     * Remove item from cart
     */
    public function removeItem(string $variantId): void {
        $stmt = $this->db->prepare(
            "DELETE FROM cart_items WHERE cart_id = ? AND variant_id = ?"
        );
        $stmt->execute([$this->cartId, $variantId]);
        $this->calculateTotal();
    }

    /**
     * Update quantity of an item in cart
     */
    public function updateItemQuantity(string $variantId, int $quantity): void {
        if ($quantity <= 0) {
            $this->removeItem($variantId);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND variant_id = ?"
            );
            $stmt->execute([$quantity, $this->cartId, $variantId]);
            $this->calculateTotal();
        }
    }

    /**
     * Calculate and update total amount
     */
    public function calculateTotal(): float {
        $stmt = $this->db->prepare(
            "SELECT SUM((p.base_price + pv.price_adjustment) * ci.quantity) AS total
             FROM cart_items ci
             JOIN product_variants pv ON ci.variant_id = pv.variant_id
             JOIN products p ON pv.product_id = p.product_id
             WHERE ci.cart_id = ?"
        );
        $stmt->execute([$this->cartId]);
        $result = $stmt->fetch();
        $this->totalAmount = (float)($result['total'] ?? 0);

        $this->db->prepare("UPDATE carts SET total_amount = ? WHERE cart_id = ?")
             ->execute([$this->totalAmount, $this->cartId]);

        return $this->totalAmount;
    }

    /**
     * Get all items in this cart
     */
    public function getItems(): array {
        $stmt = $this->db->prepare(
            "SELECT ci.*, pv.size, pv.color, pv.price_adjustment,
                    p.product_name, p.base_price,
                    (p.base_price + pv.price_adjustment) AS unit_price
             FROM cart_items ci
             JOIN product_variants pv ON ci.variant_id = pv.variant_id
             JOIN products p ON pv.product_id = p.product_id
             WHERE ci.cart_id = ?"
        );
        $stmt->execute([$this->cartId]);
        return $stmt->fetchAll();
    }

    /**
     * Clear all items from cart
     */
    public function clear(): void {
        $this->db->prepare("DELETE FROM cart_items WHERE cart_id = ?")
             ->execute([$this->cartId]);
        $this->totalAmount = 0;
        $this->db->prepare("UPDATE carts SET total_amount = 0 WHERE cart_id = ?")
             ->execute([$this->cartId]);
    }
}
