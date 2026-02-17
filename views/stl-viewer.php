<?php
$pageTitle = 'Visor 3D - My3DStore';
$loadSTLViewer = true; // Activar carga de Three.js
include __DIR__ . '/../includes/header.php';

// Obtener el archivo STL desde la URL o usar el predeterminado
$stlFile = $_GET['file'] ?? 'Geekko.stl';
$stlPath = '/My3DStore/public/stl/' . htmlspecialchars($stlFile);
?>

<div class="stl-viewer-page">
    <div class="viewer-header">
        <h1>Visor 3D</h1>
        <p>Visualiza modelos STL en tiempo real</p>
    </div>

    <div class="viewer-container">
        <div class="viewer-controls">
            <h3>Controles</h3>
            <div class="control-group">
                <label>Color del modelo:</label>
                <input type="color" id="modelColor" value="#00ff00">
            </div>
            <div class="control-group">
                <label>
                    <input type="checkbox" id="wireframe"> Modo alambre
                </label>
            </div>
            <div class="control-group">
                <button class="btn btn-secondary" id="resetView">Resetear Vista</button>
            </div>
            <div class="control-group">
                <p class="help-text">
                    <strong>Instrucciones:</strong><br>
                    • Arrastra para rotar<br>
                    • Rueda del mouse para zoom<br>
                    • Click derecho + arrastrar para mover
                </p>
            </div>
        </div>

        <div class="viewer-main">
            <div id="stl-viewer-container" style="width: 100%; height: 600px; border: 1px solid #ddd; background: #f0f0f0;"></div>
            <div class="viewer-info">
                <p><strong>Archivo:</strong> <?php echo htmlspecialchars($stlFile); ?></p>
            </div>
        </div>
    </div>

    <div class="available-models">
        <h3>Modelos disponibles</h3>
        <ul>
            <li><a href="/My3DStore/?action=stl-viewer&file=Geekko.stl">Geekko.stl</a></li>
        </ul>
    </div>
</div>

<script src="<?php echo htmlspecialchars(asset('js/stl-viewer.js')); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar visor
    // Aplicar rotación inicial para corregir orientación del modelo
    const viewer = new STLViewer('stl-viewer-container', {
        backgroundColor: 0xf0f0f0,
        modelColor: 0x00ff00,
        showGrid: true,
        showAxes: true,
        initialRotation: { 
            x: -Math.PI / 2, // Rotar 90 grados en X (hacia arriba)
            y: 0, 
            z: 0 
        }
    });

    // Cargar modelo STL
    const stlPath = '<?php echo $stlPath; ?>';
    viewer.loadSTL(stlPath, (progress) => {
        if (progress.success === false) {
            alert('Error al cargar el modelo STL: ' + (progress.error?.message || 'Error desconocido'));
        } else if (progress.success === true) {
            console.log('Modelo cargado exitosamente');
        }
    });

    // Controles
    const colorInput = document.getElementById('modelColor');
    const wireframeCheckbox = document.getElementById('wireframe');
    const resetButton = document.getElementById('resetView');

    if (colorInput) {
        colorInput.addEventListener('change', function() {
            const color = this.value;
            const hex = parseInt(color.replace('#', ''), 16);
            viewer.setColor(hex);
        });
    }

    if (wireframeCheckbox) {
        wireframeCheckbox.addEventListener('change', function() {
            viewer.setWireframe(this.checked);
        });
    }

    if (resetButton) {
        resetButton.addEventListener('click', function() {
            viewer.resetView();
        });
    }
});
</script>

<style>
.stl-viewer-page {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.viewer-header {
    text-align: center;
    margin-bottom: 2rem;
}

.viewer-header h1 {
    margin-bottom: 0.5rem;
}

.viewer-container {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.viewer-controls {
    background: #f9f9f9;
    padding: 1.5rem;
    border-radius: 8px;
    height: fit-content;
}

.viewer-controls h3 {
    margin-top: 0;
    margin-bottom: 1rem;
}

.control-group {
    margin-bottom: 1.5rem;
}

.control-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.control-group input[type="color"] {
    width: 100%;
    height: 40px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
}

.control-group input[type="checkbox"] {
    margin-right: 0.5rem;
}

.help-text {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.6;
    margin: 0;
}

.viewer-main {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.viewer-info {
    margin-top: 1rem;
    padding: 1rem;
    background: #f9f9f9;
    border-radius: 4px;
}

.available-models {
    background: #f9f9f9;
    padding: 1.5rem;
    border-radius: 8px;
}

.available-models h3 {
    margin-top: 0;
}

.available-models ul {
    list-style: none;
    padding: 0;
}

.available-models li {
    margin-bottom: 0.5rem;
}

.available-models a {
    color: #007bff;
    text-decoration: none;
}

.available-models a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .viewer-container {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

