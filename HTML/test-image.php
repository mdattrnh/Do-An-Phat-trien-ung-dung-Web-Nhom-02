<?php
// Test endpoint to check image paths
echo "<h1>Image Path Testing</h1>";

// 1. Check database
require_once 'config/database.php';
$db = Database::getInstance()->getConnection();

echo "<h2>1. Images in database:</h2>";
$stmt = $db->prepare("SELECT DISTINCT image_url FROM product_images LIMIT 5");
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "<pre>";
foreach ($images as $img) {
    echo "DB: " . htmlspecialchars($img) . "\n";
}
echo "</pre>";

// 2. Check filesystem
echo "<h2>2. Images in filesystem:</h2>";
$uploadDir = __DIR__ . '/public/uploads/products/';
echo "Upload dir: " . $uploadDir . "\n<br>";
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    echo "<pre>";
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            $fullPath = $uploadDir . $f;
            $size = filesize($fullPath);
            echo "File: " . htmlspecialchars($f) . " (" . $size . " bytes)\n";
            echo "  URL: /HTML/public/uploads/products/" . htmlspecialchars($f) . "\n";
        }
    }
    echo "</pre>";
}

// 3. Test URL construction
echo "<h2>3. URL Construction Test:</h2>";
echo "BASE_URL: " . BASE_URL . "<br>";
foreach ($images as $img) {
    $url = '/HTML/' . $img;
    echo "Path: " . htmlspecialchars($img) . "<br>";
    echo "URL: " . htmlspecialchars($url) . "<br>";
    echo "<img src=\"$url\" width=\"100\" onerror=\"this.src='error.png'\" style=\"border:1px solid red\"><br><br>";
}
?>
