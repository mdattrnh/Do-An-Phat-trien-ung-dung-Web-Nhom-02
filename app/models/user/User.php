<?php
abstract class User {
private $userId;
private $fullName;
private $email;
private $phone;
private $password;
private $role;
private $status;
private $createdAt;

public function __construct($userId, $fullName, $email, $phone, $password, $role, $status, $createdAt) {
    $this->userId = $userId;
    $this->fullName = $fullName;
    $this->email = $email;
    $this->phone = $phone;
    $this->password = $password;
    $this->role = $role;
    $this->status = $status;
    $this->createdAt = $createdAt;
}

// Getter
public function getUserId() {
    return $this->userId;
}

public function getFullName() {
    return $this->fullName;
}

public function getEmail() {
    return $this->email;
}

public function getPhone() {
    return $this->phone;
}

public function getRole() {
    return $this->role;
}

public function getStatus() {
    return $this->status;
}

// Methods
public function login($email, $password) {
    if ($this->email == $email && $this->password == $password) {
        return true;
    }
    return false;
}

public function logout() {
    session_destroy();
}

public function updateProfile($fullName, $phone) {
    $this->fullName = $fullName;
    $this->phone = $phone;
}
}
?>