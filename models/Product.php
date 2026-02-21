<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($name, $description, $price, $imageUrl) {
        $stmt = $this->db->prepare("INSERT INTO products (name, description, price, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $description, $price, $imageUrl);
        
        if ($stmt->execute()) {
            return $this->db->getLastInsertId();
        }
        return false;
    }

    /**
     * Crea un producto publicado desde el personalizador (nombre, descripción, precio, STL, material, dimensiones, autor, color hex, logo, lado del logo).
     */
    public function createPublish($name, $description, $price, $imageUrl, $stlUrl, $material, $dimX, $dimY, $dimZ, $author, $color = '', $logoUrl = '', $logoSide = '') {
        $stmt = $this->db->prepare("INSERT INTO products (name, description, price, image_url, stl_url, material, dim_x, dim_y, dim_z, author, color, logo_url, logo_side) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsssdddssss", $name, $description, $price, $imageUrl, $stlUrl, $material, $dimX, $dimY, $dimZ, $author, $color, $logoUrl, $logoSide);
        
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

    public function search($query = '', $material = null, $priceRange = null) {
        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];
        $types = "";
        
        if ($query) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
            $types .= "ss";
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
        return [];
    }

    public function update($id, $name, $description, $price, $imageUrl) {
        $stmt = $this->db->prepare("UPDATE products SET name = ?, description = ?, price = ?, image_url = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $name, $description, $price, $imageUrl, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function updateStock($id, $quantity) {
        return true;
    }

    /**
     * Actualiza stl_url (ruta del archivo STL/GLB).
     */
    public function updateDimensions($id, $dimensions) {
        $stlUrl = ($dimensions !== '' && (strpos($dimensions, '.stl') !== false || strpos($dimensions, '.glb') !== false))
            ? ('stl/' . $dimensions)
            : null;
        $stmt = $this->db->prepare("UPDATE products SET stl_url = ? WHERE id = ?");
        $stmt->bind_param("si", $stlUrl, $id);
        return $stmt->execute();
    }
}

