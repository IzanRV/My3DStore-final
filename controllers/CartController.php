<?php
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../includes/functions.php';

class CartController {
    private $cartModel;
    private $productModel;

    public function __construct() {
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }

    public function index() {
        requireLogin();
        
        $userId = $_SESSION['user_id'];
        $items = $this->cartModel->getItems($userId);
        $total = $this->cartModel->getTotal($userId);
        
        include __DIR__ . '/../views/cart/index.php';
    }

    public function add() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            
            if ($productId <= 0) {
                setFlashMessage('Producto inválido', 'error');
                redirect(url('products'));
            }
            
            $product = $this->productModel->findById($productId);
            
            if (!$product) {
                setFlashMessage('Producto no encontrado', 'error');
                redirect(url('products'));
            }
            
            $userId = $_SESSION['user_id'];
            
            if ($this->cartModel->addItem($userId, $productId, $quantity)) {
                setFlashMessage('Producto añadido al carrito', 'success');
            } else {
                setFlashMessage('Error al añadir producto al carrito', 'error');
            }
            
            redirect(url('product', ['id' => $productId]));
        }
    }

    public function update() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            $userId = $_SESSION['user_id'];
            
            if ($this->cartModel->updateQuantity($userId, $productId, $quantity)) {
                setFlashMessage('Carrito actualizado', 'success');
            } else {
                setFlashMessage('Error al actualizar el carrito', 'error');
            }
        }
        
        redirect(url('cart'));
    }

    public function remove() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = intval($_POST['product_id'] ?? 0);
            $userId = $_SESSION['user_id'];
            
            if ($this->cartModel->removeItem($userId, $productId)) {
                setFlashMessage('Producto eliminado del carrito', 'success');
            } else {
                setFlashMessage('Error al eliminar producto', 'error');
            }
        }
        
        redirect(url('cart'));
    }
}

