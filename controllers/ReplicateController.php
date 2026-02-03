<?php
require_once __DIR__ . '/../includes/functions.php';

class ReplicateController {
    private $apiKey;
    private $apiBaseUrl;
    private $model;
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../config/replicate.php';
        $this->apiKey = $this->config['api_key'];
        $this->apiBaseUrl = $this->config['api_base_url'];
        $this->model = $this->config['model'];
        
        if ($this->apiKey === 'TU_API_KEY_AQUI') {
            error_log('Replicate API Key no configurada. Configúrala en config/replicate.php');
        }
    }
    
    /**
     * Busca modelos de text-to-3D disponibles y retorna el primero que encuentre
     */
    private function getModelVersion() {
        // Buscar modelos disponibles de text-to-3D
        $searchQueries = ['shap', 'text-to-3d', '3d'];
        
        foreach ($searchQueries as $query) {
            $url = $this->apiBaseUrl . '/models?query=' . urlencode($query);
            
            error_log("Replicate: Buscando modelos con query: $query");
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER => [
                    'Authorization: Token ' . $this->apiKey,
                    'Accept: application/json'
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                if (isset($data['results']) && is_array($data['results']) && !empty($data['results'])) {
                    // Buscar el primer modelo que tenga versión disponible
                    foreach ($data['results'] as $model) {
                        $modelName = ($model['owner'] ?? '') . '/' . ($model['name'] ?? '');
                        $version = $model['latest_version']['id'] ?? null;
                        
                        if ($version) {
                            error_log("Replicate: Modelo encontrado: $modelName, versión: $version");
                            $this->model = $modelName;
                            return $version;
                        }
                    }
                }
            }
        }
        
        // Si no encuentra nada, intentar con modelos conocidos directamente
        $knownModels = [
            'cjwbw/shap-e',
            'lucataco/shap-e',
            'openai/shap-e'
        ];
        
        foreach ($knownModels as $model) {
            $url = $this->apiBaseUrl . '/models/' . $model;
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER => [
                    'Authorization: Token ' . $this->apiKey,
                    'Accept: application/json'
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                if (isset($data['latest_version']['id'])) {
                    $version = $data['latest_version']['id'];
                    error_log("Replicate: Modelo conocido $model encontrado, versión: $version");
                    $this->model = $model;
                    return $version;
                }
            }
        }
        
        error_log("Replicate: No se encontró ningún modelo de text-to-3D disponible");
        return null;
    }
    
    
    /**
     * Genera un modelo 3D desde texto usando Replicate (Shap-E)
     */
    public function generateModel() {
        header('Content-Type: application/json');
        
        if (!isLoggedIn()) {
            echo json_encode([
                'success' => false,
                'error' => 'Debes iniciar sesión para generar modelos'
            ]);
            return;
        }
        
        $prompt = $_POST['prompt'] ?? '';
        
        if (empty($prompt)) {
            echo json_encode([
                'success' => false,
                'error' => 'El prompt no puede estar vacío'
            ]);
            return;
        }
        
        $result = $this->textToModel($prompt);
        echo json_encode($result);
    }
    
    /**
     * Genera modelo 3D desde texto usando Shap-E
     */
    private function textToModel($prompt) {
        // Replicate API: crear una predicción
        // Formato correcto: POST /v1/predictions
        $url = $this->apiBaseUrl . '/predictions';
        
        // Obtener la versión más reciente del modelo
        $version = $this->getModelVersion();
        
        if (!$version) {
            // Si no se puede obtener versión, informar al usuario
            return [
                'success' => false,
                'error' => 'No se encontraron modelos de text-to-3D disponibles en Replicate con tu API key. ' .
                          'Los modelos shap-e pueden no estar disponibles públicamente. ' .
                          'Considera usar Meshy.ai o Tripo3D como alternativa.'
            ];
        }
        
        $data = [
            'version' => $version,
            'input' => [
                'prompt' => $prompt
            ]
        ];
        
        error_log("Replicate: Creando predicción con modelo " . $this->model . ", versión $version");
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Token ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config['timeout'],
            CURLOPT_CONNECTTIMEOUT => $this->config['connect_timeout'],
            CURLOPT_SSL_VERIFYPEER => false, // Temporalmente desactivar para Windows/WAMP
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'Error de conexión: ' . $error
            ];
        }
        
        if ($httpCode !== 201 && $httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = 'Error al crear predicción: HTTP ' . $httpCode;
            
            if (isset($errorData['detail'])) {
                $errorMessage = is_array($errorData['detail']) 
                    ? json_encode($errorData['detail']) 
                    : $errorData['detail'];
            } elseif (isset($errorData['error'])) {
                $errorMessage = $errorData['error'];
            } elseif (isset($errorData['message'])) {
                $errorMessage = $errorData['message'];
            }
            
            error_log("Replicate API Error: HTTP $httpCode - " . $errorMessage . " - Full Response: " . $response);
            error_log("Replicate: Datos enviados: " . json_encode($data));
            
            // Si es 404 o error de versión, intentar obtener una versión válida nuevamente
            if ($httpCode === 404 || strpos(strtolower($errorMessage), 'version') !== false) {
                error_log("Replicate: Error de versión detectado, intentando obtener versión válida...");
                
                // Intentar obtener la versión nuevamente
                $newVersion = $this->getModelVersion();
                if ($newVersion && $newVersion !== ($data['version'] ?? null)) {
                    error_log("Replicate: Nueva versión obtenida: $newVersion, reintentando...");
                    // Reintentar con la nueva versión
                    $data['version'] = $newVersion;
                    unset($data['model']); // Asegurar que no hay conflicto
                    
                    $ch2 = curl_init($url);
                    curl_setopt_array($ch2, [
                        CURLOPT_POST => true,
                        CURLOPT_HTTPHEADER => [
                            'Authorization: Token ' . $this->apiKey,
                            'Content-Type: application/json',
                            'Accept: application/json'
                        ],
                        CURLOPT_POSTFIELDS => json_encode($data),
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => $this->config['timeout'],
                        CURLOPT_CONNECTTIMEOUT => $this->config['connect_timeout']
                    ]);
                    
                    $response2 = curl_exec($ch2);
                    $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                    curl_close($ch2);
                    
                    if ($httpCode2 === 201 || $httpCode2 === 200) {
                        $data2 = json_decode($response2, true);
                        $predictionId = $data2['id'] ?? null;
                        if ($predictionId) {
                            error_log("Replicate: Predicción creada exitosamente con nueva versión");
                            return [
                                'success' => true,
                                'prediction_id' => $predictionId,
                                'status_url' => $data2['urls']['get'] ?? null
                            ];
                        }
                    }
                }
                
                return [
                    'success' => false,
                    'error' => 'La versión del modelo no existe o no tienes permisos. Por favor, verifica tu API key en Replicate. Error: ' . $errorMessage
                ];
            }
            
            return [
                'success' => false,
                'error' => $errorMessage
            ];
        }
        
        $data = json_decode($response, true);
        
        if (!$data) {
            error_log("Replicate API: Respuesta no válida - " . substr($response, 0, 500));
            return [
                'success' => false,
                'error' => 'Respuesta inválida del servidor'
            ];
        }
        
        $predictionId = $data['id'] ?? null;
        
        if (!$predictionId) {
            error_log("Replicate API: No se recibió ID de predicción - " . json_encode($data));
            return [
                'success' => false,
                'error' => 'No se recibió ID de predicción. Respuesta: ' . json_encode($data)
            ];
        }
        
        return [
            'success' => true,
            'prediction_id' => $predictionId,
            'status_url' => $data['urls']['get'] ?? null
        ];
    }
    
    /**
     * Verifica el estado de una predicción
     */
    public function checkStatus() {
        header('Content-Type: application/json');
        
        $predictionId = $_GET['prediction_id'] ?? '';
        
        if (empty($predictionId)) {
            echo json_encode([
                'success' => false,
                'error' => 'ID de predicción no proporcionado'
            ]);
            return;
        }
        
        $url = $this->apiBaseUrl . '/predictions/' . urlencode($predictionId);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Token ' . $this->apiKey,
                'Accept: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener estado: HTTP ' . $httpCode
            ]);
            return;
        }
        
        $data = json_decode($response, true);
        
        $status = $data['status'] ?? 'unknown';
        $output = $data['output'] ?? null;
        
        // Replicate devuelve URLs de archivos cuando está completo
        $modelUrl = null;
        if ($status === 'succeeded' && $output) {
            error_log("Replicate: Status succeeded, output type: " . gettype($output) . ", output: " . json_encode($output));
            
            // Shap-E puede devolver múltiples formatos, buscar GLB o PLY
            if (is_array($output)) {
                foreach ($output as $url) {
                    if (is_string($url) && (strpos($url, '.glb') !== false || strpos($url, '.ply') !== false || strpos($url, '.obj') !== false)) {
                        $modelUrl = $url;
                        break;
                    }
                }
                // Si no encontramos en el array, tomar el primer elemento si es una URL
                if (!$modelUrl && !empty($output[0]) && filter_var($output[0], FILTER_VALIDATE_URL)) {
                    $modelUrl = $output[0];
                }
            } elseif (is_string($output)) {
                // Si es una URL directa
                if (filter_var($output, FILTER_VALIDATE_URL)) {
                    $modelUrl = $output;
                } elseif (strpos($output, '.glb') !== false || strpos($output, '.ply') !== false || strpos($output, '.obj') !== false) {
                    $modelUrl = $output;
                }
            }
        }
        
        // Calcular progreso aproximado basado en el estado
        $progress = 0;
        if ($status === 'starting') {
            $progress = 10;
        } elseif ($status === 'processing') {
            $progress = 50;
        } elseif ($status === 'succeeded') {
            $progress = 100;
        }
        
        echo json_encode([
            'success' => true,
            'status' => $status,
            'model_url' => $modelUrl,
            'progress' => $progress,
            'output' => $output
        ]);
    }
    
    /**
     * Descarga el modelo generado
     */
    public function downloadModel() {
        header('Content-Type: application/json');
        
        $modelUrl = $_GET['model_url'] ?? '';
        
        if (empty($modelUrl)) {
            echo json_encode([
                'success' => false,
                'error' => 'URL del modelo no proporcionada'
            ]);
            return;
        }
        
        // Descargar el modelo
        $ch = curl_init($modelUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 120
        ]);
        
        $modelContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$modelContent) {
            echo json_encode([
                'success' => false,
                'error' => 'Error al descargar el modelo'
            ]);
            return;
        }
        
        // Guardar en la carpeta glb
        $glbDir = __DIR__ . '/../public/glb/generated/';
        if (!is_dir($glbDir)) {
            mkdir($glbDir, 0755, true);
        }
        
        // Determinar extensión
        $extension = 'glb';
        if (strpos($modelUrl, '.ply') !== false) {
            $extension = 'ply';
        } elseif (strpos($modelUrl, '.obj') !== false) {
            $extension = 'obj';
        }
        
        $filename = 'generated_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $glbDir . $filename;
        
        if (file_put_contents($filepath, $modelContent)) {
            echo json_encode([
                'success' => true,
                'glb_path' => '/My3DStore/public/glb/generated/' . $filename,
                'format' => $extension
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Error al guardar el modelo'
            ]);
        }
    }
}
