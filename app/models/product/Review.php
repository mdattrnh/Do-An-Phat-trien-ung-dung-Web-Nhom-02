<?php
class Review
{
private $review_id;
private $product_id;
private $user_name;
private $rating;
private $comment;
private $review_date;

public function __construct($review_id, $product_id, $user_name, $rating, $comment, $review_date)
{
    $this->review_id = $review_id;
    $this->product_id = $product_id;
    $this->user_name = $user_name;
    $this->rating = $rating;
    $this->comment = $comment;
    $this->review_date = $review_date;
}

// Getter
public function getReviewId()
{
    return $this->review_id;
}

public function getProductId()
{
    return $this->product_id;
}

public function getUserName()
{
    return $this->user_name;
}

public function getRating()
{
    return $this->rating;
}

public function getComment()
{
    return $this->comment;
}

public function getReviewDate()
{
    return $this->review_date;
}

// Setter
public function setRating($rating)
{
    $this->rating = $rating;
}

public function setComment($comment)
{
    $this->comment = $comment;
}
}
?>