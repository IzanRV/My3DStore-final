<?php
/**
 * Endpoint API para el microservicio de IA 3D
 *
 * Uso:
 *   POST /api/ai3d.php?action=generateFromText
 *   POST /api/ai3d.php?action=generateFromImages
 *   GET  /api/ai3d.php?action=getJobStatus&job_id=xxx
 *   GET  /api/ai3d.php?action=downloadModel&job_id=xxx
 */

// Cargar .env si existe (local); en Railway las variables las inyecta la plataforma
$envFile = __DIR__ . '/../../.env';
if (is_file($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\"'");
            if ($key !== '') {
                putenv("$key=$val");
                $_ENV[$key] = $val;
            }
        }
    }
}

require_once __DIR__ . '/../../controllers/AI3DController.php';

$controller = new AI3DController();
$controller->handle();
