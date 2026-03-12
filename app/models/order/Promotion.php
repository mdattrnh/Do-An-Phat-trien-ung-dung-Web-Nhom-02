<?php
class Promotion {
public int $id;
public string $code;
public string $type; // 'percent' | 'fixed'
public float $value; // 10 = 10% hoặc 10000đ
public float $minOrderValue;
public DateTime $startDate;
public DateTime $endDate;

public function __construct(
    string $code,
    string $type,
    float $value,
    float $minOrderValue,
    DateTime $startDate,
    DateTime $endDate
) {
    $this->code          = strtoupper($code);
    $this->type          = $type;
    $this->value         = $value;
    $this->minOrderValue = $minOrderValue;
    $this->startDate     = $startDate;
    $this->endDate       = $endDate;
}

public function isValid(): bool {
    $now = new DateTime();
    return $now >= $this->startDate && $now <= $this->endDate;
}

public function calculate(float $orderTotal): float {
    if (!$this->isValid()) return 0;
    if ($orderTotal < $this->minOrderValue) return 0;

    if ($this->type === 'percent') {
        return $orderTotal * ($this->value / 100);
    }
    return $this->value; // fixed
}
}

?>