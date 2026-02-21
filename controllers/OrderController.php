<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../includes/functions.php';

class OrderController {
    private $orderModel;

    public function __construct() {
        $this->orderModel = new Order();
    }

    public function index() {
        requireLogin();
        
        $userId = $_SESSION['user_id'];
        $orders = $this->orderModel->getByUserId($userId);
        
        include __DIR__ . '/../views/orders/index.php';
    }

    public function show() {
        requireLogin();
        
        $id = intval($_GET['id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        $order = $this->orderModel->findById($id);
        
        if (!$order) {
            setFlashMessage('Pedido no encontrado', 'error');
            redirect(url('orders'));
        }
        
        // Verificar que el pedido pertenece al usuario (a menos que sea admin)
        if ($order['user_id'] != $userId && !isAdmin()) {
            setFlashMessage('No tienes permiso para ver este pedido', 'error');
            redirect(url('orders'));
        }
        
        $items = $this->orderModel->getItems($id);
        
        include __DIR__ . '/../views/orders/show.php';
    }
}

