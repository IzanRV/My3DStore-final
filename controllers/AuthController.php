<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/functions.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                setFlashMessage('Por favor, completa todos los campos', 'error');
                include __DIR__ . '/../views/auth/login.php';
                return;
            }

            $user = $this->userModel->findByEmail($email);

            if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                startSession();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                setFlashMessage('Bienvenido, ' . $user['name'], 'success');
                redirect(url(''));
            } else {
                setFlashMessage('Email o contraseña incorrectos', 'error');
            }
        }
        
        include __DIR__ . '/../views/auth/login.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $name = sanitize($_POST['name'] ?? '');
            $address = sanitize($_POST['address'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');

            // Validaciones
            if (empty($email) || empty($password) || empty($name)) {
                setFlashMessage('Por favor, completa todos los campos obligatorios', 'error');
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            if ($password !== $confirmPassword) {
                setFlashMessage('Las contraseñas no coinciden', 'error');
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            if (strlen($password) < 6) {
                setFlashMessage('La contraseña debe tener al menos 6 caracteres', 'error');
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            // Verificar si el email ya existe
            if ($this->userModel->findByEmail($email)) {
                setFlashMessage('Este email ya está registrado', 'error');
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            // Crear usuario
            $userId = $this->userModel->create($email, $password, $name, $address, $phone);

            if ($userId) {
                startSession();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'user';
                
                setFlashMessage('Registro exitoso. Bienvenido!', 'success');
                redirect(url(''));
            } else {
                setFlashMessage('Error al registrar usuario', 'error');
            }
        }
        
        include __DIR__ . '/../views/auth/register.php';
    }

    public function logout() {
        startSession();
        session_destroy();
        setFlashMessage('Sesión cerrada correctamente', 'success');
        redirect(url('login'));
    }
}

