<?php
class Customer extends User {

private $customerId;
private $gender;
private $dateOfBirth;
private $loyaltyPoint;
private $defaultAddress;

public function __construct(
    $userId, $fullName, $email, $phone, $password, $role, $status, $createdAt,
    $customerId, $gender, $dateOfBirth, $loyaltyPoint, $defaultAddress
) {
    parent::__construct($userId, $fullName, $email, $phone, $password, $role, $status, $createdAt);

    $this->customerId = $customerId;
    $this->gender = $gender;
    $this->dateOfBirth = $dateOfBirth;
    $this->loyaltyPoint = $loyaltyPoint;
    $this->defaultAddress = $defaultAddress;
}

public function addToCart($productVariant, $quantity) {
    echo "Added $quantity of product variant $productVariant to cart.";
}

public function placeOrder() {
    echo "Order placed successfully.";
}

public function viewOrderHistory() {
    echo "Displaying order history.";
}

public function reviewProduct($product, $rating, $comment) {
    echo "Review submitted for $product with rating $rating.";
}
}
?>