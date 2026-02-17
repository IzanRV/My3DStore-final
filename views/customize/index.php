<?php
$pageTitle = 'My3DStore - Personalizador 3D';
$useTailwindBody = true; // Activar clases Tailwind para esta página
$loadSTLViewer = true; // Activar carga de Three.js
$customizeJobId = isset($_GET['job_id']) ? preg_replace('/[^a-zA-Z0-9\-_]/', '', trim($_GET['job_id'])) : '';
$customizePrompt = isset($_GET['prompt']) ? trim($_GET['prompt']) : '';
if (mb_strlen($customizePrompt) > 500) {
    $customizePrompt = mb_substr($customizePrompt, 0, 500);
}
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo htmlspecialchars(asset('css/ai-chatbot.css')); ?>">
<style>
    body { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>

<main class="flex-1 flex flex-col md:flex-row overflow-hidden max-w-[1920px] mx-auto w-full">
    <!-- Sidebar izquierdo - Controles de personalización -->
    <aside class="w-full md:w-80 lg:w-96 bg-card-light dark:bg-card-dark border-r border-slate-200 dark:border-slate-800 flex flex-col overflow-y-auto custom-scrollbar">
        <div class="p-6">
            <h1 class="text-xl font-bold mb-1">Personalizador 3D</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-8">Diseña tu producto a medida</p>
            
            <div class="space-y-6 mb-8">
                <!-- Dimensiones -->
                <div>
                    <div class="flex items-center gap-2 text-primary font-semibold text-sm uppercase tracking-wider mb-4">
                        <span class="material-icons-outlined text-sm">straighten</span>
                        Dimensiones
                    </div>
                    <div class="space-y-4 px-2">
                        <div>
                            <div class="flex justify-between text-xs mb-2">
                                <label class="text-slate-600 dark:text-slate-400">Ancho (X)</label>
                                <span class="font-mono text-primary font-bold" id="width-value">60mm</span>
                            </div>
                            <input 
                                type="range" 
                                id="width" 
                                name="width" 
                                min="10" 
                                max="200" 
                                value="60"
                                class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-primary"
                                oninput="document.getElementById('width-value').textContent = this.value + 'mm'"
                            />
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-2">
                                <label class="text-slate-600 dark:text-slate-400">Alto (Y)</label>
                                <span class="font-mono text-primary font-bold" id="height-value">85mm</span>
                            </div>
                            <input 
                                type="range" 
                                id="height" 
                                name="height" 
                                min="10" 
                                max="200" 
                                value="85"
                                class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-primary"
                                oninput="document.getElementById('height-value').textContent = this.value + 'mm'"
                            />
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-2">
                                <label class="text-slate-600 dark:text-slate-400">Profundidad (Z)</label>
                                <span class="font-mono text-primary font-bold" id="depth-value">45mm</span>
                            </div>
                            <input 
                                type="range" 
                                id="depth" 
                                name="depth" 
                                min="10" 
                                max="200" 
                                value="45"
                                class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-primary"
                                oninput="document.getElementById('depth-value').textContent = this.value + 'mm'"
                            />
                        </div>
                    </div>
                </div>
                
                <!-- Color -->
                <div>
                    <div class="flex items-center gap-2 text-primary font-semibold text-sm uppercase tracking-wider mb-4">
                        <span class="material-icons-outlined text-sm">palette</span>
                        Color
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button class="w-8 h-8 rounded-full border-2 border-primary ring-2 ring-primary/20 bg-[#003d7a] hover:scale-110 transition-transform color-btn active" data-color="#003d7a"></button>
                        <button class="w-8 h-8 rounded-full border-2 border-transparent bg-red-500 hover:scale-110 transition-transform color-btn" data-color="#ef4444"></button>
                        <button class="w-8 h-8 rounded-full border-2 border-transparent bg-orange-500 hover:scale-110 transition-transform color-btn" data-color="#f97316"></button>
                        <button class="w-8 h-8 rounded-full border-2 border-transparent bg-emerald-500 hover:scale-110 transition-transform color-btn" data-color="#10b981"></button>
                        <button class="w-8 h-8 rounded-full border-2 border-transparent bg-blue-600 hover:scale-110 transition-transform color-btn" data-color="#2563eb"></button>
                        <button class="w-8 h-8 rounded-full border-2 border-transparent bg-purple-500 hover:scale-110 transition-transform color-btn" data-color="#a855f7"></button>
                        <button class="w-8 h-8 rounded-full border-2 border-transparent bg-pink-500 hover:scale-110 transition-transform color-btn" data-color="#ec4899"></button>
                        <button class="w-8 h-8 rounded-full border-2 border-transparent bg-slate-200 dark:bg-slate-700 flex items-center justify-center hover:scale-110 transition-transform">
                            <span class="material-icons-outlined text-slate-500 text-sm">add</span>
                        </button>
                    </div>
                </div>
                
                <!-- Material -->
                <div>
                    <div class="flex items-center gap-2 text-primary font-semibold text-sm uppercase tracking-wider mb-4">
                        <span class="material-icons-outlined text-sm">layers</span>
                        Material
                    </div>
                    <div class="grid gap-2">
                        <button class="material-btn flex items-center justify-between p-3 rounded-xl border-2 border-primary bg-primary/5 text-left transition-all active" data-material="PLA" data-price="0">
                            <div>
                                <p class="font-bold text-sm">Plástico PLA</p>
                                <p class="text-[10px] text-slate-500">Biodegradable, fácil de imprimir</p>
                            </div>
                            <span class="text-xs font-bold text-primary">+0€</span>
                        </button>
                        <button class="material-btn flex items-center justify-between p-3 rounded-xl border-2 border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700 bg-white dark:bg-slate-900/50 text-left transition-all group" data-material="Madera" data-price="5">
                            <div>
                                <p class="font-bold text-sm group-hover:text-primary transition-colors">Madera</p>
                                <p class="text-[10px] text-slate-500">Resistente y duradero</p>
                            </div>
                            <span class="text-xs font-bold text-slate-500">+5€</span>
                        </button>
                        <button class="material-btn flex items-center justify-between p-3 rounded-xl border-2 border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700 bg-white dark:bg-slate-900/50 text-left transition-all group" data-material="Metal" data-price="15">
                            <div>
                                <p class="font-bold text-sm group-hover:text-primary transition-colors">Metal</p>
                                <p class="text-[10px] text-slate-500">Resistente al calor, alta resolución</p>
                            </div>
                            <span class="text-xs font-bold text-slate-500">+15€</span>
                        </button>
                        <button class="material-btn flex items-center justify-between p-3 rounded-xl border-2 border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700 bg-white dark:bg-slate-900/50 text-left transition-all group" data-material="Ceramica" data-price="10">
                            <div>
                                <p class="font-bold text-sm group-hover:text-primary transition-colors">Cerámica</p>
                                <p class="text-[10px] text-slate-500">Resistente al calor</p>
                            </div>
                            <span class="text-xs font-bold text-slate-500">+10€</span>
                        </button>
                    </div>
                </div>
                
                <!-- Añadir Imagen -->
                <div>
                    <div class="flex items-center gap-2 text-primary font-semibold text-sm uppercase tracking-wider mb-4">
                        <span class="material-icons-outlined text-sm">add_photo_alternate</span>
                        Añadir Imagen
                    </div>
                    <label for="logo" class="border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl p-6 flex flex-col items-center justify-center gap-2 hover:border-primary/50 transition-colors cursor-pointer group">
                        <input type="file" id="logo" name="logo" accept="image/*" class="hidden" />
                        <span class="material-icons-outlined text-slate-400 group-hover:text-primary">upload_file</span>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Subir imagen</p>
                        <p class="text-[9px] text-slate-400 uppercase">PNG, JPG Máx 10MB</p>
                    </label>
                </div>
            </div>
        </div>
    </aside>
    
    <!-- Zona central extendida - Visor 3D -->
    <section class="flex-1 relative bg-[#e2e8f0] dark:bg-slate-900 overflow-hidden flex items-center justify-center p-8">
        <!-- Controles de zoom y rotación -->
        <div class="absolute top-6 left-6 flex flex-col gap-2 z-50">
            <button id="zoom-in-btn" class="w-10 h-10 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center hover:text-primary transition-colors z-50">
                <span class="material-icons-outlined">zoom_in</span>
            </button>
            <button id="zoom-out-btn" class="w-10 h-10 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center hover:text-primary transition-colors z-50">
                <span class="material-icons-outlined">zoom_out</span>
            </button>
            <button id="reset-view-btn" class="w-10 h-10 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center hover:text-primary transition-colors z-50">
                <span class="material-icons-outlined">360</span>
            </button>
        </div>
        
        <!-- Contenedor del modelo 3D -->
        <div class="relative group cursor-grab active:cursor-grabbing w-full h-full flex items-center justify-center z-0">
            <div class="absolute inset-0 bg-blue-400/10 blur-[100px] rounded-full"></div>
            <div id="preview-3d-container" class="preview-3d relative z-0 w-full h-full max-w-4xl">
                <div id="preview-loading" class="preview-loading absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-slate-900/50 rounded-xl">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
                        <p class="text-slate-600 dark:text-slate-400">Cargando modelo 3D...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botón Descargar modelo (habilitado cuando hay modelo generado por IA) -->
        <div class="absolute bottom-24 left-1/2 -translate-x-1/2 z-10">
            <a id="download-model-btn" href="#" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-600 text-white font-medium opacity-60 pointer-events-none cursor-not-allowed transition-all" title="Genera un modelo para poder descargarlo">
                <span class="material-icons-outlined text-sm">download</span>
                Descargar modelo
            </a>
        </div>
        <!-- Botón de añadir al carrito -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 w-full max-w-md px-6 z-10">
            <button id="add-to-cart-btn" class="w-full bg-primary hover:bg-blue-700 text-white py-4 px-8 rounded-2xl shadow-xl shadow-primary/20 font-bold flex items-center justify-center gap-3 transition-all transform hover:-translate-y-1 active:scale-95">
                <span class="material-icons-outlined">shopping_cart</span>
                <span>Añadir al Carrito — <span id="final-price">15,00€</span></span>
            </button>
        </div>
    </section>
</main>
<div id="ai-chatbot" aria-hidden="true"></div>

<script>
    window.CUSTOMIZE_JOB_ID = <?php echo json_encode($customizeJobId); ?>;
    window.CUSTOMIZE_PROMPT = <?php echo json_encode($customizePrompt, JSON_UNESCAPED_UNICODE); ?>;
    window.BASE_PATH = '<?php echo addslashes(getBasePath()); ?>';
</script>
<script src="<?php echo htmlspecialchars(asset('js/stl-viewer.js')); ?>"></script>
<script src="<?php echo htmlspecialchars(asset('js/ai-chatbot.js')); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Precio base
    let basePrice = 15.00;
    let materialPrice = 0;
    
    // Mapeo de colores
    const colorMap = {
        '#003d7a': 0x003d7a,
        '#ef4444': 0xef4444,
        '#f97316': 0xf97316,
        '#10b981': 0x10b981,
        '#2563eb': 0x2563eb,
        '#a855f7': 0xa855f7,
        '#ec4899': 0xec4899
    };

    // Inicializar visor 3D
    const viewer = new STLViewer('preview-3d-container', {
        backgroundColor: 0xe2e8f0,
        modelColor: 0x003d7a,
        showGrid: false,
        showAxes: false,
        initialRotation: { 
            x: 0, 
            y: Math.PI / 2,
            z: 0 
        }
    });

    const loadingDiv = document.getElementById('preview-loading');
    const downloadBtn = document.getElementById('download-model-btn');

    // Botón Descargar modelo: habilitado cuando hay modelo generado por IA
    let currentDownloadJobId = null;
    function updateDownloadButton(jobId) {
        currentDownloadJobId = jobId || null;
        if (downloadBtn) {
            if (currentDownloadJobId) {
                downloadBtn.href = (window.BASE_PATH || '/') + 'api/ai3d.php?action=downloadModel&job_id=' + encodeURIComponent(currentDownloadJobId);
                downloadBtn.classList.remove('opacity-60', 'pointer-events-none', 'cursor-not-allowed');
                downloadBtn.classList.add('hover:bg-slate-500', 'cursor-pointer');
                downloadBtn.title = 'Descargar el último modelo generado';
            } else {
                downloadBtn.href = '#';
                downloadBtn.classList.add('opacity-60', 'pointer-events-none', 'cursor-not-allowed');
                downloadBtn.classList.remove('hover:bg-slate-500', 'cursor-pointer');
                downloadBtn.title = 'Genera un modelo para poder descargarlo';
            }
        }
    }
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function(e) {
            if (!currentDownloadJobId) e.preventDefault();
        });
    }

    // Inicializar chatbot con visor y callbacks para descarga
    const chatbot = new AIChatbot('ai-chatbot', viewer, {
        onModelReady: function(jobId) { updateDownloadButton(jobId); },
        onViewerCleared: function() { updateDownloadButton(null); }
    });

    // Si hay job_id en la URL: no cargar GLB; hacer polling y cargar modelo en visor
    if (window.CUSTOMIZE_JOB_ID) {
        const jobId = window.CUSTOMIZE_JOB_ID;
        const promptForCatalog = window.CUSTOMIZE_PROMPT || '';
        if (loadingDiv) {
            loadingDiv.innerHTML = '<div class="text-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div><p class="text-slate-600 dark:text-slate-400">Generando modelo...</p></div>';
            loadingDiv.style.display = '';
        }
        const AI_API = (window.BASE_PATH || '/') + 'api/ai3d.php';
        const maxAttempts = 150;
        let attempts = 0;
        (async function pollAndLoad() {
            while (attempts < maxAttempts) {
                try {
                    const response = await fetch(AI_API + '?action=getJobStatus&job_id=' + encodeURIComponent(jobId));
                    if (!response.ok) {
                        if (loadingDiv && attempts === 0) loadingDiv.innerHTML = '<p class="text-slate-600 dark:text-slate-400">Error al conectar. Reintentando...</p>';
                        attempts++;
                        await new Promise(function(r) { setTimeout(r, 2000); });
                        continue;
                    }
                    var data;
                    try { data = await response.json(); } catch (e) {
                        attempts++;
                        await new Promise(function(r) { setTimeout(r, 2000); });
                        continue;
                    }
                    if (data.status === 'completed') {
                        if (loadingDiv) loadingDiv.innerHTML = '<div class="text-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div><p class="text-slate-600 dark:text-slate-400">Cargando modelo en el visor...</p></div>';
                        await chatbot.loadJobInViewer(jobId, promptForCatalog);
                        updateDownloadButton(jobId);
                        if (loadingDiv) loadingDiv.style.display = 'none';
                        return;
                    }
                    if (data.status === 'failed') {
                        if (loadingDiv) {
                            loadingDiv.innerHTML = '<p class="text-red-500">Error en la generación. ' + (typeof data.error === 'string' ? data.error : '') + '</p>';
                            loadingDiv.style.display = '';
                        }
                        return;
                    }
                } catch (err) {
                    if (loadingDiv && attempts === 0) loadingDiv.innerHTML = '<p class="text-slate-600 dark:text-slate-400">Error al conectar. Reintentando...</p>';
                }
                attempts++;
                await new Promise(function(r) { setTimeout(r, 2000); });
            }
            if (loadingDiv) loadingDiv.innerHTML = '<p class="text-slate-600 dark:text-slate-400">Tiempo de espera agotado. Prueba de nuevo más tarde.</p>';
        })();
    } else {
        // Cargar modelo GLB por defecto cuando no hay job_id
        const glbPath = '<?php echo addslashes(asset('glb/pato.glb')); ?>';
        viewer.loadGLB(glbPath, (progress) => {
            if (progress.success === false) {
                if (loadingDiv) loadingDiv.innerHTML = '<p class="text-red-500">Error al cargar el modelo 3D</p>';
                console.error('Error loading GLB:', progress.error);
            } else if (progress.success === true) {
                if (loadingDiv) loadingDiv.style.display = 'none';
                console.log('Modelo GLB cargado exitosamente');
            }
        });
    }

    // Actualizar precio final
    function updatePrice() {
        const totalPrice = basePrice + materialPrice;
        document.getElementById('final-price').textContent = totalPrice.toFixed(2).replace('.', ',') + '€';
    }

    // Selectores de color
    const colorButtons = document.querySelectorAll('.color-btn');
    colorButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            colorButtons.forEach(b => {
                b.classList.remove('active', 'border-primary', 'ring-2', 'ring-primary/20');
                b.classList.add('border-transparent');
            });
            this.classList.add('active', 'border-primary', 'ring-2', 'ring-primary/20');
            this.classList.remove('border-transparent');
            
            const color = this.dataset.color;
            if (color && colorMap[color]) {
                viewer.setColor(colorMap[color]);
            }
        });
    });

    // Selectores de material
    const materialButtons = document.querySelectorAll('.material-btn');
    materialButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            materialButtons.forEach(b => {
                b.classList.remove('active', 'border-primary', 'bg-primary/5');
                b.classList.add('border-slate-100', 'dark:border-slate-800', 'bg-white', 'dark:bg-slate-900/50');
            });
            this.classList.add('active', 'border-primary', 'bg-primary/5');
            this.classList.remove('border-slate-100', 'dark:border-slate-800', 'bg-white', 'dark:bg-slate-900/50');
            
            materialPrice = parseFloat(this.dataset.price) || 0;
            updatePrice();
        });
    });

    // Controles de zoom y vista
    document.getElementById('zoom-in-btn')?.addEventListener('click', () => {
        if (viewer && viewer.zoomIn) viewer.zoomIn();
    });
    
    document.getElementById('zoom-out-btn')?.addEventListener('click', () => {
        if (viewer && viewer.zoomOut) viewer.zoomOut();
    });
    
    document.getElementById('reset-view-btn')?.addEventListener('click', () => {
        if (viewer && viewer.resetView) viewer.resetView();
    });

    // Botón añadir al carrito
    document.getElementById('add-to-cart-btn')?.addEventListener('click', function() {
        <?php if (!isLoggedIn()): ?>
            window.location.href = (window.BASE_PATH || '/') + '?action=login';
        <?php else: ?>
            // Aquí iría la lógica para añadir al carrito
            const width = document.getElementById('width').value;
            const height = document.getElementById('height').value;
            const depth = document.getElementById('depth').value;
            const selectedColor = document.querySelector('.color-btn.active')?.dataset.color || '#003d7a';
            const selectedMaterial = document.querySelector('.material-btn.active')?.dataset.material || 'PLA';
            const finalPrice = basePrice + materialPrice;
            
            // Crear formulario y enviar
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = (window.BASE_PATH || '/') + '?action=cart-add';
            
            const productId = document.createElement('input');
            productId.type = 'hidden';
            productId.name = 'product_id';
            productId.value = '<?php echo $_GET['product_id'] ?? 0; ?>';
            form.appendChild(productId);
            
            const quantity = document.createElement('input');
            quantity.type = 'hidden';
            quantity.name = 'quantity';
            quantity.value = '1';
            form.appendChild(quantity);
            
            document.body.appendChild(form);
            form.submit();
        <?php endif; ?>
    });

    // Manejar redimensionamiento
    window.addEventListener('resize', function() {
        if (viewer && viewer.onWindowResize) {
            viewer.onWindowResize();
        }
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
