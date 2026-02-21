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

/**
 * Formatea dimensiones X, Y, Z (en mm) para mostrar.
 * @param mixed $x
 * @param mixed $y
 * @param mixed $z
 * @return string
 */
function formatDimensions($x, $y = null, $z = null) {
    $x = $x !== null && $x !== '' ? (float) $x : null;
    $y = $y !== null && $y !== '' ? (float) $y : null;
    $z = $z !== null && $z !== '' ? (float) $z : null;
    if ($x === null && $y === null && $z === null) {
        return '';
    }
    $parts = array_filter([$x, $y, $z], function ($v) { return $v !== null; });
    return implode(' × ', $parts) . ' mm';
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
 * URL del modelo 3D para un producto (stl_url o pato.glb por defecto).
 * @param array|null $product Debe contener 'stl_url' (ruta STL/GLB si existe).
 * @return string URL del asset del modelo.
 */
function productModelAsset($product) {
    $urls = productModelAssets($product);
    return $urls ? $urls[0] : asset('glb/pato.glb');
}

/**
 * Lista nombres de archivos .stl y .glb en un directorio (ordenados).
 * @param string $dirPath Ruta absoluta del directorio.
 * @return array Lista de nombres de archivo.
 */
function listStlGlbInDir($dirPath) {
    $files = [];
    if (!is_dir($dirPath)) return $files;
    foreach (new DirectoryIterator($dirPath) as $f) {
        if ($f->isDot()) continue;
        $name = $f->getFilename();
        if (preg_match('/\.(stl|glb)$/i', $name)) {
            $files[] = $name;
        }
    }
    sort($files);
    return $files;
}

/**
 * Lista de URLs de modelos 3D para un producto.
 * Si existe la carpeta public/stl/{id}/ (por id de producto o por número en stl_url, ej. 1471971), devuelve todos los .stl/.glb.
 * Si no, devuelve un único elemento con el modelo actual (stl_url).
 * @param array|null $product Debe contener 'id' y opcionalmente 'stl_url'.
 * @return array Lista de URLs de assets.
 */
function productModelAssets($product) {
    if (!is_array($product)) {
        return [asset('glb/pato.glb')];
    }
    $id = isset($product['id']) ? (int) $product['id'] : 0;
    $stlDir = __DIR__ . '/../public/stl/';

    // 1) Carpeta por id de producto: public/stl/{id}/
    $folderPath = $stlDir . $id;
    if ($id > 0 && is_dir($folderPath)) {
        $files = listStlGlbInDir($folderPath);
        if (!empty($files)) {
            $out = [];
            foreach ($files as $name) {
                $out[] = asset('stl/' . $id . '/' . $name);
            }
            return $out;
        }
    }

    // 2) Carpeta por número en stl_url (ej. printables_1471971_... -> carpeta stl/1471971/)
    $stlUrl = isset($product['stl_url']) ? trim($product['stl_url']) : '';
    $haystack = $stlUrl;
    if (preg_match_all('/(\d{4,})/', $haystack, $m)) {
        foreach (array_unique($m[1]) as $folderId) {
            $folderPath = $stlDir . $folderId;
            if (is_dir($folderPath)) {
                $files = listStlGlbInDir($folderPath);
                if (!empty($files)) {
                    $out = [];
                    foreach ($files as $name) {
                        $out[] = asset('stl/' . $folderId . '/' . $name);
                    }
                    return $out;
                }
            }
        }
    }

    $single = null;
    $stlUrl = isset($product['stl_url']) ? trim($product['stl_url']) : '';
    if ($stlUrl !== '' && (strpos($stlUrl, '.stl') !== false || strpos($stlUrl, '.glb') !== false)) {
        $single = asset($stlUrl);
    }
    return $single ? [$single] : [asset('glb/pato.glb')];
}

/**
 * Lista de rutas relativas (stl/...) de modelos para un producto (mismo orden que productModelAssets).
 * @param array|null $product
 * @return array Lista de paths como 'stl/123/file.stl'.
 */
function productModelPaths($product) {
    if (!is_array($product)) {
        return [];
    }
    $id = isset($product['id']) ? (int) $product['id'] : 0;
    $stlDir = __DIR__ . '/../public/stl/';

    $folderPath = $stlDir . $id;
    if ($id > 0 && is_dir($folderPath)) {
        $files = listStlGlbInDir($folderPath);
        if (!empty($files)) {
            $out = [];
            foreach ($files as $name) {
                $out[] = 'stl/' . $id . '/' . $name;
            }
            return $out;
        }
    }

    $stlUrl = isset($product['stl_url']) ? trim($product['stl_url']) : '';
    if (preg_match_all('/(\d{4,})/', $stlUrl, $m)) {
        foreach (array_unique($m[1]) as $folderId) {
            $folderPath = $stlDir . $folderId;
            if (is_dir($folderPath)) {
                $files = listStlGlbInDir($folderPath);
                if (!empty($files)) {
                    $out = [];
                    foreach ($files as $name) {
                        $out[] = 'stl/' . $folderId . '/' . $name;
                    }
                    return $out;
                }
            }
        }
    }

    if ($stlUrl !== '' && (strpos($stlUrl, '.stl') !== false || strpos($stlUrl, '.glb') !== false)) {
        return [$stlUrl];
    }
    return [];
}

/**
 * Lista de URLs de imágenes para un producto.
 * Si existe la carpeta public/images/products/{id}/, devuelve todas las imágenes (ordenadas).
 * Si no, devuelve un único elemento con image_url si existe.
 * @param array|null $product Debe contener 'id' y opcionalmente 'image_url'.
 * @return array Lista de URLs de imágenes.
 */
function productImageAssets($product) {
    if (!is_array($product)) {
        return [];
    }
    $id = isset($product['id']) ? (int) $product['id'] : 0;
    $imgDir = __DIR__ . '/../public/images/products/';
    $folderPath = $imgDir . $id;
    if ($id > 0 && is_dir($folderPath)) {
        $files = [];
        foreach (new DirectoryIterator($folderPath) as $f) {
            if ($f->isDot()) continue;
            $name = $f->getFilename();
            if (preg_match('/\.(jpe?g|png|gif|webp)$/i', $name)) {
                $files[] = $name;
            }
        }
        sort($files);
        $out = [];
        foreach ($files as $name) {
            $out[] = asset('images/products/' . $id . '/' . $name);
        }
        if (!empty($out)) return $out;
    }
    $iu = isset($product['image_url']) ? trim($product['image_url']) : '';
    if ($iu === '') return [];
    if (strpos($iu, 'http') === 0) {
        return [htmlspecialchars($iu)];
    }
    $rel = $iu;
    if (strpos($rel, '/') === 0) $rel = ltrim($rel, '/');
    if (preg_match('#^My3DStore/public/(.*)#', $rel, $m)) $rel = $m[1];
    if (preg_match('#^public/(images/.*)#', $rel, $m)) $rel = $m[1];
    return [asset($rel)];
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

