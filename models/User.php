<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($email, $password, $name, $address = '', $phone = '') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password, name, address, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $email, $hashedPassword, $name, $address, $phone);
        
        if ($stmt->execute()) {
            return $this->db->getLastInsertId();
        }
        return false;
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT id, email, name, address, phone, role, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }

    public function update($id, $name, $address, $phone) {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, address = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $address, $phone, $id);
        return $stmt->execute();
    }

    public function getAll() {
        $result = $this->db->query("SELECT id, email, name, address, phone, role, created_at FROM users ORDER BY created_at DESC");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }
}

