<?php
class Cart {
private $items = [];

public function addItem($id, $name, $price, $quantity = 1, $image = '') {
    if (isset($this->items[$id])) {
        $this->items[$id]->quantity += $quantity;
    } else {
        $this->items[$id] = new CartItem($id, $name, $price, $quantity, $image);
    }
}

public function removeItem($id) {
    if (isset($this->items[$id])) {
        unset($this->items[$id]);
    }
}

public function updateQuantity($id, $quantity) {
    if (isset($this->items[$id])) {
        if ($quantity <= 0) {
            $this->removeItem($id);
        } else {
            $this->items[$id]->setQuantity($quantity);
        }
    }
}

public function clearCart() {
    $this->items = [];
}

public function getItems() {
    return $this->items;
}

public function countItems() {
    return count($this->items);
}

public function countTotalQuantity() {
    $total = 0;
    foreach ($this->items as $item) {
        $total += $item->quantity;
    }
    return $total;
}

public function getTotalPrice() {
    $total = 0;
    foreach ($this->items as $item) {
        $total += $item->getTotal();
    }
    return $total;
}

public function isEmpty() {
    return empty($this->items);
}

public function hasItem($id) {
    return isset($this->items[$id]);
}
}
?>