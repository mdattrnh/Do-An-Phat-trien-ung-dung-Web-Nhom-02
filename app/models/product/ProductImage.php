<?php
class ProductImage
{
private $image_id;
private $product_id;
private $image_url;
private $is_main;

public function __construct($image_id, $product_id, $image_url, $is_main = false)
{
    $this->image_id = $image_id;
    $this->product_id = $product_id;
    $this->image_url = $image_url;
    $this->is_main = $is_main;
}

// Getter
public function getImageId()
{
    return $this->image_id;
}

public function getProductId()
{
    return $this->product_id;
}

public function getImageUrl()
{
    return $this->image_url;
}

public function isMainImage()
{
    return $this->is_main;
}

// Setter
public function setImageUrl($image_url)
{
    $this->image_url = $image_url;
}

public function setMainImage($is_main)
{
    $this->is_main = $is_main;
}
}
?>