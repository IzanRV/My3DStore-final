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
            $paymentMethod = trim($_POST['payment_method'] ?? 'card');
            if (!in_array($paymentMethod, ['card', 'paypal', 'bizum'], true)) {
                $paymentMethod = 'card';
            }
            
            if (empty($shippingAddress)) {
                setFlashMessage('Por favor, proporciona una dirección de envío', 'error');
                include __DIR__ . '/../views/checkout/index.php';
                return;
            }
            
            $paymentLast4 = null;
            if ($paymentMethod === 'card') {
                $cardNumber = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
                $cardExpiry = trim($_POST['card_expiry'] ?? '');
                $cardCvv = trim($_POST['card_cvv'] ?? '');
                if (strlen(preg_replace('/\D/', '', $cardNumber)) < 13) {
                    setFlashMessage('Introduce un número de tarjeta válido', 'error');
                    include __DIR__ . '/../views/checkout/index.php';
                    return;
                }
                if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $cardExpiry)) {
                    setFlashMessage('Caducidad inválida (usa MM/AA)', 'error');
                    include __DIR__ . '/../views/checkout/index.php';
                    return;
                }
                if (!preg_match('/^\d{3,4}$/', $cardCvv)) {
                    setFlashMessage('CVV inválido (3 o 4 dígitos)', 'error');
                    include __DIR__ . '/../views/checkout/index.php';
                    return;
                }
                $paymentLast4 = substr(preg_replace('/\D/', '', $cardNumber), -4);
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
            
            // Crear pedido (payment_method y payment_last4 se guardan en orders)
            $orderId = $this->orderModel->create($userId, $total, $shippingAddress, $orderItems, $paymentMethod, $paymentLast4);
            
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

