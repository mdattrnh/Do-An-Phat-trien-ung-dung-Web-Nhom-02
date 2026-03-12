<?php
class Shipment {
public int $id;
public int $orderId;
public Address $address;
public string $status; // 'pending' | 'shipping' | 'delivered' | 'returned'
public ?string $trackingCode;
public ?DateTime $shippedAt;
public ?DateTime $deliveredAt;
public float $shippingFee;

public function __construct(
    int $orderId,
    Address $address,
    float $shippingFee = 0
) {
    $this->orderId      = $orderId;
    $this->address      = $address;
    $this->status       = 'pending';
    $this->trackingCode = null;
    $this->shippedAt    = null;
    $this->deliveredAt  = null;
    $this->shippingFee  = $shippingFee;
}

public function ship(string $trackingCode): void {
    $this->status       = 'shipping';
    $this->trackingCode = $trackingCode;
    $this->shippedAt    = new DateTime();
}

public function deliver(): void {
    $this->status      = 'delivered';
    $this->deliveredAt = new DateTime();
}

public function isDelivered(): bool {
    return $this->status === 'delivered';
}
}
?>