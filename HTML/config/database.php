<?php
/**
 * Database Configuration
 */

// --- DATABASE ENVIRONMENT DETECTION ---

$is_local = strpos($_SERVER['HTTP_HOST'], 'localhost') !== false
         || $_SERVER['SERVER_ADDR'] === '127.0.0.1';
if ($is_local) {
    // Local / XAMPP Credentials
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'shop_db');
} else {
    // Production / ProFreeHost Credentials (UPDATE THESE ONCE)
    define('DB_HOST', 'localhost'); // Usually localhost on shared hosting, or an IP/host from SQL manager
    define('DB_USER', 'your_production_user');
    define('DB_PASS', 'your_production_pass');
    define('DB_NAME', 'your_production_dbname');
}
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}
