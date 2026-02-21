<?php
/**
 * Controlador para integración con el microservicio de IA 3D
 */

require_once __DIR__ . '/../includes/AI3DService.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Product.php';

class AI3DController {
    private $aiService;
    private $outputDir;
    private $stlDir;
    private $logoDir;

    public function __construct() {
        // Configurar URL del microservicio (Docker: http://ai3d:8000)
        $serviceUrl = getenv('AI_3D_SERVICE_URL') ?: 'http://localhost:8000';
        $apiKey = getenv('AI_3D_SERVICE_API_KEY') ?: null;

        $this->aiService = new AI3DService($serviceUrl, $apiKey);
        $this->outputDir = __DIR__ . '/../public/generated_models';
        $this->stlDir = __DIR__ . '/../public/stl';
        $this->logoDir = __DIR__ . '/../public/images/logos';

        // Crear directorios si no existen
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
        if (!is_dir($this->stlDir)) {
            mkdir($this->stlDir, 0755, true);
        }
        if (!is_dir($this->logoDir)) {
            mkdir($this->logoDir, 0755, true);
        }
    }

    /**
     * Router de acciones
     */
    public function handle() {
        $action = $_GET['action'] ?? $_POST['action'] ?? null;

        if (!$action) {
            http_response_code(400);
            echo json_encode(['error' => 'Acción no especificada']);
            return;
        }

        switch ($action) {
            case 'generateFromText':
                $this->generateFromText();
                break;
            case 'generateFromImages':
                $this->generateFromImages();
                break;
            case 'getJobStatus':
                $this->getJobStatus();
                break;
            case 'downloadModel':
                $this->downloadModel();
                break;
            case 'saveAsProduct':
                $this->saveAsProduct();
                break;
            case 'previewModel':
                $this->previewModel();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Acción no encontrada']);
        }
    }

    /**
     * Genera un modelo 3D desde texto
     */
    public function generateFromText() {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['prompt']) || empty($input['prompt'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El prompt es requerido']);
                return;
            }

            $prompt = $input['prompt'];
            $outputFormat = $input['output_format'] ?? 'stl';
            $modelType = $input['model_type'] ?? 'shap-e';
            $quality = $input['quality'] ?? 'medium';

            // Verificar que el servicio esté activo
            if (!$this->aiService->isHealthy()) {
                http_response_code(503);
                echo json_encode(['error' => 'El servicio de IA no está disponible']);
                return;
            }

            // Iniciar generación
            $result = $this->aiService->generateFromText(
                $prompt,
                $outputFormat,
                $modelType,
                $quality
            );

            echo json_encode([
                'success' => true,
                'job_id' => $result['job_id'],
                'status' => $result['status'],
                'message' => $result['message']
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Genera un modelo 3D desde imágenes
     */
    public function generateFromImages() {
        header('Content-Type: application/json');

        try {
            if (!isset($_FILES['images']) || empty($_FILES['images'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Se requiere al menos una imagen']);
                return;
            }

            // Guardar imágenes temporalmente
            $imagePaths = [];
            $tempDir = sys_get_temp_dir();

            foreach ($_FILES['images']['tmp_name'] as $tmpName) {
                $imagePath = $tempDir . '/' . uniqid('img_') . '.jpg';
                move_uploaded_file($tmpName, $imagePath);
                $imagePaths[] = $imagePath;
            }

            $outputFormat = $_POST['output_format'] ?? 'stl';
            $modelType = $_POST['model_type'] ?? 'triposr';
            $quality = $_POST['quality'] ?? 'medium';

            // Verificar que el servicio esté activo
            if (!$this->aiService->isHealthy()) {
                http_response_code(503);
                echo json_encode(['error' => 'El servicio de IA no está disponible']);
                return;
            }

            // Iniciar generación
            $result = $this->aiService->generateFromImages(
                $imagePaths,
                $outputFormat,
                $modelType,
                $quality
            );

            // Limpiar imágenes temporales
            foreach ($imagePaths as $path) {
                @unlink($path);
            }

            echo json_encode([
                'success' => true,
                'job_id' => $result['job_id'],
                'status' => $result['status'],
                'message' => $result['message']
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Consulta el estado de un job
     */
    public function getJobStatus() {
        header('Content-Type: application/json');

        try {
            $jobId = $_GET['job_id'] ?? null;

            if (!$jobId) {
                http_response_code(400);
                echo json_encode(['error' => 'job_id es requerido']);
                return;
            }

            $status = $this->aiService->getJobStatus($jobId);
            echo json_encode($status);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Descarga un modelo generado
     */
    public function downloadModel() {
        try {
            $jobId = $_GET['job_id'] ?? null;

            if (!$jobId) {
                http_response_code(400);
                echo json_encode(['error' => 'job_id es requerido']);
                return;
            }

            // Obtener información del archivo
            $fileInfo = $this->aiService->getFileInfo($jobId);

            if (!isset($fileInfo['filename'])) {
                http_response_code(404);
                echo json_encode(['error' => 'Archivo no encontrado']);
                return;
            }

            // Descargar archivo
            $savePath = $this->outputDir . '/' . $fileInfo['filename'];

            if ($this->aiService->downloadFile($jobId, $savePath)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $fileInfo['filename'] . '"');
                readfile($savePath);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al descargar el archivo']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Guarda el modelo generado como producto en el catálogo (STL en public/stl, producto en BD).
     */
    public function saveAsProduct() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            startSession();
            if (!isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['error' => 'Debes iniciar sesión para añadir el modelo al catálogo']);
                return;
            }

            $jobId = $_POST['job_id'] ?? $_GET['job_id'] ?? null;
            $modelUrl = trim($_POST['model_url'] ?? $_GET['model_url'] ?? '');
            $prompt = trim($_POST['prompt'] ?? $_GET['prompt'] ?? '');
            $stlPath = null;

            if ($jobId) {
                // Publicar desde modelo generado por IA
                $fileInfo = $this->aiService->getFileInfo($jobId);
                if (!isset($fileInfo['filename']) || $fileInfo['filename'] === '') {
                    http_response_code(404);
                    echo json_encode(['error' => 'Archivo no encontrado para este job']);
                    return;
                }
                $originalName = $fileInfo['filename'];
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if ($ext !== 'stl' && $ext !== 'obj' && $ext !== 'glb') {
                    $ext = 'stl';
                }
                $safeBasename = 'ai_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $jobId) . '_' . time() . '.' . $ext;
                $stlPath = $this->stlDir . '/' . $safeBasename;
                if (!$this->aiService->downloadFile($jobId, $stlPath)) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Error al descargar el archivo del servicio']);
                    return;
                }
                $stlUrl = 'stl/' . $safeBasename;
                $productName = trim($_POST['name'] ?? '');
                if ($productName === '') {
                    $productName = $prompt !== ''
                        ? $this->extractProductName($prompt)
                        : 'Modelo generado por IA - ' . date('d/m/Y H:i');
                }
                $description = trim($_POST['description'] ?? '');
                if ($description === '') {
                    $description = 'Modelo 3D generado automáticamente por IA. '
                        . ($prompt !== '' ? 'Descripción: "' . $prompt . '". ' : '')
                        . 'Añadido al catálogo desde el personalizador.';
                }
            } elseif ($modelUrl !== '') {
                // Publicar desde modelo existente (personalización de producto)
                $modelUrl = str_replace('\\', '/', $modelUrl);
                if (strpos($modelUrl, '..') !== false || !preg_match('#^stl/[a-zA-Z0-9_\-./]+\.(stl|glb|obj)$#i', $modelUrl)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Ruta de modelo no válida']);
                    return;
                }
                $fullPath = $this->stlDir . '/' . preg_replace('#^stl/#', '', $modelUrl);
                if (!is_file($fullPath)) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Archivo del modelo no encontrado']);
                    return;
                }
                $stlUrl = $modelUrl;
                $productName = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                if ($productName === '') {
                    http_response_code(400);
                    echo json_encode(['error' => 'Indica un nombre para el nuevo producto']);
                    return;
                }
                if ($description === '') {
                    $description = $productName;
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Indica job_id (modelo IA) o model_url (modelo de un producto)']);
                return;
            }
            $price = isset($_POST['price']) ? floatval($_POST['price']) : 19.99;
            if ($price <= 0) {
                $price = 19.99;
            }
            $material = trim($_POST['material'] ?? 'PLA');
            $dimX = isset($_POST['dim_x']) ? floatval($_POST['dim_x']) : 100;
            $dimY = isset($_POST['dim_y']) ? floatval($_POST['dim_y']) : 100;
            $dimZ = isset($_POST['dim_z']) ? floatval($_POST['dim_z']) : 100;
            $color = trim($_POST['color'] ?? '');
            if ($color !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                $color = '';
            }
            $logoSide = trim($_POST['logo_side'] ?? '');
            $allowedSides = ['front', 'back', 'left', 'right', 'top', 'bottom'];
            if (!in_array($logoSide, $allowedSides, true)) {
                $logoSide = 'front';
            }
            $logoUrl = '';
            if (!empty($_FILES['logo']['tmp_name']) && is_uploaded_file($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $logoExt = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                if (in_array($logoExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $logoBasename = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $logoExt;
                    $logoPath = $this->logoDir . '/' . $logoBasename;
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath)) {
                        $logoUrl = 'images/logos/' . $logoBasename;
                    }
                }
            }
            $imageUrl = '';
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            $user = $userModel->findById($_SESSION['user_id'] ?? 0);
            $author = $user['name'] ?? '';

            $productModel = new Product();
            try {
                $newId = $productModel->createPublish($productName, $description, $price, $imageUrl, $stlUrl, $material, $dimX, $dimY, $dimZ, $author, $color, $logoUrl, $logoSide);
            } catch (Throwable $e) {
                if ($stlPath !== null) {
                    @unlink($stlPath);
                }
                http_response_code(500);
                echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
                return;
            }
            if (!$newId) {
                if ($stlPath !== null) {
                    @unlink($stlPath);
                }
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear el producto en la base de datos']);
                return;
            }

            $productUrl = url('product', ['id' => $newId]);
            $stlBasename = $jobId ? $safeBasename : basename($stlUrl);

            echo json_encode([
                'success' => true,
                'product_id' => (int) $newId,
                'product_url' => $productUrl,
                'name' => $productName,
                'stl_filename' => $stlBasename,
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    private function extractProductName($prompt) {
        $cleanName = mb_strtolower(trim($prompt));
        $actionWords = ['genera', 'crea', 'haz', 'diseña', 'hacer', 'construir', 'fabricar', 'crear', 'generar', 'diseñar'];
        foreach ($actionWords as $word) {
            $cleanName = preg_replace('/\b' . preg_quote($word, '/') . '\b/ui', '', $cleanName);
        }
        $cleanName = preg_replace('/^\s*(un|una|el|la|los|las|unos|unas)\s+/ui', '', trim($cleanName));
        $cleanName = preg_replace('/^\s*(a|an)\s+/ui', '', trim($cleanName));
        $cleanName = preg_replace('/\s+/', ' ', trim($cleanName));
        if ($cleanName !== '') {
            $cleanName = mb_strtoupper(mb_substr($cleanName, 0, 1)) . mb_substr($cleanName, 1);
        }
        if ($cleanName === '') {
            $cleanName = 'Modelo 3D';
        }
        return mb_substr($cleanName, 0, 200);
    }

    /**
     * Previsualiza un modelo 3D
     */
    public function previewModel() {
        $jobId = $_GET['job_id'] ?? null;

        if (!$jobId) {
            http_response_code(400);
            echo json_encode(['error' => 'job_id es requerido']);
            return;
        }

        try {
            $fileInfo = $this->aiService->getFileInfo($jobId);

            if (!isset($fileInfo['preview_url'])) {
                http_response_code(404);
                echo json_encode(['error' => 'Previsualización no disponible']);
                return;
            }

            include __DIR__ . '/../views/ai3d/preview.php';

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
