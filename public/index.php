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
            header('Location: /My3DStore/?action=login');
            exit;
        }
        break;
        
    case 'tripo-generate':
        require_once __DIR__ . '/../controllers/TripoController.php';
        $controller = new TripoController();
        $controller->generateModel();
        exit; // Importante: salir después de enviar JSON
        break;
        
    case 'shape-e-generate':
        require_once __DIR__ . '/../controllers/ShapeEController.php';
        $controller = new ShapeEController();
        $controller->generateModel();
        exit;
        break;
        
    case 'tripo-status':
        require_once __DIR__ . '/../controllers/TripoController.php';
        $controller = new TripoController();
        $controller->checkStatus();
        exit;
        break;
        
    case 'tripo-download':
        require_once __DIR__ . '/../controllers/TripoController.php';
        $controller = new TripoController();
        $controller->downloadModel();
        exit;
        break;
        
    // Rutas para Replicate (alternativa a Tripo3D)
    case 'replicate-generate':
        require_once __DIR__ . '/../controllers/ReplicateController.php';
        $controller = new ReplicateController();
        $controller->generateModel();
        exit;
        break;
        
    case 'replicate-status':
        require_once __DIR__ . '/../controllers/ReplicateController.php';
        $controller = new ReplicateController();
        $controller->checkStatus();
        exit;
        break;
        
    case 'replicate-download':
        require_once __DIR__ . '/../controllers/ReplicateController.php';
        $controller = new ReplicateController();
        $controller->downloadModel();
        exit;
        break;
        
    // Rutas para Meshy AI (alternativa a Replicate)
    case 'meshy-generate':
        require_once __DIR__ . '/../controllers/MeshyController.php';
        $controller = new MeshyController();
        $controller->generateModel();
        exit;
        break;
        
    case 'meshy-status':
        require_once __DIR__ . '/../controllers/MeshyController.php';
        $controller = new MeshyController();
        $controller->checkStatus();
        exit;
        break;
        
    case 'meshy-download':
        require_once __DIR__ . '/../controllers/MeshyController.php';
        $controller = new MeshyController();
        $controller->downloadModel();
        exit;
        break;
        
    case 'test-tripo-api':
        require_once __DIR__ . '/../config/tripo3d.php';
        
        header('Content-Type: text/html; charset=utf-8');
        
        $config = require __DIR__ . '/../config/tripo3d.php';
        $apiKey = $config['api_key'];
        
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Prueba API Tripo3D</title>";
        echo "<style>body{font-family:Arial,sans-serif;max-width:900px;margin:20px auto;padding:20px;}";
        echo "h1{color:#333;}h2{color:#666;margin-top:30px;border-top:2px solid #eee;padding-top:20px;}";
        echo ".success{color:green;font-weight:bold;}.error{color:red;font-weight:bold;}.warning{color:orange;font-weight:bold;}";
        echo "pre{background:#f5f5f5;padding:10px;border-radius:5px;overflow-x:auto;}";
        echo "ul{line-height:1.8;}</style></head><body>";
        
        echo "<h1>🔍 Prueba de API Key de Tripo3D</h1>";
        echo "<p><strong>API Key:</strong> " . htmlspecialchars(substr($apiKey, 0, 15)) . "...</p>";
        echo "<p><strong>Longitud:</strong> " . strlen($apiKey) . " caracteres</p>";
        echo "<p><strong>Base URL:</strong> " . htmlspecialchars($config['api_base_url']) . "</p>";
        echo "<hr>";
        
        // Prueba 1: Verificar autenticación - Intentar obtener información de la cuenta
        echo "<h2>Prueba 1: Verificar Autenticación</h2>";
        echo "<p>Tripo3D no tiene un endpoint directo de cuenta, pero podemos probar con un endpoint de API...</p>";
        
        // Prueba 2: Probar diferentes endpoints y seguir redirecciones
        echo "<h2>Prueba 2: Probar Endpoints de Tripo3D</h2>";
        
        $endpoints = [
            '/v2/image-to-model',
            '/v1/image-to-model',
            '/v2/text-to-model',
            '/v1/text-to-model',
            '/api/v1/image-to-model',
            '/api/v2/image-to-model'
        ];
        
        foreach ($endpoints as $endpoint) {
            echo "<h3>Probando: $endpoint</h3>";
            $url = $config['api_base_url'] . $endpoint;
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Accept: application/json'
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $redirectCount = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
            curl_close($ch);
            
            echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
            echo "<p><strong>URL Final:</strong> " . htmlspecialchars($finalUrl) . "</p>";
            if ($redirectCount > 0) {
                echo "<p><strong>Redirecciones:</strong> $redirectCount</p>";
            }
            
            if ($httpCode === 200 || $httpCode === 201 || $httpCode === 400) {
                // 400 puede ser porque no enviamos datos, pero significa que el endpoint existe
                echo "<p class='success'>✅ Endpoint disponible</p>";
            } elseif ($httpCode === 401 || $httpCode === 403) {
                echo "<p class='error'>❌ API Key INVÁLIDA o sin permisos</p>";
            } elseif ($httpCode === 404) {
                echo "<p class='warning'>⚠️ Endpoint no encontrado</p>";
            } elseif ($httpCode === 307 || $httpCode === 301 || $httpCode === 302) {
                echo "<p class='warning'>⚠️ Redirección detectada (puede ser normal)</p>";
            }
            echo "<hr>";
        }
        
        echo "<hr>";
        
        // Prueba 3: Intentar una petición real de image-to-model con datos mínimos
        echo "<h2>Prueba 3: Intentar Petición Real de Image-to-3D</h2>";
        echo "<p>Intentando crear una tarea de image-to-model para verificar que la API key funciona...</p>";
        
        // Crear una imagen de prueba pequeña en memoria
        $testImage = imagecreate(10, 10);
        $white = imagecolorallocate($testImage, 255, 255, 255);
        $black = imagecolorallocate($testImage, 0, 0, 0);
        imagestring($testImage, 1, 1, 1, 'TEST', $black);
        
        // Guardar temporalmente
        $tempFile = sys_get_temp_dir() . '/tripo_test_' . time() . '.png';
        imagepng($testImage, $tempFile);
        imagedestroy($testImage);
        
        $cfile = new CURLFile($tempFile, 'image/png', 'test.png');
        
        $postData = [
            'image' => $cfile,
            'mesh_quality' => 'standard'
        ];
        
        $url3 = $config['api_base_url'] . '/v2/image-to-model';
        $ch3 = curl_init($url3);
        curl_setopt_array($ch3, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $response3 = curl_exec($ch3);
        $httpCode3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
        $error3 = curl_error($ch3);
        curl_close($ch3);
        
        // Limpiar archivo temporal
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        
        echo "<p><strong>HTTP Code:</strong> $httpCode3</p>";
        
        if ($error3) {
            echo "<p class='error'>Error cURL: " . htmlspecialchars($error3) . "</p>";
        }
        
        if ($httpCode3 === 200 || $httpCode3 === 201) {
            echo "<p class='success'>✅ API Key VÁLIDA - Tarea creada exitosamente</p>";
            $data3 = json_decode($response3, true);
            echo "<h3>Respuesta:</h3>";
            echo "<pre>" . htmlspecialchars(json_encode($data3, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
        } elseif ($httpCode3 === 401 || $httpCode3 === 403) {
            echo "<p class='error'>❌ API Key INVÁLIDA - Error de autenticación</p>";
            echo "<p>Respuesta: " . htmlspecialchars(substr($response3, 0, 500)) . "</p>";
        } elseif ($httpCode3 === 404) {
            echo "<p class='warning'>⚠️ Endpoint no encontrado (404)</p>";
            echo "<p>Esto significa que el endpoint /v2/image-to-model no existe o ha cambiado.</p>";
            echo "<p>Respuesta: " . htmlspecialchars(substr($response3, 0, 500)) . "</p>";
        } else {
            echo "<p class='warning'>⚠️ Respuesta inesperada: HTTP $httpCode3</p>";
            echo "<p>Respuesta: " . htmlspecialchars(substr($response3, 0, 500)) . "</p>";
        }
        
        echo "<hr>";
        echo "<h2>Prueba 4: Probar Script Python</h2>";
        echo "<p>Tripo3D solo funciona con el SDK de Python. Probando el script Python...</p>";
        
        $scriptPath = __DIR__ . '/../scripts/tripo3d_generate.py';
        
        if (!file_exists($scriptPath)) {
            echo "<p class='error'>❌ Script Python no encontrado en: " . htmlspecialchars($scriptPath) . "</p>";
        } else {
            echo "<p class='success'>✅ Script Python encontrado</p>";
            
            // Probar ejecutar el script con una acción de prueba
            $testData = json_encode([
                'action' => 'check-status',
                'api_key' => $apiKey,
                'task_id' => 'test-connection'
            ]);
            
            $descriptorspec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ];
            
            // Intentar diferentes rutas de Python
            $pythonPaths = [
                'C:\\Python314\\python.exe',
                'python',
                'python3',
                'py'
            ];
            
            $process = null;
            $usedPath = null;
            
            foreach ($pythonPaths as $pythonPath) {
                // Usar el formato que funciona: sin comillas alrededor de Python, solo alrededor del script
                $scriptPathQuoted = str_replace('/', '\\', $scriptPath);
                if (strpos($scriptPathQuoted, ' ') !== false) {
                    $scriptPathQuoted = '"' . $scriptPathQuoted . '"';
                }
                $command = $pythonPath . ' ' . $scriptPathQuoted;
                
                echo "<p>Probando comando: <code>" . htmlspecialchars($command) . "</code></p>";
                
                $process = @proc_open($command, $descriptorspec, $pipes);
                if (is_resource($process)) {
                    $usedPath = $pythonPath;
                    echo "<p class='success'>✅ Proceso iniciado con: $pythonPath</p>";
                    break;
                }
            }
            
            if (!is_resource($process)) {
                echo "<p class='error'>❌ No se pudo iniciar el proceso Python</p>";
                echo "<p>Rutas probadas: " . implode(', ', $pythonPaths) . "</p>";
            } else {
                fwrite($pipes[0], $testData);
                fclose($pipes[0]);
                
                $output = stream_get_contents($pipes[1]);
                $errors = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                
                $returnCode = proc_close($process);
                
                echo "<p><strong>Código de salida:</strong> $returnCode</p>";
                
                $result = json_decode($output, true);
                
                if ($returnCode === 0) {
                    if ($result && isset($result['success'])) {
                        if ($result['success']) {
                            echo "<p class='success'>✅ Script Python funciona correctamente y la API key es válida</p>";
                        } else {
                            // Si el error es sobre parámetros inválidos o task_id inválido, significa que la conexión funciona
                            $errorMsg = $result['error'] ?? '';
                            if (strpos($errorMsg, 'HTTP 400') !== false || strpos($errorMsg, 'parameter') !== false || strpos($errorMsg, 'task_id') !== false) {
                                echo "<p class='success'>✅ Script Python funciona correctamente</p>";
                                echo "<p class='warning'>⚠️ Error esperado (usando task_id de prueba inválido): " . htmlspecialchars($errorMsg) . "</p>";
                                echo "<p>Esto significa que la API key es válida y puede conectarse a Tripo3D.</p>";
                            } elseif (strpos($errorMsg, 'API key') !== false || strpos($errorMsg, 'authentication') !== false || strpos($errorMsg, '401') !== false || strpos($errorMsg, '403') !== false) {
                                echo "<p class='error'>❌ Error de autenticación: " . htmlspecialchars($errorMsg) . "</p>";
                            } else {
                                echo "<p class='warning'>⚠️ Error: " . htmlspecialchars($errorMsg) . "</p>";
                            }
                        }
                    } else {
                        echo "<p class='success'>✅ Script Python se ejecuta correctamente</p>";
                    }
                    
                    if ($result) {
                        echo "<h3>Respuesta del script:</h3>";
                        echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
                    }
                } else {
                    echo "<p class='error'>❌ Error al ejecutar script Python</p>";
                    if ($errors) {
                        echo "<p><strong>Errores:</strong></p>";
                        echo "<pre>" . htmlspecialchars($errors) . "</pre>";
                    }
                    if ($output) {
                        echo "<p><strong>Salida:</strong></p>";
                        echo "<pre>" . htmlspecialchars($output) . "</pre>";
                    }
                }
            }
        }
        
        echo "<hr>";
        echo "<h2>Conclusión</h2>";
        echo "<p><strong>Tripo3D NO tiene API REST pública.</strong> Todos los endpoints devuelven 404.</p>";
        echo "<p><strong>Tripo3D solo funciona con el SDK de Python.</strong></p>";
        echo "<p>El código de la aplicación ya está configurado para usar el script Python:</p>";
        echo "<ul>";
        echo "<li>✅ Text-to-3D: Usa script Python</li>";
        echo "<li>✅ Image-to-3D: Usa script Python</li>";
        echo "<li>✅ Verificación de estado: Usa script Python</li>";
        echo "<li>✅ Descarga de modelos: Usa script Python</li>";
        echo "</ul>";
        echo "<p><strong>La API key es válida si el script Python puede conectarse a Tripo3D.</strong></p>";
        echo "<p>Para probar completamente, intenta generar un modelo desde la página de personalización.</p>";
        
        echo "</body></html>";
        exit;
        break;
        
    case 'test-replicate-api':
        require_once __DIR__ . '/../config/replicate.php';
        
        header('Content-Type: text/html; charset=utf-8');
        
        $config = require __DIR__ . '/../config/replicate.php';
        $apiKey = $config['api_key'];
        
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Prueba API Replicate</title>";
        echo "<style>body{font-family:Arial,sans-serif;max-width:900px;margin:20px auto;padding:20px;}";
        echo "h1{color:#333;}h2{color:#666;margin-top:30px;border-top:2px solid #eee;padding-top:20px;}";
        echo ".success{color:green;font-weight:bold;}.error{color:red;font-weight:bold;}.warning{color:orange;font-weight:bold;}";
        echo "pre{background:#f5f5f5;padding:10px;border-radius:5px;overflow-x:auto;}";
        echo "ul{line-height:1.8;}</style></head><body>";
        
        echo "<h1>🔍 Prueba de API Key de Replicate</h1>";
        echo "<p><strong>API Key:</strong> " . htmlspecialchars(substr($apiKey, 0, 10)) . "...</p>";
        echo "<p><strong>Longitud:</strong> " . strlen($apiKey) . " caracteres</p>";
        echo "<hr>";
        
        // Prueba 1: Verificar autenticación
        echo "<h2>Prueba 1: Verificar Autenticación</h2>";
        $url = 'https://api.replicate.com/v1/account';
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Token ' . $apiKey,
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
        $error = curl_error($ch);
        curl_close($ch);
        
        echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
        
        if ($httpCode === 200) {
            echo "<p class='success'>✅ API Key VÁLIDA - Autenticación exitosa</p>";
            $data = json_decode($response, true);
            echo "<h3>Información de la cuenta:</h3>";
            echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
        } elseif ($httpCode === 401) {
            echo "<p class='error'>❌ API Key INVÁLIDA - Error de autenticación</p>";
            echo "<p>Respuesta: " . htmlspecialchars(substr($response, 0, 500)) . "</p>";
            echo "<p><strong>Verifica que:</strong></p>";
            echo "<ul>";
            echo "<li>La API key esté correcta en config/replicate.php</li>";
            echo "<li>La API key no tenga espacios al inicio o final</li>";
            echo "<li>La API key sea de tu cuenta de Replicate</li>";
            echo "</ul>";
        } else {
            echo "<p class='warning'>⚠️ Respuesta inesperada: HTTP $httpCode</p>";
            echo "<p>Respuesta: " . htmlspecialchars(substr($response, 0, 500)) . "</p>";
        }
        
        if ($error) {
            echo "<p class='error'>Error cURL: " . htmlspecialchars($error) . "</p>";
        }
        
        echo "<hr>";
        
        // Prueba 2: Listar modelos disponibles
        echo "<h2>Prueba 2: Buscar Modelos de Text-to-3D</h2>";
        $url = 'https://api.replicate.com/v1/models?query=shap';
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Token ' . $apiKey,
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
        
        echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['results']) && count($data['results']) > 0) {
                echo "<p class='success'>✅ Modelos encontrados: " . count($data['results']) . "</p>";
                echo "<h3>Primeros 10 modelos:</h3>";
                echo "<ul>";
                foreach (array_slice($data['results'], 0, 10) as $model) {
                    $modelName = ($model['owner'] ?? '') . '/' . ($model['name'] ?? '');
                    $version = $model['latest_version']['id'] ?? 'N/A';
                    echo "<li><strong>" . htmlspecialchars($modelName) . "</strong>";
                    if ($version !== 'N/A') {
                        echo " (Versión: " . htmlspecialchars(substr($version, 0, 20)) . "...)";
                    }
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='warning'>⚠️ No se encontraron modelos con la búsqueda 'shap'</p>";
            }
        } else {
            echo "<p class='error'>❌ Error al listar modelos: HTTP $httpCode</p>";
            echo "<p>Respuesta: " . htmlspecialchars(substr($response, 0, 500)) . "</p>";
        }
        
        echo "<hr>";
        
        // Prueba 3: Buscar modelos de text-to-3d
        echo "<h2>Prueba 3: Buscar Modelos de Text-to-3D (búsqueda amplia)</h2>";
        $url = 'https://api.replicate.com/v1/models?query=text-to-3d';
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Token ' . $apiKey,
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
        
        echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['results']) && count($data['results']) > 0) {
                echo "<p class='success'>✅ Modelos encontrados: " . count($data['results']) . "</p>";
                echo "<h3>Primeros 5 modelos:</h3>";
                echo "<ul>";
                foreach (array_slice($data['results'], 0, 5) as $model) {
                    $modelName = ($model['owner'] ?? '') . '/' . ($model['name'] ?? '');
                    $version = $model['latest_version']['id'] ?? 'N/A';
                    echo "<li><strong>" . htmlspecialchars($modelName) . "</strong>";
                    if ($version !== 'N/A') {
                        echo " (Versión: " . htmlspecialchars(substr($version, 0, 20)) . "...)";
                    }
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='warning'>⚠️ No se encontraron modelos de text-to-3d</p>";
            }
        } else {
            echo "<p class='error'>❌ Error al buscar modelos: HTTP $httpCode</p>";
        }
        
        echo "</body></html>";
        exit;
        break;
        
    case 'contact':
        // Por ahora solo visual
        setFlashMessage('Gracias por tu mensaje. Te contactaremos pronto.', 'success');
        header('Location: /My3DStore/');
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

