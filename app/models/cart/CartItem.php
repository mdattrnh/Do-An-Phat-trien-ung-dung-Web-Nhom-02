<?php

class CartItem {
public $id;
public $name;
public $price;
public $quantity;
public $image;

public function __construct($id, $name, $price, $quantity = 1, $image = '') {
    $this->id       = $id;
    $this->name     = $name;
    $this->price    = $price;
    $this->quantity = $quantity;
    $this->image    = $image;
}

public function getTotal() {
    return $this->price * $this->quantity;
}

public function increaseQuantity() {
    $this->quantity++;
}

public function decreaseQuantity() {
    if ($this->quantity > 1) {
        $this->quantity--;
    }
}

public function setQuantity($quantity) {
    if ($quantity > 0) {
        $this->quantity = $quantity;
    }
}
}
?>