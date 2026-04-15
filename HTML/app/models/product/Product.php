<?php
/**
 * Product Model
 * UML: product_id, brand_id, product_name, description, base_price, category, gender_type, status, createdAt, updatedAt
 */
class Product {
    private string $productId;
    private string $brandId;
    private string $productName;
    private string $description;
    private float $basePrice;
    private string $category;
    private string $genderType;
    private string $status;
    
    // Virtual field for JOINs
    private string $brandName;

    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Getters ──────────────────────────────
    public function getProductId(): string   { return $this->productId; }
    public function getBrandId(): string     { return $this->brandId; }
    public function getProductName(): string { return $this->productName; }
    public function getDescription(): string { return $this->description; }
    public function getBasePrice(): float    { return $this->basePrice; }
    public function getCategory(): string    { return $this->category; }
    public function getGenderType(): string  { return $this->genderType; }
    public function getStatus(): string      { return $this->status; }
    public function getBrandName(): string   { return $this->brandName; }

    // ── Setters ──────────────────────────────
    public function setBrandId(string $id): void     { $this->brandId = $id; }
    public function setProductName(string $n): void  { $this->productName = $n; }
    public function setDescription(string $d): void  { $this->description = $d; }
    public function setBasePrice(float $p): void     { $this->basePrice = $p; }
    public function setCategory(string $c): void     { $this->category = $c; }
    public function setGenderType(string $g): void   { $this->genderType = $g; }
    public function setStatus(string $s): void       { $this->status = $s; }

    /**
     * Lấy toàn bộ sản phẩm (kết hợp các bảng cho SPA Frontend)
     */
    public function getAllForSpa(): array {
        // Query to get all active products and their primary image, variations, and brand
        $sql = "
            SELECT 
                p.product_id as id,
                p.product_name as name,
                p.category,
                p.base_price as price,
                p.description as `desc`,
                b.brand_name,
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image,
                (SELECT GROUP_CONCAT(DISTINCT color SEPARATOR ',') FROM product_variants WHERE product_id = p.product_id) as colors,
                (SELECT GROUP_CONCAT(DISTINCT size SEPARATOR ',') FROM product_variants WHERE product_id = p.product_id) as sizes
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.brand_id
            WHERE p.status = 'active'
            ORDER BY p.product_name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'id'           => $row['id'],
                'name'         => $row['name'],
                'category'     => $row['category'] ?? 'others', 
                'color'        => $row['colors'] ? explode(',', $row['colors'])[0] : 'Trắng', // Lấy màu đầu tiên làm màu đại diện
                'price'        => (int)$row['price'],
                'tag'          => $row['brand_name'] ?? '',
                'sizes'        => $row['sizes'] ? explode(',', $row['sizes']) : ['One Size'],
                'desc'         => $row['desc'] ?? '',
                'image' => $row['image'] ? $this->buildImageUrl($row['image']) : 'https://via.placeholder.com/400x500',
                'isNew'        => rand(0, 1) == 1, // Random feature for UI
                'isBestSeller' => rand(0, 1) == 1
            ];
        }
        return $formatted;
    }

    /**
     * Get product by ID with full details
     */
    public function getById(string $id): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, b.brand_name
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.brand_id
            WHERE p.product_id = ?
        ");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            return null;
        }
        
        // Get primary image
        $stmt = $this->db->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
        $stmt->execute([$id]);
        $primaryImage = $stmt->fetch(PDO::FETCH_COLUMN);

        // Get sizes
        $stmt = $this->db->prepare("SELECT DISTINCT size FROM product_variants WHERE product_id = ? ORDER BY size ASC");
        $stmt->execute([$id]);
        $sizes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return [
            'id' => $product['product_id'],
            'name' => $product['product_name'],
            'description' => $product['description'],
            'desc' => $product['description'],
            'price' => $product['base_price'],
            'category' => $product['category'],
            'brand' => $product['brand_name'] ?? 'SOFT EDGE',
            'brand_name' => $product['brand_name'] ?? 'SOFT EDGE',
            'tag' => $product['brand_name'] ?? 'NEW DROP',
            'image' => $primaryImage ? $this->buildImageUrl($primaryImage) : 'https://via.placeholder.com/400x500',
            'sizes' => $sizes,
            'status' => $product['status']
        ];
    }

    /**
     * Thêm mới sản phẩm (Dành cho Admin Panel mới)
     */
    public function save(): void {
        $stmt = $this->db->prepare(
            "INSERT INTO products (product_id, brand_id, product_name, description, base_price, category, gender_type, status) 
             VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $this->brandId,
            $this->productName,
            $this->description,
            $this->basePrice,
            $this->category,
            $this->genderType,
            $this->status
        ]);
    }

    /**
     * Build proper image URL from database path
     * Database stores paths like: public/uploads/products/filename.ext or assets/image/filename.ext
     */
    public function buildImageUrl(string $imagePath): string {
        if (empty($imagePath)) {
            return 'https://via.placeholder.com/600x800?text=No+Image';
        }

        // If it's already a full URL (http/https), return as is
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }

        // If path starts with /, return it as an absolute path
        if (strpos($imagePath, '/') === 0) {
            return $imagePath;
        }

        $imagePath = trim($imagePath);
        $base = rtrim(BASE_URL, '/');

        // If it already includes the public or assets prefix, just prefix with BASE_URL
        if (strpos($imagePath, 'public/uploads/products/') === 0 || strpos($imagePath, 'assets/image/') === 0) {
            return $base . '/' . $imagePath;
        }

        // Fallback: treat as public/uploads/products
        return $base . '/public/uploads/products/' . ltrim($imagePath, '/');
    }
}
