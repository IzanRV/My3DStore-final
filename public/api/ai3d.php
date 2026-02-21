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

require_once __DIR__ . '/../../controllers/AI3DController.php';

$controller = new AI3DController();
$controller->handle();
