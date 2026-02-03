<?php
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/functions.php';

class CheckoutController {
    private $cartModel;
    private $orderModel;
    private $userModel;

    public function __construct() {
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
        $this->userModel = new User();
    }

    public function index() {
        requireLogin();
        
        $userId = $_SESSION['user_id'];
        $items = $this->cartModel->getItems($userId);
        $total = $this->cartModel->getTotal($userId);
        
        if (empty($items)) {
            setFlashMessage('Tu carrito está vacío', 'error');
            redirect('/My3DStore/?action=cart');
        }
        
        $user = $this->userModel->findById($userId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $shippingAddress = sanitize($_POST['shipping_address'] ?? '');
            
            if (empty($shippingAddress)) {
                setFlashMessage('Por favor, proporciona una dirección de envío', 'error');
                include __DIR__ . '/../views/checkout/index.php';
                return;
            }
            
            // Preparar items para el pedido
            $orderItems = [];
            foreach ($items as $item) {
                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ];
            }
            
            // Crear pedido
            $orderId = $this->orderModel->create($userId, $total, $shippingAddress, $orderItems);
            
            if ($orderId) {
                // Limpiar carrito
                $this->cartModel->clear($userId);
                
                setFlashMessage('Pedido realizado correctamente', 'success');
                redirect("/My3DStore/?action=order&id=$orderId");
            } else {
                setFlashMessage('Error al procesar el pedido', 'error');
            }
        }
        
        include __DIR__ . '/../views/checkout/index.php';
    }
}

