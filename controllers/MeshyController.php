<?php
require_once __DIR__ . '/../includes/functions.php';

class MeshyController {
    private $apiKey;
    private $apiBaseUrl;
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../config/meshy.php';
        $this->apiKey = $this->config['api_key'];
        $this->apiBaseUrl = $this->config['api_base_url'];
        
        if ($this->apiKey === 'TU_API_KEY_AQUI') {
            error_log('Meshy API Key no configurada. Configúrala en config/meshy.php');
        }
    }
    
    /**
     * Genera un modelo 3D desde texto usando Meshy AI
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
     * Genera modelo 3D desde texto
     */
    private function textToModel($prompt) {
        $url = $this->apiBaseUrl . '/text-to-3d';
        
        $data = [
            'prompt' => $prompt,
            'art_style' => 'realistic', // o 'stylized'
            'output_format' => 'glb'
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config['timeout'],
            CURLOPT_CONNECTTIMEOUT => $this->config['connect_timeout'],
            CURLOPT_SSL_VERIFYPEER => false,
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
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            $errorData = json_decode($response, true);
            $errorMessage = 'Error al crear tarea: HTTP ' . $httpCode;
            
            if (isset($errorData['message'])) {
                $errorMessage = $errorData['message'];
            } elseif (isset($errorData['error'])) {
                $errorMessage = $errorData['error'];
            }
            
            error_log("Meshy API Error: HTTP $httpCode - " . $errorMessage);
            
            return [
                'success' => false,
                'error' => $errorMessage
            ];
        }
        
        $data = json_decode($response, true);
        $taskId = $data['result'] ?? $data['id'] ?? null;
        
        if (!$taskId) {
            return [
                'success' => false,
                'error' => 'No se recibió ID de tarea'
            ];
        }
        
        return [
            'success' => true,
            'task_id' => $taskId
        ];
    }
    
    /**
     * Verifica el estado de una tarea
     */
    public function checkStatus() {
        header('Content-Type: application/json');
        
        $taskId = $_GET['task_id'] ?? '';
        
        if (empty($taskId)) {
            echo json_encode([
                'success' => false,
                'error' => 'ID de tarea no proporcionado'
            ]);
            return;
        }
        
        $url = $this->apiBaseUrl . '/text-to-3d/' . urlencode($taskId);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Accept: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
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
        $modelUrl = $data['model_urls']['glb'] ?? $data['model_url'] ?? null;
        $progress = $data['progress'] ?? 0;
        
        echo json_encode([
            'success' => true,
            'status' => $status,
            'model_url' => $modelUrl,
            'progress' => $progress
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
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
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
        
        $filename = 'generated_' . time() . '_' . uniqid() . '.glb';
        $filepath = $glbDir . $filename;
        
        if (file_put_contents($filepath, $modelContent)) {
            echo json_encode([
                'success' => true,
                'glb_path' => '/My3DStore/public/glb/generated/' . $filename
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Error al guardar el modelo'
            ]);
        }
    }
}
