<?php
// Punto de entrada principal de la aplicación

// Incluir archivos necesarios
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Iniciar sesión
startSession();

// Obtener acción
$action = $_GET['action'] ?? 'home';

// Router básico
switch ($action) {
    case 'login':
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;
        
    case 'register':
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        $controller->register();
        break;
        
    case 'logout':
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;
        
    case 'home':
        require_once __DIR__ . '/../controllers/ProductController.php';
        $controller = new ProductController();
        $controller->home();
        break;
        
    case 'products':
        require_once __DIR__ . '/../controllers/ProductController.php';
        $controller = new ProductController();
        $controller->index();
        break;
        
    case 'customize':
        require_once __DIR__ . '/../views/customize/index.php';
        break;
        
    case 'stl-viewer':
        require_once __DIR__ . '/../views/stl-viewer.php';
        break;
        
    case 'account':
        if (isLoggedIn()) {
            require_once __DIR__ . '/../views/account/index.php';
        } else {
            header('Location: ' . url('login'));
            exit;
        }
        break;

    case 'account-update':
        if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('login'));
            exit;
        }
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        $user = $userModel->findById($_SESSION['user_id']);
        if (!$user) {
            header('Location: ' . url('login'));
            exit;
        }
        $name = trim($_POST['name'] ?? $user['name'] ?? '');
        $phone = trim($_POST['phone'] ?? $user['phone'] ?? '');
        $address = trim($_POST['address'] ?? $user['address'] ?? '');
        if ($userModel->update((int) $_SESSION['user_id'], $name, $address, $phone)) {
            setFlashMessage('Datos actualizados correctamente.', 'success');
        } else {
            setFlashMessage('Error al actualizar los datos.', 'error');
        }
        header('Location: ' . url('account'));
        exit;

    case 'contact':
        // Por ahora solo visual
        setFlashMessage('Gracias por tu mensaje. Te contactaremos pronto.', 'success');
        header('Location: ' . url());
        exit;
        break;
        
    case 'product':
        require_once __DIR__ . '/../controllers/ProductController.php';
        $controller = new ProductController();
        $controller->show();
        break;
        
    case 'create-review':
        require_once __DIR__ . '/../controllers/ProductController.php';
        $controller = new ProductController();
        $controller->createReview();
        break;
        
    case 'cart':
        require_once __DIR__ . '/../controllers/CartController.php';
        $controller = new CartController();
        $controller->index();
        break;
        
    case 'cart-add':
        require_once __DIR__ . '/../controllers/CartController.php';
        $controller = new CartController();
        $controller->add();
        break;
        
    case 'cart-update':
        require_once __DIR__ . '/../controllers/CartController.php';
        $controller = new CartController();
        $controller->update();
        break;
        
    case 'cart-remove':
        require_once __DIR__ . '/../controllers/CartController.php';
        $controller = new CartController();
        $controller->remove();
        break;
        
    case 'checkout':
        require_once __DIR__ . '/../controllers/CheckoutController.php';
        $controller = new CheckoutController();
        $controller->index();
        break;
        
    case 'orders':
        require_once __DIR__ . '/../controllers/OrderController.php';
        $controller = new OrderController();
        $controller->index();
        break;
        
    case 'order':
        require_once __DIR__ . '/../controllers/OrderController.php';
        $controller = new OrderController();
        $controller->show();
        break;
        
    case 'admin-dashboard':
        require_once __DIR__ . '/../controllers/AdminController.php';
        $controller = new AdminController();
        $controller->dashboard();
        break;
        
    case 'admin-products':
        require_once __DIR__ . '/../controllers/AdminController.php';
        $controller = new AdminController();
        $controller->products();
        break;
        
    case 'admin-product-create':
        require_once __DIR__ . '/../controllers/AdminController.php';
        $controller = new AdminController();
        $controller->createProduct();
        break;
        
    case 'admin-product-edit':
        require_once __DIR__ . '/../controllers/AdminController.php';
        $controller = new AdminController();
        $controller->editProduct();
        break;
        
    case 'admin-product-delete':
        require_once __DIR__ . '/../controllers/AdminController.php';
        $controller = new AdminController();
        $controller->deleteProduct();
        break;
        
    case 'admin-orders':
        require_once __DIR__ . '/../controllers/AdminController.php';
        $controller = new AdminController();
        $controller->orders();
        break;
        
    case 'admin-order-update-status':
        require_once __DIR__ . '/../controllers/AdminController.php';
        $controller = new AdminController();
        $controller->updateOrderStatus();
        break;
        
    case 'admin-users':
        require_once __DIR__ . '/../controllers/AdminController.php';
        $controller = new AdminController();
        $controller->users();
        break;
        
    default:
        // Por defecto, mostrar productos
        require_once __DIR__ . '/../controllers/ProductController.php';
        $controller = new ProductController();
        $controller->index();
        break;
}

