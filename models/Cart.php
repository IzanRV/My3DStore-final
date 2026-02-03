<?php
require_once __DIR__ . '/../config/database.php';

class Cart {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function addItem($userId, $productId, $quantity = 1) {
        // Verificar si el item ya existe
        $existing = $this->getItem($userId, $productId);
        
        if ($existing) {
            // Actualizar cantidad
            $newQuantity = $existing['quantity'] + $quantity;
            $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $newQuantity, $userId, $productId);
            return $stmt->execute();
        } else {
            // Crear nuevo item
            $stmt = $this->db->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $userId, $productId, $quantity);
            return $stmt->execute();
        }
    }

    public function getItem($userId, $productId) {
        $stmt = $this->db->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getItems($userId) {
        $stmt = $this->db->prepare("
            SELECT ci.*, p.name, p.price, p.image_url, p.stock, p.material, p.dimensions, p.category, p.weight
            FROM cart_items ci 
            JOIN products p ON ci.product_id = p.id 
            WHERE ci.user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    public function updateQuantity($userId, $productId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($userId, $productId);
        }
        
        $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $userId, $productId);
        return $stmt->execute();
    }

    public function removeItem($userId, $productId) {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);
        return $stmt->execute();
    }

    public function clear($userId) {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    public function getTotal($userId) {
        $items = $this->getItems($userId);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}

