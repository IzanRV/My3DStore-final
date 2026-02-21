<?php
/**
 * Cliente PHP para el microservicio de generación de modelos 3D
 *
 * Uso:
 *   $service = new AI3DService('http://localhost:8000');
 *   $result = $service->generateFromText('a red car', 'stl');
 */

class AI3DService {
    private $baseUrl;
    private $apiKey;
    private $timeout;

    /** @var array|null Last health check result (url, httpCode, curlError, elapsedMs) for debug */
    private $lastHealthCheckResult = null;

    public function __construct($baseUrl = 'http://localhost:8000', $apiKey = null, $timeout = 30) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
    }

    /**
     * Genera un modelo 3D desde texto
     *
     * @param string $prompt Descripción del objeto 3D
     * @param string $outputFormat Formato de salida (stl, obj, glb)
     * @param string $modelType Tipo de modelo (shap-e, point-e, meshy, tripo3d)
     * @param string $quality Calidad (low, medium, high)
     * @return array Resultado con job_id y status
     */
    public function generateFromText(
        $prompt,
        $outputFormat = 'stl',
        $modelType = 'shap-e',
        $quality = 'medium'
    ) {
        $url = $this->baseUrl . '/api/v1/generate/text-to-3d';

        $data = [
            'prompt' => $prompt,
            'model_type' => $modelType,
            'output_format' => $outputFormat,
            'quality' => $quality
        ];

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Genera un modelo 3D desde una o más imágenes
     *
     * @param array $imagePaths Rutas de las imágenes
     * @param string $outputFormat Formato de salida
     * @param string $modelType Tipo de modelo (triposr)
     * @param string $quality Calidad
     * @return array Resultado con job_id y status
     */
    public function generateFromImages(
        $imagePaths,
        $outputFormat = 'stl',
        $modelType = 'triposr',
        $quality = 'medium'
    ) {
        $url = $this->baseUrl . '/api/v1/generate/image-to-3d';

        // Preparar archivos para multipart/form-data
        $files = [];
        foreach ($imagePaths as $index => $imagePath) {
            if (file_exists($imagePath)) {
                $files["files[$index]"] = new CURLFile($imagePath);
            }
        }

        $data = array_merge($files, [
            'model_type' => $modelType,
            'output_format' => $outputFormat,
            'quality' => $quality
        ]);

        return $this->makeRequest('POST', $url, $data, true);
    }

    /**
     * Consulta el estado de un job
     *
     * @param string $jobId ID del job
     * @return array Estado del job
     */
    public function getJobStatus($jobId) {
        $url = $this->baseUrl . '/api/v1/generate/status/' . urlencode($jobId);
        return $this->makeRequest('GET', $url);
    }

    /**
     * Obtiene información detallada de un job
     *
     * @param string $jobId ID del job
     * @return array Información del job
     */
    public function getJob($jobId) {
        $url = $this->baseUrl . '/api/v1/jobs/' . urlencode($jobId);
        return $this->makeRequest('GET', $url);
    }

    /**
     * Descarga el archivo generado
     *
     * @param string $jobId ID del job
     * @param string $savePath Ruta donde guardar el archivo
     * @return bool True si se descargó correctamente
     */
    public function downloadFile($jobId, $savePath) {
        $url = $this->baseUrl . '/api/v1/files/download/' . urlencode($jobId);

        $ch = curl_init($url);
        $fp = fopen($savePath, 'wb');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout * 2);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        if ($this->apiKey) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->apiKey
            ]);
        }

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (!$success || $httpCode !== 200) {
            @unlink($savePath);
            return false;
        }

        return true;
    }

    /**
     * Obtiene información del archivo para previsualización
     *
     * @param string $jobId ID del job
     * @return array Información del archivo
     */
    public function getFileInfo($jobId) {
        $url = $this->baseUrl . '/api/v1/files/preview/' . urlencode($jobId);
        return $this->makeRequest('GET', $url);
    }

    /**
     * Espera a que un job se complete
     *
     * @param string $jobId ID del job
     * @param int $maxWaitSeconds Tiempo máximo de espera
     * @param int $pollInterval Intervalo entre consultas (segundos)
     * @return array Estado final del job
     */
    public function waitForJob(
        $jobId,
        $maxWaitSeconds = 300,
        $pollInterval = 2
    ) {
        $startTime = time();

        while (true) {
            $status = $this->getJobStatus($jobId);

            if (!isset($status['status'])) {
                throw new Exception('Error obteniendo estado del job');
            }

            $currentStatus = $status['status'];

            if ($currentStatus === 'completed' || $currentStatus === 'failed') {
                return $status;
            }

            if (time() - $startTime > $maxWaitSeconds) {
                throw new Exception('Tiempo de espera excedido');
            }

            sleep($pollInterval);
        }
    }

    /**
     * Realiza una petición HTTP
     *
     * @param string $method Método HTTP
     * @param string $url URL
     * @param array $data Datos a enviar
     * @param bool $multipart Si es multipart/form-data
     * @return array Respuesta
     */
    private function makeRequest($method, $url, $data = [], $multipart = false) {
        $ch = curl_init($url);

        $headers = ['Content-Type: application/json'];

        if ($this->apiKey) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }

        if ($multipart) {
            $headers = [];
            if ($this->apiKey) {
                $headers[] = 'Authorization: Bearer ' . $this->apiKey;
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Error en la petición: $error");
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMsg = isset($decoded['detail']) ? $decoded['detail'] : 'Error HTTP ' . $httpCode;
            throw new Exception($errorMsg);
        }

        return $decoded;
    }

    /**
     * Verifica si el servicio está activo
     *
     * @return bool True si está activo
     */
    public function isHealthy() {
        try {
            $url = $this->baseUrl . '/health';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $t0 = microtime(true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            $this->lastHealthCheckResult = [
                'url' => $url,
                'httpCode' => $httpCode,
                'curlError' => $curlError ?: null,
                'elapsedMs' => round((microtime(true) - $t0) * 1000),
            ];
            return $httpCode === 200;
        } catch (Exception $e) {
            $this->lastHealthCheckResult = ['exception' => $e->getMessage()];
            return false;
        }
    }

    /**
     * Returns the last health check result for debugging (url, httpCode, curlError, elapsedMs).
     * @return array|null
     */
    public function getLastHealthCheckResult() {
        return $this->lastHealthCheckResult;
    }
}
