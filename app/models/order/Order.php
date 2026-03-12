<?php
class Order {
public int $id;
public int $userId;
public array $items = []; // mảng OrderItem
public ?Payment $payment;
public ?Shipment $shipment;
public ?Promotion $promotion;
public string $status; // 'pending' | 'confirmed' | 'shipping' | 'completed' | 'cancelled'
public DateTime $createdAt;

public function __construct(int $userId) {
    $this->userId    = $userId;
    $this->status    = 'pending';
    $this->payment   = null;
    $this->shipment  = null;
    $this->promotion = null;
    $this->createdAt = new DateTime();
}

public function addItem(OrderItem $item): void {
    $this->items[] = $item;
}

public function getSubtotal(): float {
    $total = 0;
    foreach ($this->items as $item) {
        $total += $item->getSubtotal();
    }
    return $total;
}

public function getDiscount(): float {
    if (!$this->promotion) return 0;
    return $this->promotion->calculate($this->getSubtotal());
}

public function getShippingFee(): float {
    if (!$this->shipment) return 0;
    return $this->shipment->shippingFee;
}

public function getTotal(): float {
    return $this->getSubtotal() - $this->getDiscount() + $this->getShippingFee();
}

public function applyPromotion(Promotion $promotion): bool {
    if (!$promotion->isValid()) return false;
    if ($this->getSubtotal() < $promotion->minOrderValue) return false;
    $this->promotion = $promotion;
    return true;
}

public function setPayment(Payment $payment): void {
    $this->payment = $payment;
}

public function setShipment(Shipment $shipment): void {
    $this->shipment = $shipment;
}

public function confirm(): void {
    $this->status = 'confirmed';
}

public function cancel(): void {
    $this->status = 'cancelled';
}

public function complete(): void {
    $this->status = 'completed';
}

public function getSummary(): array {
    return [
        'orderId'     => $this->id,
        'userId'      => $this->userId,
        'status'      => $this->status,
        'subtotal'    => $this->getSubtotal(),
        'discount'    => $this->getDiscount(),
        'shippingFee' => $this->getShippingFee(),
        'total'       => $this->getTotal(),
        'itemCount'   => count($this->items),
        'createdAt'   => $this->createdAt->format('Y-m-d H:i:s'),
    ];
}
}
?>