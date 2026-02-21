<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/functions.php';

class AdminController {
    private $productModel;
    private $orderModel;
    private $userModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->userModel = new User();
    }

    public function dashboard() {
        requireAdmin();
        
        $db = Database::getInstance();
        
        // Estadísticas
        $totalProducts = $db->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
        $totalOrders = $db->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
        $totalUsers = $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        $totalRevenue = $db->query("SELECT SUM(total) as total FROM orders WHERE status != 'cancelled'")->fetch_assoc()['total'] ?? 0;
        
        include __DIR__ . '/../views/admin/dashboard.php';
    }

    public function products() {
        requireAdmin();
        
        $products = $this->productModel->getAll();
        
        include __DIR__ . '/../views/admin/products/index.php';
    }

    public function createProduct() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitize($_POST['name'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $imageUrl = sanitize($_POST['image_url'] ?? '');
            
            if (empty($name) || $price <= 0) {
                setFlashMessage('Por favor, completa todos los campos obligatorios', 'error');
                include __DIR__ . '/../views/admin/products/create.php';
                return;
            }
            
            if ($this->productModel->create($name, $description, $price, $imageUrl)) {
                setFlashMessage('Producto creado correctamente', 'success');
                redirect('/My3DStore/?action=admin-products');
            } else {
                setFlashMessage('Error al crear el producto', 'error');
            }
        }
        
        include __DIR__ . '/../views/admin/products/create.php';
    }

    public function editProduct() {
        requireAdmin();
        
        $id = intval($_GET['id'] ?? 0);
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            setFlashMessage('Producto no encontrado', 'error');
            redirect('/My3DStore/public/index.php?action=admin-products');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitize($_POST['name'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $imageUrl = sanitize($_POST['image_url'] ?? '');
            
            if (empty($name) || $price <= 0) {
                setFlashMessage('Por favor, completa todos los campos obligatorios', 'error');
                include __DIR__ . '/../views/admin/products/edit.php';
                return;
            }
            
            if ($this->productModel->update($id, $name, $description, $price, $imageUrl)) {
                setFlashMessage('Producto actualizado correctamente', 'success');
                redirect('/My3DStore/?action=admin-products');
            } else {
                setFlashMessage('Error al actualizar el producto', 'error');
            }
        }
        
        include __DIR__ . '/../views/admin/products/edit.php';
    }

    public function deleteProduct() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            
            if ($this->productModel->delete($id)) {
                setFlashMessage('Producto eliminado correctamente', 'success');
            } else {
                setFlashMessage('Error al eliminar el producto', 'error');
            }
        }
        
        redirect('/My3DStore/public/index.php?action=admin-products');
    }

    public function orders() {
        requireAdmin();
        
        $orders = $this->orderModel->getAll();
        
        include __DIR__ . '/../views/admin/orders/index.php';
    }

    public function updateOrderStatus() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['order_id'] ?? 0);
            $status = sanitize($_POST['status'] ?? '');
            
            if ($this->orderModel->updateStatus($id, $status)) {
                setFlashMessage('Estado del pedido actualizado', 'success');
            } else {
                setFlashMessage('Error al actualizar el estado', 'error');
            }
        }
        
        redirect('/My3DStore/?action=admin-orders');
    }

    public function users() {
        requireAdmin();
        
        $users = $this->userModel->getAll();
        
        include __DIR__ . '/../views/admin/users.php';
    }
}

