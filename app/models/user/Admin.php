<?php
class Admin extends User {

private $adminId;
private $permissionLevel;

public function __construct(
    $userId, $fullName, $email, $phone, $password, $role, $status, $createdAt,
    $adminId, $permissionLevel
) {
    parent::__construct($userId, $fullName, $email, $phone, $password, $role, $status, $createdAt);

    $this->adminId = $adminId;
    $this->permissionLevel = $permissionLevel;
}

public function manageProduct() {
    echo "Managing products...";
}

public function manageBrand() {
    echo "Managing brands...";
}

public function manageOrder() {
    echo "Managing orders...";
}

public function manageUser() {
    echo "Managing users...";
}

public function managePromotion() {
    echo "Managing promotions...";
}
}
?>