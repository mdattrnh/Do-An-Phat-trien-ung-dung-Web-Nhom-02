<?php
class Payment {
public int $id;
public int $orderId;
public string $method; // 'cod' | 'banking' | 'momo' | 'vnpay'
public float $amount;
public string $status; // 'pending' | 'paid' | 'failed' | 'refunded'
public ?string $transactionId;
public DateTime $createdAt;

public function __construct(
    int $orderId,
    string $method,
    float $amount
) {
    $this->orderId       = $orderId;
    $this->method        = $method;
    $this->amount        = $amount;
    $this->status        = 'pending';
    $this->transactionId = null;
    $this->createdAt     = new DateTime();
}

public function markAsPaid(string $transactionId): void {
    $this->status        = 'paid';
    $this->transactionId = $transactionId;
}

public function markAsFailed(): void {
    $this->status = 'failed';
}

public function isPaid(): bool {
    return $this->status === 'paid';
}
}

?>