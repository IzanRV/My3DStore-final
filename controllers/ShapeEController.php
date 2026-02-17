<?php
require_once __DIR__ . '/../includes/functions.php';

class ShapeEController {
    private $pythonPath;
    private $outputDir;
    
    public function __construct() {
        // Detectar ruta de Python
        $this->pythonPath = $this->detectPythonPath();
        
        // Directorio de salida para modelos generados
        $this->outputDir = __DIR__ . '/../public/glb/generated/';
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }
    
    /**
     * Detecta la ruta de Python disponible con soporte CUDA
     * Prioriza Python 3.11/3.12 que tienen mejor soporte CUDA
     * En Docker, usa la variable de entorno PYTHON_PATH o python3 por defecto
     */
    private function detectPythonPath() {
        // Si estamos en Docker, usar variable de entorno o python3
        $pythonPathEnv = getenv('PYTHON_PATH');
        if ($pythonPathEnv) {
            return $pythonPathEnv;
        }
        
        // Si estamos en un contenedor Docker (verificar si estamos en /var/www/html)
        if (file_exists('/.dockerenv') || getenv('DOCKER_CONTAINER')) {
            return 'python3';
        }
        
        // Priorizar Python 3.11 y 3.12 (mejor soporte CUDA)
        $pythonPaths = [
            // Python 3.12 (recomendado para CUDA)
            'C:\\Python312\\python.exe',
            'C:\\Program Files\\Python312\\python.exe',
            'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python312\\python.exe',
            'py -3.12',
            // Python 3.11
            'C:\\Python311\\python.exe',
            'C:\\Program Files\\Python311\\python.exe',
            'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python311\\python.exe',
            'py -3.11',
            // Python 3.10
            'C:\\Python310\\python.exe',
            'C:\\Program Files\\Python310\\python.exe',
            'py -3.10',
            // Python genérico (última opción)
            'python',
            'python3',
            'py',
            // Python 3.14 (solo si no hay otros, pero sin CUDA)
            'C:\\Python314\\python.exe',
        ];
        
        $bestPython = null;
        $bestVersion = 0;
        $hasCuda = false;
        
        foreach ($pythonPaths as $path) {
            $output = [];
            $returnCode = 0;
            
            // Verificar que Python existe y tiene PyTorch
            if (strpos($path, '\\') !== false || strpos($path, 'py -') === 0) {
                $command = '"' . $path . '" -c "import torch; print(\'OK\')" 2>&1';
            } else {
                $command = $path . ' -c "import torch; print(\'OK\')" 2>&1';
            }
            
            @exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                // Verificar versión de Python y soporte CUDA
                $versionOutput = [];
                $cudaOutput = [];
                
                if (strpos($path, '\\') !== false || strpos($path, 'py -') === 0) {
                    @exec('"' . $path . '" -c "import sys; print(sys.version_info.major, sys.version_info.minor)" 2>&1', $versionOutput, $returnCode);
                    @exec('"' . $path . '" -c "import torch; print(torch.cuda.is_available())" 2>&1', $cudaOutput, $returnCode);
                } else {
                    @exec($path . ' -c "import sys; print(sys.version_info.major, sys.version_info.minor)" 2>&1', $versionOutput, $returnCode);
                    @exec($path . ' -c "import torch; print(torch.cuda.is_available())" 2>&1', $cudaOutput, $returnCode);
                }
                
                $versionStr = implode('', $versionOutput);
                $cudaStr = implode('', $cudaOutput);
                
                // Extraer versión mayor y menor
                if (preg_match('/(\d+)\.(\d+)/', $versionStr, $matches)) {
                    $major = (int)$matches[1];
                    $minor = (int)$matches[2];
                    $version = $major * 100 + $minor;
                    
                    $hasCudaHere = stripos($cudaStr, 'true') !== false || stripos($cudaStr, 'True') !== false;
                    
                    // Priorizar: Python 3.11/3.12 con CUDA > Python 3.11/3.12 sin CUDA > otras versiones con CUDA > otras versiones
                    if ($major === 3 && ($minor === 11 || $minor === 12)) {
                        if ($hasCudaHere && (!$hasCuda || $version > $bestVersion)) {
                            $bestPython = $path;
                            $bestVersion = $version;
                            $hasCuda = true;
                        } elseif (!$hasCuda && $version > $bestVersion) {
                            $bestPython = $path;
                            $bestVersion = $version;
                        }
                    } elseif ($hasCudaHere && !$hasCuda) {
                        $bestPython = $path;
                        $bestVersion = $version;
                        $hasCuda = true;
                    } elseif (!$bestPython || ($version > $bestVersion && !$hasCuda)) {
                        $bestPython = $path;
                        $bestVersion = $version;
                    }
                } else {
                    // Si no podemos detectar versión, usar la primera que funcione
                    if (!$bestPython) {
                        $bestPython = $path;
                    }
                }
            }
        }
        
        if ($bestPython) {
            error_log("Shape-E: Python encontrado en: $bestPython (versión: $bestVersion, CUDA: " . ($hasCuda ? 'Sí' : 'No') . ")");
            return $bestPython;
        }
        
        error_log("Shape-E: No se encontró Python con PyTorch. Usando fallback: python");
        return 'python';
    }
    
    /**
     * Genera un modelo 3D desde texto usando Shap-E localmente
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
        
        $prompt = trim($_POST['prompt'] ?? '');
        
        if (empty($prompt)) {
            echo json_encode([
                'success' => false,
                'error' => 'Por favor, ingresa una descripción del modelo'
            ]);
            return;
        }
        
        $result = $this->textToModel($prompt);
        
        if ($result['success']) {
            // Convertir ruta absoluta a ruta relativa para la web
            $glbPath = $result['glb_path'] ?? $result['ply_path'] ?? '';
            if ($glbPath) {
                $relativePath = str_replace(__DIR__ . '/../public', '/My3DStore/public', $glbPath);
                $relativePath = str_replace('\\', '/', $relativePath);
                
                echo json_encode([
                    'success' => true,
                    'glb_path' => $relativePath,
                    'message' => $result['message'] ?? 'Modelo generado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo determinar la ruta del modelo generado'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['error']
            ]);
        }
    }
    
    /**
     * Genera modelo 3D desde texto usando Shap-E localmente
     */
    private function textToModel($prompt) {
        $scriptPath = __DIR__ . '/../scripts/shape_e_generate.py';
        
        if (!file_exists($scriptPath)) {
            return [
                'success' => false,
                'error' => 'Script Python no encontrado. Asegúrate de que scripts/shape_e_generate.py existe.'
            ];
        }
        
        // Preparar datos para el script Python
        $data = [
            'prompt' => $prompt,
            'output_dir' => $this->outputDir
        ];
        
        // Ejecutar script Python
        $descriptorspec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];
        
        // Construir comando
        $scriptPathQuoted = str_replace('/', '\\', $scriptPath);
        if (strpos($scriptPathQuoted, ' ') !== false) {
            $scriptPathQuoted = '"' . $scriptPathQuoted . '"';
        }
        
        // Si pythonPath contiene espacios o es 'py -3.11', necesitamos manejarlo diferente
        $pythonCmd = $this->pythonPath;
        if (strpos($pythonCmd, ' ') !== false && strpos($pythonCmd, 'py -') === 0) {
            // Para 'py -3.11', necesitamos mantenerlo junto
            $command = $pythonCmd . ' ' . $scriptPathQuoted;
        } else {
            // Para rutas normales, usar directamente
            $command = $pythonCmd . ' ' . $scriptPathQuoted;
        }
        
        error_log("Shape-E: Ejecutando: $command");
        
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
        
        // Leer respuesta (puede tardar varios minutos)
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        $returnCode = proc_close($process);
        
        // Log detallado
        error_log("Shape-E - Return code: $returnCode");
        error_log("Shape-E - Output: " . substr($output, 0, 500));
        if ($errors) {
            error_log("Shape-E - Errors: " . substr($errors, 0, 500));
        }
        
        if ($returnCode !== 0) {
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
            error_log("Shape-E: No se pudo parsear respuesta JSON. Output completo: $output");
            return [
                'success' => false,
                'error' => 'Error al parsear respuesta del script Python: ' . substr($output, 0, 200)
            ];
        }
        
        return $result;
    }
}
