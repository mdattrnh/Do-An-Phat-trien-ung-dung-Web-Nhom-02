<?php
class Address {
public int $id;
public string $fullName;
public string $phone;
public string $street;
public string $ward;
public string $district;
public string $city;
public string $country;

public function __construct(
    string $fullName,
    string $phone,
    string $street,
    string $ward,
    string $district,
    string $city,
    string $country = "Việt Nam"
) {
    $this->fullName = $fullName;
    $this->phone    = $phone;
    $this->street   = $street;
    $this->ward     = $ward;
    $this->district = $district;
    $this->city     = $city;
    $this->country  = $country;
}

public function getFullAddress(): string {
    return "{$this->street}, {$this->ward}, {$this->district}, {$this->city}, {$this->country}";
}
}
?>