<?php
require_once __DIR__ . '/../config/database.php';

class Order {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($userId, $total, $shippingAddress, $items) {
        $this->db->getConnection()->autocommit(false);
        
        try {
            // Crear pedido
            $stmt = $this->db->prepare("INSERT INTO orders (user_id, total, shipping_address) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $userId, $total, $shippingAddress);
            $stmt->execute();
            $orderId = $this->db->getLastInsertId();
            
            // Crear items del pedido
            $productModel = new Product();
            foreach ($items as $item) {
                $product = $productModel->findById($item['product_id']);
                if (!$product) {
                    throw new Exception("Producto no encontrado: " . $item['product_id']);
                }
                
                $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }
            
            $this->db->getConnection()->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            return false;
        } finally {
            $this->db->getConnection()->autocommit(true);
        }
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        return $orders;
    }

    public function getAll() {
        $result = $this->db->query("SELECT * FROM orders ORDER BY created_at DESC");
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        return $orders;
    }

    public function getItems($orderId) {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.name, p.image_url 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }
}

