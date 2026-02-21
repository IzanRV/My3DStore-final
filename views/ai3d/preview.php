<?php
/**
 * Vista para previsualización de modelos 3D
 */
$pageTitle = 'Previsualización - My3DStore';
include __DIR__ . '/../../includes/header.php';
?>

<main class="flex-1 container mx-auto px-4 py-8 max-w-6xl">
    <h1 class="text-2xl font-bold mb-6">Previsualización del Modelo 3D</h1>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            Error: <?php echo htmlspecialchars($error); ?>
        </div>
    <?php elseif (!empty($fileInfo) && isset($fileInfo['preview_url'])): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-4">
                        <model-viewer
                            src="<?php echo htmlspecialchars($fileInfo['download_url'] ?? ''); ?>"
                            alt="Modelo 3D generado"
                            auto-rotate
                            camera-controls
                            style="width: 100%; height: 500px;"
                        ></model-viewer>
                    </div>
                </div>
            </div>
            <div>
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                        <h2 class="font-semibold text-lg">Información del Modelo</h2>
                    </div>
                    <div class="p-4 space-y-2">
                        <p><strong>Formato:</strong> <?php echo strtoupper($fileInfo['format'] ?? 'N/A'); ?></p>
                        <p><strong>Tamaño:</strong> <?php echo isset($fileInfo['size']) ? number_format($fileInfo['size'] / 1024, 2) . ' KB' : 'N/A'; ?></p>
                        <p><strong>Job ID:</strong> <?php echo htmlspecialchars($jobId ?? ''); ?></p>
                        <a href="<?php echo htmlspecialchars($fileInfo['download_url'] ?? '#'); ?>" 
                           class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:opacity-90">
                            Descargar Modelo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-amber-100 dark:bg-amber-900/30 border border-amber-400 text-amber-800 dark:text-amber-200 px-4 py-3 rounded">
            Previsualización no disponible para este formato de archivo.
        </div>
    <?php endif; ?>
</main>

<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.0.1/model-viewer.min.js"></script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
