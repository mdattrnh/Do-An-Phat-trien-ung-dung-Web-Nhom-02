<?php
class OrderItem {
public int $id;
public int $productId;
public string $productName;
public float $price;
public int $quantity;

public function __construct(
    int $productId,
    string $productName,
    float $price,
    int $quantity
) {
    $this->productId   = $productId;
    $this->productName = $productName;
    $this->price       = $price;
    $this->quantity    = $quantity;
}

public function getSubtotal(): float {
    return $this->price * $this->quantity;
}
}
?>