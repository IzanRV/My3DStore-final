<?php
require_once __DIR__ . '/../includes/functions.php';

class TripoController {
    private $apiKey;
    private $apiBaseUrl;
    private $config;
    private $pythonPath;
    
    public function __construct() {
        // Cargar configuración
        $this->config = require __DIR__ . '/../config/tripo3d.php';
        $this->apiKey = $this->config['api_key'];
        $this->apiBaseUrl = $this->config['api_base_url'];
        
        if ($this->apiKey === 'TU_API_KEY_AQUI') {
            error_log('Tripo3D API Key no configurada. Configúrala en config/tripo3d.php');
        }
        
        // Detectar ruta de Python
        $this->pythonPath = $this->detectPythonPath();
    }
    
    /**
     * Detecta la ruta de Python disponible
     */
    private function detectPythonPath() {
        // Ruta conocida donde está instalado Python (verificada que funciona)
        $pythonPaths = [
            'C:\\Python314\\python.exe',  // Ruta completa encontrada y verificada
            'python',  // PATH
            'python3', // PATH alternativo
            'py',      // Launcher de Windows
        ];
        
        foreach ($pythonPaths as $path) {
            // Verificar que Python existe y puede importar tripo3d
            $output = [];
            $returnCode = 0;
            
            // En Windows, usar comillas simples para el comando Python interno
            if (strpos($path, '\\') !== false) {
                // Ruta completa con espacios, usar comillas
                $command = '"' . $path . '" -c "import tripo3d; print(\'OK\')" 2>&1';
            } else {
                // Comando en PATH
                $command = $path . ' -c "import tripo3d; print(\'OK\')" 2>&1';
            }
            
            @exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && (strpos(implode(' ', $output), 'OK') !== false || empty($output))) {
                error_log("Tripo3D: Python encontrado con SDK en: $path");
                return $path;
            }
        }
        
        // Usar la ruta conocida que funciona
        error_log("Tripo3D: Usando ruta conocida: C:\\Python314\\python.exe");
        return 'C:\\Python314\\python.exe';
    }
    
    /**
     * Genera un modelo 3D desde una imagen o texto usando Tripo3D API
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
        
        $generationType = $_POST['generation_type'] ?? 'image'; // 'image' o 'text'
        
        if ($generationType === 'text') {
            // Generar desde texto
            $prompt = trim($_POST['prompt'] ?? '');
            
            if (empty($prompt)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Por favor, ingresa una descripción del modelo'
                ]);
                return;
            }
            
            $meshQuality = $_POST['mesh_quality'] ?? 'standard';
            $result = $this->textToModel($prompt, $meshQuality);
            
        } else {
            // Generar desde imagen
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode([
                    'success' => false,
                    'error' => 'No se ha subido ninguna imagen o hubo un error en la subida'
                ]);
                return;
            }
            
            $imageFile = $_FILES['image'];
            
            // Validar tipo de archivo
            $allowedTypes = $this->config['allowed_types'];
            $fileType = mime_content_type($imageFile['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Formato de imagen no válido. Usa JPG, PNG o WEBP'
                ]);
                return;
            }
            
            // Validar tamaño
            if ($imageFile['size'] > $this->config['max_file_size']) {
                echo json_encode([
                    'success' => false,
                    'error' => 'La imagen es demasiado grande. Máximo 5MB'
                ]);
                return;
            }
            
            // Obtener parámetros opcionales
            $meshQuality = $_POST['mesh_quality'] ?? 'standard';
            $generateInParts = isset($_POST['generate_in_parts']) ? 'true' : 'false';
            $privacy = $_POST['privacy'] ?? 'public';
            
            // Subir imagen a Tripo3D y generar modelo
            $result = $this->uploadAndGenerate($imageFile, $meshQuality, $generateInParts, $privacy);
        }
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'task_id' => $result['task_id'],
                'message' => 'Modelo en proceso de generación'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['error']
            ]);
        }
    }
    
    /**
     * Genera modelo 3D desde texto usando el SDK de Tripo3D vía script Python
     */
    private function textToModel($prompt, $meshQuality = 'standard') {
        $scriptPath = __DIR__ . '/../scripts/tripo3d_generate.py';
        
        if (!file_exists($scriptPath)) {
            return [
                'success' => false,
                'error' => 'Script Python no encontrado. Asegúrate de que scripts/tripo3d_generate.py existe.'
            ];
        }
        
        // Preparar datos para el script Python
        $data = [
            'action' => 'text-to-model',
            'api_key' => $this->apiKey,
            'prompt' => $prompt,
            'mesh_quality' => $meshQuality
        ];
        
        // Ejecutar script Python
        $descriptorspec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];
        
        // Construir comando: en Windows, no usar comillas alrededor de la ruta de Python
        // Solo usar comillas alrededor del script si tiene espacios
        $scriptPathQuoted = str_replace('/', '\\', $scriptPath);
        if (strpos($scriptPathQuoted, ' ') !== false) {
            $scriptPathQuoted = '"' . $scriptPathQuoted . '"';
        }
        
        $command = $this->pythonPath . ' ' . $scriptPathQuoted;
        
        error_log("Tripo3D: Ejecutando: $command");
        
        $process = proc_open($command, $descriptorspec, $pipes);
        
        if (!is_resource($process)) {
            return [
                'success' => false,
                'error' => 'No se pudo ejecutar el script Python'
            ];
        }
        
        // Enviar datos JSON al script
        fwrite($pipes[0], json_encode($data));
        fclose($pipes[0]);
        
        // Leer respuesta
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        $returnCode = proc_close($process);
        
        // Log detallado para debugging
        error_log("Tripo3D textToModel - Return code: $returnCode");
        error_log("Tripo3D textToModel - Output: " . substr($output, 0, 500));
        if ($errors) {
            error_log("Tripo3D textToModel - Errors: " . substr($errors, 0, 500));
        }
        
        if ($returnCode !== 0) {
            // Intentar parsear el output aunque haya error (puede contener información útil)
            $result = json_decode($output, true);
            if ($result && isset($result['error'])) {
                return [
                    'success' => false,
                    'error' => $result['error']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Error al ejecutar script Python: ' . ($errors ?: 'Código de salida ' . $returnCode) . ($output ? ' - ' . substr($output, 0, 200) : '')
            ];
        }
        
        $result = json_decode($output, true);
        
        if (!$result) {
            error_log("Tripo3D: No se pudo parsear respuesta JSON. Output completo: $output");
            return [
                'success' => false,
                'error' => 'Error al parsear respuesta del script Python: ' . substr($output, 0, 200)
            ];
        }
        
        // Log del resultado
        if (isset($result['success']) && !$result['success']) {
            error_log("Tripo3D textToModel error: " . ($result['error'] ?? 'Error desconocido'));
        } else if (isset($result['success']) && $result['success']) {
            error_log("Tripo3D textToModel success: task_id = " . ($result['task_id'] ?? 'N/A'));
        }
        
        return $result;
        
        /* Código anterior - los endpoints devuelven 404, comentado
        $postData = json_encode([
            'prompt' => $prompt,
            'mesh_quality' => $meshQuality,
            'format' => 'glb'
        ]);
        
        $endpoints = [
            '/v2/text-to-model',
            '/v1/text-to-model'
        ];
        
        $lastError = null;
        $lastResponse = null;
        
        foreach ($endpoints as $endpoint) {
            $ch = curl_init($this->apiBaseUrl . $endpoint);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $this->apiKey,
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ],
                    CURLOPT_POSTFIELDS => $postData,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_TIMEOUT => $this->config['timeout'] ?? 120,
                    CURLOPT_CONNECTTIMEOUT => $this->config['connect_timeout'] ?? 30
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($httpCode === 404) {
                    $lastError = "Endpoint no encontrado: $this->apiBaseUrl$endpoint";
                    continue;
                }
                
                if ($httpCode === 200 || $httpCode === 201) {
                    $data = json_decode($response, true);
                    $taskId = $data['task_id'] ?? $data['id'] ?? null;
                    if ($taskId) {
                        return ['success' => true, 'task_id' => $taskId];
                    }
                }
            }
        
        return [
            'success' => false,
            'error' => $lastError ?? 'No se pudo conectar con la API'
        ];
        */
        $postData = json_encode([
            'prompt' => $prompt,
            'mesh_quality' => $meshQuality,
            'format' => 'glb'
        ]);
        
        // Lista de endpoints a probar (v2 es la versión actual según redirección 307)
        $endpoints = [
            '/v2/text-to-model',  // Versión actual (redirección desde v1)
            '/v1/text-to-model'   // Versión antigua (redirige a v2)
        ];
        
        $lastError = null;
        $lastResponse = null;
        
        // Probar cada endpoint
        foreach ($endpoints as $endpoint) {
            $ch = curl_init($this->apiBaseUrl . $endpoint);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $this->apiKey,
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ],
                    CURLOPT_POSTFIELDS => $postData,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_SSL_VERIFYPEER => false, // Temporalmente desactivar verificación SSL
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_TIMEOUT => $this->config['timeout'] ?? 120,
                    CURLOPT_CONNECTTIMEOUT => $this->config['connect_timeout'] ?? 30
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL); // Obtener antes de cerrar
                $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                curl_close($ch);
                
                $fullUrl = $this->apiBaseUrl . $endpoint;
                // Log para debugging
                error_log("Tripo3D Text-to-Model: $fullUrl | HTTP: $httpCode | Final URL: $finalUrl | Response: " . substr($response, 0, 500));
                
                if ($error) {
                    $lastError = "Error de conexión en $fullUrl: $error";
                    continue;
                }
                
                // Si es 307 (redirección) y CURLOPT_FOLLOWLOCATION no funcionó, seguir manualmente
                if (($httpCode === 307 || $httpCode === 301 || $httpCode === 302) && empty($response)) {
                    // Intentar extraer Location del header si está disponible
                    // O usar la URL de redirección si curl la detectó
                    if ($redirectUrl) {
                        error_log("Tripo3D: Redirección detectada a: $redirectUrl");
                        // Intentar con la URL de redirección
                        $ch2 = curl_init($redirectUrl);
                        curl_setopt_array($ch2, [
                            CURLOPT_POST => true,
                            CURLOPT_HTTPHEADER => [
                                'Authorization: Bearer ' . $this->apiKey,
                                'Content-Type: application/json',
                                'Accept: application/json'
                            ],
                            CURLOPT_POSTFIELDS => $postData,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_MAXREDIRS => 5,
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_TIMEOUT => $this->config['timeout'] ?? 120,
                            CURLOPT_CONNECTTIMEOUT => $this->config['connect_timeout'] ?? 30
                        ]);
                        $response = curl_exec($ch2);
                        $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                        curl_close($ch2);
                        error_log("Tripo3D después de redirección: HTTP $httpCode | Response: " . substr($response, 0, 500));
                    }
                }
                
                // Si es 404, probar siguiente endpoint
                if ($httpCode === 404) {
                    $lastError = "Endpoint no encontrado: $fullUrl";
                    $lastResponse = $response;
                    continue;
                }
                
                // Si es timeout, probar siguiente
                if ($httpCode === 0) {
                    $lastError = "Timeout en $fullUrl";
                    continue;
                }
                
                // Si es 200 o 201, procesar respuesta
                if ($httpCode === 200 || $httpCode === 201) {
                    $data = json_decode($response, true);
                    
                    // Log completo de la respuesta para debugging
                    error_log("Tripo3D Success Response: " . json_encode($data));
                    
                    // Extraer task_id
                    $taskId = null;
                    if (isset($data['task_id'])) {
                        $taskId = $data['task_id'];
                    } elseif (isset($data['id'])) {
                        $taskId = $data['id'];
                    } elseif (isset($data['data']['task_id'])) {
                        $taskId = $data['data']['task_id'];
                    } elseif (isset($data['data']['id'])) {
                        $taskId = $data['data']['id'];
                    } elseif (isset($data['task']['id'])) {
                        $taskId = $data['task']['id'];
                    }
                    
                    if ($taskId) {
                        return [
                            'success' => true,
                            'task_id' => $taskId,
                            'endpoint_used' => $fullUrl
                        ];
                    } else {
                        // Si no hay task_id pero la respuesta fue exitosa, loguear para debugging
                        error_log("Tripo3D: Respuesta exitosa pero sin task_id. Respuesta completa: " . $response);
                        $lastError = "Respuesta exitosa pero formato inesperado. Respuesta: " . substr($response, 0, 200);
                    }
                }
                
                // Si es otro código de error, guardar para reportar
                if ($httpCode >= 400) {
                    $errorData = json_decode($response, true);
                    $lastError = $errorData['message'] ?? $errorData['error'] ?? "HTTP $httpCode en $fullUrl";
                    $lastResponse = $response;
                }
            }
        
        // Si llegamos aquí, ningún endpoint funcionó
        return [
            'success' => false,
            'error' => $lastError ?? 'No se pudo conectar con la API. Última respuesta: ' . substr($lastResponse ?? '', 0, 300)
        ];
    }
    
    /**
     * Verifica el estado de una tarea de generación
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
        
        $status = $this->getTaskStatus($taskId);
        echo json_encode($status);
    }
    
    /**
     * Descarga el modelo GLB generado
     */
    public function downloadModel() {
        header('Content-Type: application/json');
        
        $taskId = $_GET['task_id'] ?? '';
        
        if (empty($taskId)) {
            echo json_encode([
                'success' => false,
                'error' => 'ID de tarea no proporcionado'
            ]);
            return;
        }
        
        // Usar script Python para descargar el modelo
        $scriptPath = __DIR__ . '/../scripts/tripo3d_generate.py';
        $glbDir = __DIR__ . '/../public/glb/generated/';
        
        if (!file_exists($scriptPath)) {
            echo json_encode([
                'success' => false,
                'error' => 'Script Python no encontrado'
            ]);
            return;
        }
        
        if (!is_dir($glbDir)) {
            mkdir($glbDir, 0755, true);
        }
        
        $data = [
            'action' => 'download',
            'api_key' => $this->apiKey,
            'task_id' => $taskId,
            'output_dir' => $glbDir
        ];
        
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        
        // Construir comando: en Windows, no usar comillas alrededor de la ruta de Python
        // Solo usar comillas alrededor del script si tiene espacios
        $scriptPathQuoted = str_replace('/', '\\', $scriptPath);
        if (strpos($scriptPathQuoted, ' ') !== false) {
            $scriptPathQuoted = '"' . $scriptPathQuoted . '"';
        }
        
        $command = $this->pythonPath . ' ' . $scriptPathQuoted;
        
        error_log("Tripo3D: Ejecutando: $command");
        
        $process = proc_open($command, $descriptorspec, $pipes);
        
        if (!is_resource($process)) {
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo ejecutar el script Python'
            ]);
            return;
        }
        
        fwrite($pipes[0], json_encode($data));
        fclose($pipes[0]);
        
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        proc_close($process);
        
        $result = json_decode($output, true);
        
        if (!$result || !$result['success']) {
            echo json_encode([
                'success' => false,
                'error' => $result['error'] ?? 'Error al descargar modelo: ' . $output
            ]);
            return;
        }
        
        // Convertir ruta absoluta a ruta relativa para la web
        $glbPath = $result['glb_path'];
        $relativePath = str_replace(__DIR__ . '/../public', '/My3DStore/public', $glbPath);
        $relativePath = str_replace('\\', '/', $relativePath);
        
        echo json_encode([
            'success' => true,
            'glb_path' => $relativePath
        ]);
    }
    
    /**
     * Sube imagen y genera modelo en Tripo3D
     */
    private function uploadAndGenerate($imageFile, $meshQuality, $generateInParts, $privacy) {
        $scriptPath = __DIR__ . '/../scripts/tripo3d_generate.py';
        
        if (!file_exists($scriptPath)) {
            return [
                'success' => false,
                'error' => 'Script Python no encontrado. Asegúrate de que scripts/tripo3d_generate.py existe.'
            ];
        }
        
        // La imagen ya está en el servidor, usar su ruta temporal
        $imagePath = $imageFile['tmp_name'];
        
        if (!file_exists($imagePath)) {
            return [
                'success' => false,
                'error' => 'Archivo de imagen no encontrado'
            ];
        }
        
        // Preparar datos para el script Python
        $data = [
            'action' => 'image-to-model',
            'api_key' => $this->apiKey,
            'image_path' => $imagePath,
            'mesh_quality' => $meshQuality,
            'generate_in_parts' => $generateInParts === 'true' || $generateInParts === true,
            'privacy' => $privacy
        ];
        
        // Ejecutar script Python
        $descriptorspec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];
        
        // Construir comando: en Windows, no usar comillas alrededor de la ruta de Python
        // Solo usar comillas alrededor del script si tiene espacios
        $scriptPathQuoted = str_replace('/', '\\', $scriptPath);
        if (strpos($scriptPathQuoted, ' ') !== false) {
            $scriptPathQuoted = '"' . $scriptPathQuoted . '"';
        }
        
        $command = $this->pythonPath . ' ' . $scriptPathQuoted;
        
        error_log("Tripo3D: Ejecutando: $command");
        
        $process = proc_open($command, $descriptorspec, $pipes);
        
        if (!is_resource($process)) {
            return [
                'success' => false,
                'error' => 'No se pudo ejecutar el script Python'
            ];
        }
        
        // Enviar datos JSON al script
        fwrite($pipes[0], json_encode($data));
        fclose($pipes[0]);
        
        // Leer respuesta
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        $returnCode = proc_close($process);
        
        if ($returnCode !== 0) {
            error_log("Tripo3D Python script error: $errors");
            return [
                'success' => false,
                'error' => 'Error al ejecutar script Python: ' . ($errors ?: 'Código de salida ' . $returnCode)
            ];
        }
        
        $result = json_decode($output, true);
        
        if (!$result) {
            return [
                'success' => false,
                'error' => 'Error al parsear respuesta del script Python: ' . $output
            ];
        }
        
        return $result;
    }
    
    /**
     * Obtiene el estado de una tarea
     */
    private function getTaskStatus($taskId) {
        $scriptPath = __DIR__ . '/../scripts/tripo3d_generate.py';
        
        if (!file_exists($scriptPath)) {
            return [
                'success' => false,
                'error' => 'Script Python no encontrado'
            ];
        }
        
        $data = [
            'action' => 'check-status',
            'api_key' => $this->apiKey,
            'task_id' => $taskId
        ];
        
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        
        // Construir comando: en Windows, no usar comillas alrededor de la ruta de Python
        // Solo usar comillas alrededor del script si tiene espacios
        $scriptPathQuoted = str_replace('/', '\\', $scriptPath);
        if (strpos($scriptPathQuoted, ' ') !== false) {
            $scriptPathQuoted = '"' . $scriptPathQuoted . '"';
        }
        
        $command = $this->pythonPath . ' ' . $scriptPathQuoted;
        
        error_log("Tripo3D: Ejecutando: $command");
        
        $process = proc_open($command, $descriptorspec, $pipes);
        
        if (!is_resource($process)) {
            return [
                'success' => false,
                'error' => 'No se pudo ejecutar el script Python'
            ];
        }
        
        fwrite($pipes[0], json_encode($data));
        fclose($pipes[0]);
        
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        proc_close($process);
        
        $result = json_decode($output, true);
        
        if (!$result) {
            return [
                'success' => false,
                'error' => 'Error al parsear respuesta: ' . $output
            ];
        }
        
        return $result;
    }
    
    /**
     * Obtiene la URL del modelo generado
     */
    private function getModelUrl($taskId) {
        $status = $this->getTaskStatus($taskId);
        
        // Verificar diferentes estados de éxito
        $successStatuses = ['completed', 'COMPLETED', 'SUCCESS', 'success', 'done', 'DONE'];
        
        if ($status['success'] && in_array($status['status'], $successStatuses) && $status['model_url']) {
            return $status['model_url'];
        }
        
        return null;
    }
}
