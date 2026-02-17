<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($name, $description, $price, $imageUrl, $stock, $category) {
        $stmt = $this->db->prepare("INSERT INTO products (name, description, price, image_url, stock, category) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsis", $name, $description, $price, $imageUrl, $stock, $category);
        
        if ($stmt->execute()) {
            return $this->db->getLastInsertId();
        }
        return false;
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        $result = $this->db->query($sql);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }

    public function search($query = '', $category = null, $material = null, $priceRange = null) {
        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];
        $types = "";
        
        if ($query) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
            $types .= "ss";
        }
        
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        if ($material) {
            $sql .= " AND material = ?";
            $params[] = $material;
            $types .= "s";
        }
        
        if ($priceRange) {
            if ($priceRange === '0-5') {
                $sql .= " AND price BETWEEN 0 AND 5";
            } elseif ($priceRange === '5-15') {
                $sql .= " AND price BETWEEN 5 AND 15";
            } elseif ($priceRange === '15-20') {
                $sql .= " AND price BETWEEN 15 AND 20";
            } elseif ($priceRange === '20+') {
                $sql .= " AND price > 20";
            }
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }

    public function getCategories() {
        $result = $this->db->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        return $categories;
    }

    public function update($id, $name, $description, $price, $imageUrl, $stock, $category) {
        $stmt = $this->db->prepare("UPDATE products SET name = ?, description = ?, price = ?, image_url = ?, stock = ?, category = ? WHERE id = ?");
        $stmt->bind_param("ssdsisi", $name, $description, $price, $imageUrl, $stock, $category, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function updateStock($id, $quantity) {
        $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $id);
        return $stmt->execute();
    }

    /**
     * Actualiza el campo dimensions (p. ej. nombre de archivo STL para productos generados por IA)
     */
    public function updateDimensions($id, $dimensions) {
        $stmt = $this->db->prepare("UPDATE products SET dimensions = ? WHERE id = ?");
        $stmt->bind_param("si", $dimensions, $id);
        return $stmt->execute();
    }
}

