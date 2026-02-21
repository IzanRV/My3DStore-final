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
                        <button type="button" id="custom-color-swatch" class="color-btn w-8 h-8 rounded-full border-2 border-transparent hover:scale-110 transition-transform hidden" data-color="" title="Color personalizado"></button>
                        <button type="button" id="open-custom-color-btn" class="w-8 h-8 rounded-full border-2 border-transparent bg-slate-200 dark:bg-slate-700 flex items-center justify-center hover:scale-110 transition-transform hover:border-primary/50" title="Elegir otro color">
                            <span class="material-icons-outlined text-slate-500 text-sm">add</span>
                        </button>
                    </div>
                </div>
                
                <!-- Modal elegir color -->
                <div id="custom-color-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/40" aria-hidden="true">
                    <div class="custom-color-popover bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-5 border border-slate-200 dark:border-slate-700 max-w-[280px]" role="dialog" aria-label="Elegir color">
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Elegir color</p>
                        <div class="flex flex-col gap-4">
                            <input type="color" id="custom-color-input" value="#003d7a" class="w-full h-12 rounded-xl border-2 border-slate-200 dark:border-slate-600 cursor-pointer bg-transparent">
                            <div class="flex gap-2 justify-end">
                                <button type="button" id="custom-color-cancel" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Cancelar</button>
                                <button type="button" id="custom-color-apply" class="px-4 py-2 rounded-xl bg-primary text-white hover:opacity-90">Aplicar</button>
                            </div>
                        </div>
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
                
                <!-- Añadir Imagen / Logo -->
                <div>
                    <div class="flex items-center gap-2 text-primary font-semibold text-sm uppercase tracking-wider mb-4">
                        <span class="material-icons-outlined text-sm">add_photo_alternate</span>
                        Añadir Imagen
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Añade un logo al modelo 3D en el lado que elijas.</p>
                    <label for="logo" class="border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl p-6 flex flex-col items-center justify-center gap-2 hover:border-primary/50 transition-colors cursor-pointer group">
                        <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/jpg,image/gif" class="hidden" />
                        <span class="material-icons-outlined text-slate-400 group-hover:text-primary">upload_file</span>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Subir imagen</p>
                        <p class="text-[9px] text-slate-400 uppercase">PNG, JPG Máx 10MB</p>
                    </label>
                    <div id="logo-options-wrap" class="hidden mt-3 space-y-3">
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">¿Dónde colocar el logo?</p>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" class="logo-side-btn px-3 py-2 rounded-lg border-2 border-primary bg-primary/10 text-xs font-medium hover:border-primary/50 transition-colors" data-side="front">Frente</button>
                            <button type="button" class="logo-side-btn px-3 py-2 rounded-lg border-2 border-slate-200 dark:border-slate-700 text-xs font-medium hover:border-primary/50 transition-colors" data-side="back">Atrás</button>
                            <button type="button" class="logo-side-btn px-3 py-2 rounded-lg border-2 border-slate-200 dark:border-slate-700 text-xs font-medium hover:border-primary/50 transition-colors" data-side="left">Izquierda</button>
                            <button type="button" class="logo-side-btn px-3 py-2 rounded-lg border-2 border-slate-200 dark:border-slate-700 text-xs font-medium hover:border-primary/50 transition-colors" data-side="right">Derecha</button>
                            <button type="button" class="logo-side-btn px-3 py-2 rounded-lg border-2 border-slate-200 dark:border-slate-700 text-xs font-medium hover:border-primary/50 transition-colors" data-side="top">Arriba</button>
                            <button type="button" class="logo-side-btn px-3 py-2 rounded-lg border-2 border-slate-200 dark:border-slate-700 text-xs font-medium hover:border-primary/50 transition-colors" data-side="bottom">Abajo</button>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" id="logo-apply-btn" class="flex-1 px-3 py-2 rounded-lg bg-primary text-white text-xs font-medium hover:opacity-90">Aplicar logo</button>
                            <button type="button" id="logo-remove-btn" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 text-xs font-medium hover:bg-slate-100 dark:hover:bg-slate-700">Quitar logo</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>
    
    <!-- Zona central extendida - Visor 3D -->
    <section class="flex-1 relative bg-[#e2e8f0] dark:bg-slate-900 overflow-hidden flex items-center justify-center p-8">
        <!-- Controles de zoom y rotación (z-40 para quedar por debajo del header z-50) -->
        <div class="absolute top-6 left-6 flex flex-col gap-2 z-40">
            <button id="zoom-in-btn" class="w-10 h-10 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center hover:text-primary transition-colors">
                <span class="material-icons-outlined">zoom_in</span>
            </button>
            <button id="zoom-out-btn" class="w-10 h-10 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center hover:text-primary transition-colors">
                <span class="material-icons-outlined">zoom_out</span>
            </button>
            <button id="reset-view-btn" class="w-10 h-10 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center hover:text-primary transition-colors">
                <span class="material-icons-outlined">360</span>
            </button>
        </div>
        
        <!-- Cartel inicial: diseña con la IA (se oculta cuando hay modelo generado) -->
        <div id="preview-welcome" class="absolute inset-0 flex items-center justify-center z-20 pointer-events-none">
            <div class="text-center px-8 py-12 rounded-3xl bg-white/95 dark:bg-slate-800/95 backdrop-blur shadow-xl border border-slate-200 dark:border-slate-700 pointer-events-auto max-w-md">
                <span class="material-icons-outlined text-6xl text-primary mb-4 block">auto_awesome</span>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-2">Diseña tu producto con la IA</h2>
                <p class="text-slate-600 dark:text-slate-400 text-sm">Abre el asistente y describe el objeto 3D que quieres crear. Cuando esté listo, aparecerá aquí.</p>
            </div>
        </div>
        
        <!-- Contenedor del modelo 3D -->
        <div class="relative group cursor-grab active:cursor-grabbing w-full h-full flex items-center justify-center z-0">
            <div class="absolute inset-0 bg-blue-400/10 blur-[100px] rounded-full"></div>
            <div id="preview-3d-container" class="preview-3d relative z-0 w-full h-full max-w-4xl">
                <div id="preview-loading" class="preview-loading absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-slate-900/50 rounded-xl hidden">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
                        <p class="text-slate-600 dark:text-slate-400">Cargando modelo 3D...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botón Publicar (visible solo cuando hay modelo) -->
        <div id="add-to-cart-wrap" class="absolute bottom-8 left-1/2 -translate-x-1/2 w-full max-w-md px-6 z-10 hidden">
            <button id="publish-btn" type="button" class="w-full bg-primary hover:bg-blue-700 text-white py-4 px-8 rounded-2xl shadow-xl shadow-primary/20 font-bold flex items-center justify-center gap-3 transition-all transform hover:-translate-y-1 active:scale-95">
                <span class="material-icons-outlined">publish</span>
                <span>Publicar</span>
            </button>
        </div>
    </section>
</main>

<!-- Modal Publicar producto -->
<div id="publish-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" id="publish-modal-backdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-700" role="dialog" aria-labelledby="publish-modal-title">
            <div class="p-6">
                <h2 id="publish-modal-title" class="text-xl font-bold mb-4">Publicar producto</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">El modelo actual se añadirá al catálogo con los datos que indiques. La vista previa del producto (a la izquierda) muestra el modelo con el color y dimensiones elegidos.</p>
                <form id="publish-form">
                    <div class="space-y-4">
                        <div>
                            <label for="publish-name" class="block text-sm font-medium mb-1">Nombre del producto</label>
                            <input type="text" id="publish-name" name="name" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" placeholder="Ej: Mi figura 3D">
                        </div>
                        <div>
                            <label for="publish-description" class="block text-sm font-medium mb-1">Descripción</label>
                            <textarea id="publish-description" name="description" rows="3" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" placeholder="Describe tu producto..."></textarea>
                        </div>
                        <div class="bg-slate-100 dark:bg-slate-700/50 rounded-xl p-4 space-y-2 text-sm">
                            <p class="font-medium">Vista previa del producto</p>
                            <p class="text-slate-600 dark:text-slate-400">Dimensiones: <span id="publish-preview-dims">—</span> mm</p>
                            <p class="text-slate-600 dark:text-slate-400">Material: <span id="publish-preview-material">—</span></p>
                            <p class="text-slate-600 dark:text-slate-400">Precio: <span id="publish-preview-price" class="font-bold text-primary">—</span></p>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" id="publish-modal-cancel" class="flex-1 py-2.5 px-4 rounded-xl border border-slate-300 dark:border-slate-600 font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancelar</button>
                        <button type="submit" id="publish-modal-submit" class="flex-1 py-2.5 px-4 rounded-xl bg-primary hover:bg-blue-700 text-white font-bold transition-colors">Aceptar</button>
                    </div>
                </form>
                <p id="publish-error" class="mt-3 text-sm text-red-500 hidden"></p>
            </div>
        </div>
    </div>
</div>

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
    const welcomeDiv = document.getElementById('preview-welcome');
    const addToCartWrap = document.getElementById('add-to-cart-wrap');

    function onModelReady() {
        if (welcomeDiv) welcomeDiv.style.display = 'none';
        if (addToCartWrap) addToCartWrap.classList.remove('hidden');
    }
    function onViewerCleared() {
        if (welcomeDiv) welcomeDiv.style.display = '';
        if (addToCartWrap) addToCartWrap.classList.add('hidden');
    }

    const hasJobInUrl = !!window.CUSTOMIZE_JOB_ID;
    if (hasJobInUrl && welcomeDiv) welcomeDiv.style.display = 'none';

    // Inicializar chatbot con visor; abrir por defecto si no hay modelo
    const chatbot = new AIChatbot('ai-chatbot', viewer, {
        onModelReady: onModelReady,
        onViewerCleared: onViewerCleared,
        openByDefault: !hasJobInUrl
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
                        if (loadingDiv) loadingDiv.style.display = 'none';
                        onModelReady();
                        applyDimensionsToViewer();
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
        // Sin job_id: no cargar modelo; se muestra el cartel "Diseña tu producto con la IA" y el chatbot abierto
        if (loadingDiv) loadingDiv.style.display = 'none';
    }

    function applyDimensionsToViewer() {
        if (!viewer || !viewer.setDimensions) return;
        const w = document.getElementById('width')?.value || 60;
        const h = document.getElementById('height')?.value || 85;
        const d = document.getElementById('depth')?.value || 45;
        viewer.setDimensions(parseInt(w, 10), parseInt(h, 10), parseInt(d, 10));
    }

    // Dimensiones: aplicar al modelo 3D al mover los sliders
    ['width', 'height', 'depth'].forEach(function(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function() {
            document.getElementById(id + '-value').textContent = this.value + 'mm';
            applyDimensionsToViewer();
        });
    });

    // Actualizar precio final
    function updatePrice() {
        const totalPrice = basePrice + materialPrice;
        const el = document.getElementById('final-price');
        if (el) el.textContent = totalPrice.toFixed(2).replace('.', ',') + '€';
    }

    // Selectores de color
    const colorButtons = document.querySelectorAll('.color-btn');
    function setActiveColorButton(btn) {
        document.querySelectorAll('.color-btn').forEach(b => {
            b.classList.remove('active', 'border-primary', 'ring-2', 'ring-primary/20');
            b.classList.add('border-transparent');
        });
        btn.classList.add('active', 'border-primary', 'ring-2', 'ring-primary/20');
        btn.classList.remove('border-transparent');
        const color = btn.dataset.color;
        if (color) {
            const hex = color.startsWith('#') ? color : '#' + color;
            const num = parseInt(hex.slice(1), 16);
            if (!isNaN(num)) viewer.setColor(num);
            else if (colorMap[color]) viewer.setColor(colorMap[color]);
        }
    }
    colorButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            setActiveColorButton(this);
        });
    });

    // Modal elegir cualquier color
    const customColorModal = document.getElementById('custom-color-modal');
    const customColorInput = document.getElementById('custom-color-input');
    const customColorSwatch = document.getElementById('custom-color-swatch');
    document.getElementById('open-custom-color-btn')?.addEventListener('click', function() {
        const current = document.querySelector('.color-btn.active')?.dataset.color;
        customColorInput.value = (current && /^#[0-9A-Fa-f]{6}$/.test(current)) ? current : '#003d7a';
        customColorModal.classList.remove('hidden');
        customColorModal.classList.add('flex');
        customColorModal.setAttribute('aria-hidden', 'false');
    });
    function closeCustomColorModal() {
        customColorModal.classList.add('hidden');
        customColorModal.classList.remove('flex');
        customColorModal.setAttribute('aria-hidden', 'true');
    }
    document.getElementById('custom-color-cancel')?.addEventListener('click', closeCustomColorModal);
    customColorModal?.addEventListener('click', function(e) {
        if (e.target === customColorModal) closeCustomColorModal();
    });
    document.getElementById('custom-color-apply')?.addEventListener('click', function() {
        const hex = customColorInput.value;
        if (!hex) return;
        customColorSwatch.style.backgroundColor = hex;
        customColorSwatch.dataset.color = hex;
        customColorSwatch.classList.remove('hidden');
        setActiveColorButton(customColorSwatch);
        closeCustomColorModal();
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

    // Logo en modelo 3D (currentLogoFile se envía al publicar)
    let currentLogoUrl = null;
    let currentLogoFile = null;
    let selectedLogoSide = 'front';
    const logoOptionsWrap = document.getElementById('logo-options-wrap');
    const logoInput = document.getElementById('logo');
    document.querySelectorAll('.logo-side-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.logo-side-btn').forEach(b => {
                b.classList.remove('border-primary', 'bg-primary/10');
                b.classList.add('border-slate-200', 'dark:border-slate-700');
            });
            this.classList.add('border-primary', 'bg-primary/10');
            this.classList.remove('border-slate-200', 'dark:border-slate-700');
            selectedLogoSide = this.dataset.side || 'front';
        });
    });
    if (logoInput) {
        logoInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file) {
                logoOptionsWrap.classList.add('hidden');
                if (currentLogoUrl) URL.revokeObjectURL(currentLogoUrl);
                currentLogoUrl = null;
                currentLogoFile = null;
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('El archivo no puede superar 10MB.');
                this.value = '';
                return;
            }
            if (currentLogoUrl) URL.revokeObjectURL(currentLogoUrl);
            currentLogoUrl = URL.createObjectURL(file);
            currentLogoFile = file;
            logoOptionsWrap.classList.remove('hidden');
        });
    }
    document.getElementById('logo-apply-btn')?.addEventListener('click', function() {
        if (!currentLogoUrl || !viewer) return;
        if (viewer.logoMesh) {
            if (viewer.setLogoSide) viewer.setLogoSide(selectedLogoSide);
        } else {
            if (viewer.addLogo) viewer.addLogo(currentLogoUrl, selectedLogoSide);
        }
    });
    document.getElementById('logo-remove-btn')?.addEventListener('click', function() {
        if (viewer && viewer.removeLogo) viewer.removeLogo();
        currentLogoFile = null;
        if (currentLogoUrl) { URL.revokeObjectURL(currentLogoUrl); currentLogoUrl = null; }
        if (logoInput) logoInput.value = '';
        logoOptionsWrap.classList.add('hidden');
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

    // Botón Publicar: abrir modal
    const publishModal = document.getElementById('publish-modal');
    const publishBtn = document.getElementById('publish-btn');
    const publishForm = document.getElementById('publish-form');
    const publishError = document.getElementById('publish-error');
    let publishAutoRotateId = null;

    function openPublishModal() {
        <?php if (!isLoggedIn()): ?>
            window.location.href = (window.BASE_PATH || '/') + '?action=login';
            return;
        <?php endif; ?>
        if (!window.CUSTOMIZE_JOB_ID) {
            if (publishError) {
                publishError.textContent = 'No hay un modelo generado para publicar. Genera uno primero con el asistente.';
                publishError.classList.remove('hidden');
            }
            return;
        }
        const w = document.getElementById('width')?.value || 100;
        const h = document.getElementById('height')?.value || 100;
        const d = document.getElementById('depth')?.value || 100;
        const material = document.querySelector('.material-btn.active')?.dataset.material || 'PLA';
        const totalPrice = basePrice + materialPrice;
        document.getElementById('publish-preview-dims').textContent = w + ' × ' + h + ' × ' + d;
        document.getElementById('publish-preview-material').textContent = material;
        document.getElementById('publish-preview-price').textContent = totalPrice.toFixed(2).replace('.', ',') + ' €';
        if (publishError) publishError.classList.add('hidden');
        publishModal.classList.remove('hidden');
        publishModal.setAttribute('aria-hidden', 'false');
        if (viewer && viewer.model) {
            function rotateStep() {
                if (viewer && viewer.model) viewer.model.rotation.y += 0.01;
                publishAutoRotateId = requestAnimationFrame(rotateStep);
            }
            rotateStep();
        }
    }

    function closePublishModal() {
        publishModal.classList.add('hidden');
        publishModal.setAttribute('aria-hidden', 'true');
        if (publishAutoRotateId) {
            cancelAnimationFrame(publishAutoRotateId);
            publishAutoRotateId = null;
        }
    }

    publishBtn?.addEventListener('click', openPublishModal);
    document.getElementById('publish-modal-cancel')?.addEventListener('click', closePublishModal);
    document.getElementById('publish-modal-backdrop')?.addEventListener('click', closePublishModal);

    publishForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('publish-name').value.trim();
        const description = document.getElementById('publish-description').value.trim();
        if (!name) {
            if (publishError) { publishError.textContent = 'Indica un nombre para el producto.'; publishError.classList.remove('hidden'); }
            return;
        }
        const jobId = window.CUSTOMIZE_JOB_ID;
        const price = basePrice + materialPrice;
        const material = document.querySelector('.material-btn.active')?.dataset.material || 'PLA';
        const dimX = document.getElementById('width')?.value || 100;
        const dimY = document.getElementById('height')?.value || 100;
        const dimZ = document.getElementById('depth')?.value || 100;
        const colorEl = document.querySelector('.color-btn.active');
        const color = (colorEl && colorEl.dataset.color) ? (colorEl.dataset.color.startsWith('#') ? colorEl.dataset.color : '#' + colorEl.dataset.color) : '';
        const formData = new FormData();
        formData.append('action', 'saveAsProduct');
        formData.append('job_id', jobId);
        formData.append('name', name);
        formData.append('description', description || name);
        formData.append('price', price.toFixed(2));
        formData.append('material', material);
        formData.append('dim_x', dimX);
        formData.append('dim_y', dimY);
        formData.append('dim_z', dimZ);
        if (color) formData.append('color', color);
        formData.append('logo_side', selectedLogoSide);
        if (currentLogoFile) formData.append('logo', currentLogoFile);
        const submitBtn = document.getElementById('publish-modal-submit');
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Publicando...'; }
        if (publishError) publishError.classList.add('hidden');
        fetch((window.BASE_PATH || '/') + 'api/ai3d.php?action=saveAsProduct', {
            method: 'POST',
            body: formData
        }).then(function(r) {
            return r.text().then(function(text) {
                try {
                    return { ok: r.ok, data: text ? JSON.parse(text) : {} };
                } catch (e) {
                    return { ok: false, data: { error: r.status >= 400 ? ('Error del servidor (' + r.status + ')') : 'Respuesta no válida' } };
                }
            });
        }).then(function(result) {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Aceptar'; }
            var data = result.data;
            if (result.ok && data.success && data.product_url) {
                closePublishModal();
                window.location.href = data.product_url;
            } else {
                if (publishError) {
                    publishError.textContent = data.error || 'Error al publicar el producto.';
                    publishError.classList.remove('hidden');
                }
            }
        }).catch(function(err) {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Aceptar'; }
            if (publishError) {
                publishError.textContent = 'Error de conexión. Comprueba la URL del sitio y que el servidor esté activo.';
                publishError.classList.remove('hidden');
            }
        });
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
