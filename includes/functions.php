<?php
// Funciones auxiliares
require_once __DIR__ . '/../config/database.php';

/**
 * Ruta base del proyecto para URLs (Docker: /, WAMP: /My3DStore/)
 */
function getBasePath() {
    static $basePath = null;
    if ($basePath === null) {
        if (file_exists('/.dockerenv') || getenv('DOCKER_CONTAINER')) {
            $basePath = '/';
        } else {
            $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            if (basename($scriptPath) === 'public') {
                $scriptPath = dirname($scriptPath);
            }
            if (basename($scriptPath) === 'api') {
                $scriptPath = dirname($scriptPath);
            }
            $basePath = rtrim($scriptPath, '/') . '/';
            if ($basePath === '/') {
                // App en raíz (ej. localhost:8081/): usar / para que asset() y enlaces coincidan
                $basePath = '/';
            }
        }
    }
    return $basePath;
}

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    startSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . url('login'));
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . url());
        exit;
    }
}

function getUser() {
    startSession();
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT id, email, name, address, phone, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function formatPrice($price) {
    return number_format($price, 2, ',', '.') . ' €';
}

function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function setFlashMessage($message, $type = 'info') {
    startSession();
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashMessage() {
    startSession();
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

function getCartCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

function asset($path) {
    // Generar ruta correcta para assets (CSS, JS, imágenes, STL, GLB)
    // Docker o PHP -S con root=public: base '/' → /stl/..., /images/... (se sirven desde public/)
    // Si la entrada es index.php en la raíz del proyecto (ej. /My3DStore/index.php), los archivos
    // están en public/ → hay que devolver /My3DStore/public/stl/... para que el STL cargue.
    if (file_exists('/.dockerenv') || getenv('DOCKER_CONTAINER')) {
        $basePath = '/';
    } else {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $basePath = rtrim($scriptDir, '/') . '/';
        if ($basePath === '/') {
            $basePath = '/';
        }
    }
    $path = ltrim($path, '/');
    // Cuando la app se sirve desde la raíz del proyecto (ej. /My3DStore/index.php), los assets
    // están en la carpeta public/; si basePath no termina en /public/, anteponer public/
    if ($basePath !== '/' && substr(rtrim($basePath, '/'), -7) !== '/public') {
        $path = 'public/' . $path;
    }
    return $basePath . $path;
}

/**
 * URL del modelo 3D para un producto (STL/GLB en dimensions o pato.glb por defecto).
 * Busca en stl/ o stl/generated/ según el nombre del archivo.
 * @param array|null $product Debe contener 'dimensions' (nombre de archivo STL/GLB si existe).
 * @return string URL del asset del modelo.
 */
function productModelAsset($product) {
    if (!is_array($product)) {
        return asset('glb/pato.glb');
    }
    $stlUrl = isset($product['stl_url']) ? trim($product['stl_url']) : '';
    if ($stlUrl !== '' && (strpos($stlUrl, '.stl') !== false || strpos($stlUrl, '.glb') !== false)) {
        return asset($stlUrl);
    }
    $d = isset($product['dimensions']) ? trim($product['dimensions']) : '';
    if ($d !== '' && (strpos($d, '.stl') !== false || strpos($d, '.glb') !== false)) {
        $ext = (strpos($d, '.stl') !== false) ? 'stl' : 'glb';
        $baseDir = __DIR__ . '/../public/' . $ext . '/';
        $subPath = $ext . '/' . $d;
        if ($ext === 'stl') {
            if (file_exists($baseDir . $d)) {
                return asset($subPath);
            }
            if (file_exists($baseDir . 'generated/' . $d)) {
                return asset($ext . '/generated/' . $d);
            }
        }
        return asset($subPath);
    }
    return asset('glb/pato.glb');
}

function url($path = '', $params = []) {
    // Generar URL correcta para la aplicación (misma base que asset/getBasePath para localhost:8081, etc.)
    $baseUrl = getBasePath();
    $url = $baseUrl;
    
    if (!empty($path)) {
        $url .= '?action=' . $path;
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $url .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
    } else if (!empty($params)) {
        $url .= '?';
        $queryParts = [];
        foreach ($params as $key => $value) {
            $queryParts[] = urlencode($key) . '=' . urlencode($value);
        }
        $url .= implode('&', $queryParts);
    }
    
    return $url;
}

