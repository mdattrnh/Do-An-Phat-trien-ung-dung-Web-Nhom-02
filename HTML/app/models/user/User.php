<?php
/**
 * User Model - Base class
 * Based on UML: userId, fullName, email, phone, password, role, status, created_at
 */
require_once __DIR__ . '/UserInterface.php';

abstract class User implements UserInterface {
    protected string $userId;
    protected string $fullName;
    protected string $email;
    protected ?string $phone;
    protected string $password;
    protected string $role;       // 'customer' | 'admin'
    protected string $status;    // 'active' | 'banned'
    protected string $createdAt;

    // PDO connection
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Getters ──────────────────────────────
    public function getUserId(): string   { return $this->userId; }
    public function getFullName(): string  { return $this->fullName; }
    public function getEmail(): string     { return $this->email; }
    public function getPhone(): ?string    { return $this->phone; }
    public function getRole(): string      { return $this->role; }
    public function getStatus(): string    { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }

    // ── Setters ──────────────────────────────
    public function setFullName(string $name): void   { $this->fullName = $name; }
    public function setEmail(string $email): void     { $this->email = $email; }
    public function setPhone(?string $phone): void    { $this->phone = $phone; }
    public function setPassword(string $hash): void   { $this->password = $hash; }
    public function setStatus(string $status): void   { $this->status = $status; }

    // ── Core Methods ─────────────────────────
    /**
     * Find user by email
     */
    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Find user by ID
     */
    public function findById(string $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Login logic - returns user row or false
     */
    public function login(): bool|array {
        $user = $this->findByEmail($this->email);
        if ($user && password_verify($this->password, $user['password'])) {
            return $user;
        }
        return false;
    }

    /**
     * Logout (session destroy)
     */
    public function logout(): void {
        session_start();
        session_destroy();
    }
}
