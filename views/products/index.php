<?php
$pageTitle = 'My3DStore - Catálogo de Productos';
$useTailwindBody = true; // Activar clases Tailwind para esta página
$loadStatic3D = true; // Visor 3D solo en los primeros 6 productos (límite WebGL)
include __DIR__ . '/../../includes/header.php';

// Obtener valores de filtros actuales
$currentPrice = $_GET['price'] ?? '';
$currentMaterial = $_GET['material'] ?? '';
$currentCategory = $_GET['category'] ?? '';
$currentSearch = $_GET['search'] ?? '';
?>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569;
    }
</style>

<main class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row gap-8">
    <aside class="w-full md:w-64 space-y-8">
        <form method="GET" action="/My3DStore/" id="filterForm">
            <input type="hidden" name="action" value="products">
            <?php if ($currentSearch): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($currentSearch); ?>">
            <?php endif; ?>
            
            <!-- Filtro de Precio -->
            <div>
                <h3 class="text-lg font-bold mb-4">Precio</h3>
                <div class="space-y-4">
                    <div class="flex justify-between text-sm">
                        <span>0 €</span>
                        <span>500 €</span>
                    </div>
                    <input 
                        type="range" 
                        min="0" 
                        max="500" 
                        value="<?php echo $currentPrice ? explode('-', $currentPrice)[0] ?? 0 : 0; ?>"
                        class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-primary"
                        id="priceRange"
                    />
                    <div class="space-y-2 mt-4 text-sm">
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                            <input 
                                class="text-primary focus:ring-primary border-slate-300" 
                                name="price" 
                                type="radio" 
                                value="0-5"
                                <?php echo $currentPrice === '0-5' ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();"
                            />
                            <span>De 0 a 5 €</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                            <input 
                                class="text-primary focus:ring-primary border-slate-300" 
                                name="price" 
                                type="radio" 
                                value="5-15"
                                <?php echo $currentPrice === '5-15' ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();"
                            />
                            <span>De 5 a 15 €</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                            <input 
                                class="text-primary focus:ring-primary border-slate-300" 
                                name="price" 
                                type="radio" 
                                value="15-20"
                                <?php echo $currentPrice === '15-20' ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();"
                            />
                            <span>De 15 a 20 €</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                            <input 
                                class="text-primary focus:ring-primary border-slate-300" 
                                name="price" 
                                type="radio" 
                                value="20+"
                                <?php echo $currentPrice === '20+' ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();"
                            />
                            <span>Más de 20 €</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                            <input 
                                class="text-primary focus:ring-primary border-slate-300" 
                                name="price" 
                                type="radio" 
                                value=""
                                <?php echo !$currentPrice ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();"
                            />
                            <span>Todos</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Filtro de Materiales -->
            <div>
                <h3 class="text-lg font-bold mb-4">Materiales</h3>
                <div class="space-y-2 text-sm">
                    <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                        <input 
                            class="rounded text-primary focus:ring-primary border-slate-300" 
                            type="checkbox" 
                            name="material" 
                            value="PLA"
                            <?php echo $currentMaterial === 'PLA' ? 'checked' : ''; ?>
                            onchange="updateMaterialFilter(this);"
                        />
                        <span>Plástico</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                        <input 
                            class="rounded text-primary focus:ring-primary border-slate-300" 
                            type="checkbox" 
                            name="material" 
                            value="Metal"
                            <?php echo $currentMaterial === 'Metal' ? 'checked' : ''; ?>
                            onchange="updateMaterialFilter(this);"
                        />
                        <span>Metal</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                        <input 
                            class="rounded text-primary focus:ring-primary border-slate-300" 
                            type="checkbox" 
                            name="material" 
                            value="Madera"
                            <?php echo $currentMaterial === 'Madera' ? 'checked' : ''; ?>
                            onchange="updateMaterialFilter(this);"
                        />
                        <span>Madera</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                        <input 
                            class="rounded text-primary focus:ring-primary border-slate-300" 
                            type="checkbox" 
                            name="material" 
                            value="Ceramica"
                            <?php echo $currentMaterial === 'Ceramica' ? 'checked' : ''; ?>
                            onchange="updateMaterialFilter(this);"
                        />
                        <span>Cerámica</span>
                    </label>
                </div>
            </div>
        </form>
    </aside>
    
    <section class="flex-1">
        <?php if (empty($products)): ?>
            <div class="text-center py-12">
                <p class="text-slate-500 text-lg">No se encontraron productos.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                foreach ($products as $product): 
                    if (!empty($product['image_url'])) {
                        $iu = trim($product['image_url']);
                        if (strpos($iu, 'http') === 0) {
                            $productImage = htmlspecialchars($iu);
                        } else {
                            $rel = $iu;
                            if (strpos($rel, '/') === 0) $rel = ltrim($rel, '/');
                            if (preg_match('#^My3DStore/public/(.*)#', $rel, $m)) $rel = $m[1];
                            $productImage = htmlspecialchars(asset($rel));
                        }
                    } else {
                        $productImage = 'https://via.placeholder.com/400x400?text=3D+Product';
                    }
                    $productPrice = formatPrice($product['price']);
                    $hasModel = !empty($product['stl_url']) || (!empty($product['dimensions']) && (strpos($product['dimensions'], '.stl') !== false || strpos($product['dimensions'], '.glb') !== false));
                    $modelPath = $hasModel ? productModelAsset($product) : '';
                    $fallbackPath = asset('glb/pato.glb');
                ?>
                    <div class="bg-card-light dark:bg-card-dark rounded-xl overflow-hidden shadow-md group transition-all hover:shadow-xl hover:-translate-y-1 product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>">
                        <div class="relative overflow-hidden aspect-square bg-[#003d7e]">
                            <a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0">
                                <div class="card-image relative w-full h-full <?php echo $hasModel ? '' : 'flex items-center justify-center'; ?>" style="<?php echo $hasModel ? '' : 'min-height:200px'; ?>">
                                    <?php if ($productImage): ?>
                                    <img src="<?php echo $productImage; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
                                    <span class="material-icons-outlined text-white/90 text-5xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
                                    <?php else: ?>
                                    <span class="material-icons-outlined text-white/90 text-5xl">view_in_ar</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="card-3d absolute inset-0 w-full h-full min-h-0 hidden z-10 pointer-events-auto bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden">
                                <?php if ($hasModel): ?>
                                <div class="static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($modelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($fallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($hasModel): ?>
                            <button type="button" class="card-toggle-3d absolute top-2 right-2 z-20 px-2 py-1 rounded-lg bg-white/90 dark:bg-slate-800/90 text-xs font-medium shadow hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" title="Ver modelo 3D">Ver en 3D</button>
                            <?php endif; ?>
                            <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2 px-2 z-10">
                                <?php if ($product['stock'] > 0 && isLoggedIn()): ?>
                                    <form method="POST" action="/My3DStore/?action=cart-add" class="flex-1">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button 
                                            type="submit" 
                                            class="bg-primary hover:bg-blue-600 text-white text-[10px] uppercase font-bold py-2 px-3 rounded shadow-lg transition-colors w-full"
                                        >
                                            Añadir a la cesta
                                        </button>
                                    </form>
                                    <a 
                                        href="/My3DStore/?action=customize&product_id=<?php echo $product['id']; ?>"
                                        class="bg-footer-dark hover:bg-blue-900 text-white text-[10px] uppercase font-bold py-2 px-3 rounded shadow-lg transition-colors flex-1 text-center"
                                    >
                                        Personalizar
                                    </a>
                                <?php elseif ($product['stock'] > 0): ?>
                                    <a 
                                        href="/My3DStore/?action=login"
                                        class="bg-primary hover:bg-blue-600 text-white text-[10px] uppercase font-bold py-2 px-3 rounded shadow-lg transition-colors flex-1 text-center"
                                    >
                                        Añadir a la cesta
                                    </a>
                                    <a 
                                        href="/My3DStore/?action=login"
                                        class="bg-footer-dark hover:bg-blue-900 text-white text-[10px] uppercase font-bold py-2 px-3 rounded shadow-lg transition-colors flex-1 text-center"
                                    >
                                        Personalizar
                                    </a>
                                <?php else: ?>
                                    <button 
                                        disabled
                                        class="bg-slate-400 text-white text-[10px] uppercase font-bold py-2 px-3 rounded shadow-lg flex-1 cursor-not-allowed"
                                    >
                                        Agotado
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="p-4 text-center">
                            <a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>">
                                <h4 class="font-bold text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($product['name']); ?></h4>
                                <p class="text-primary font-bold mt-1"><?php echo $productPrice; ?></p>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Evitar que click/drag en el canvas 3D navegue al producto (una vez por tarjeta)
    document.querySelectorAll('.card-3d').forEach(function(el) {
        el.addEventListener('click', function(ev) { ev.stopPropagation(); });
        el.addEventListener('mousedown', function(ev) { ev.stopPropagation(); });
        el.addEventListener('touchstart', function(ev) { ev.stopPropagation(); }, { passive: true });
    });

    // Cerrar el visor 3D de una tarjeta y volver a mostrar la imagen
    function closeCard3D(card) {
        if (!card) return;
        var card3d = card.querySelector('.card-3d');
        var imageLink = card.querySelector('.card-image-link');
        var imageWrap = card.querySelector('.card-image');
        var btn = card.querySelector('.card-toggle-3d');
        if (card._viewerInstance && typeof disposeStatic3DViewer === 'function') {
            disposeStatic3DViewer(card._viewerInstance);
            card._viewerInstance = null;
        }
        if (card3d) card3d.classList.add('hidden');
        if (imageLink) imageLink.style.pointerEvents = '';
        if (imageWrap) imageWrap.style.visibility = '';
        if (btn) btn.textContent = 'Ver en 3D';
    }

    // Click en tarjeta: ir al producto (excepto botón, formulario o enlace)
    document.querySelectorAll('.product-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            var productUrl = card.dataset.productUrl;
            if (!productUrl) return;
            if (e.target.closest('button') || e.target.closest('form') || e.target.closest('a')) return;
            e.preventDefault();
            window.location.href = productUrl;
        });
    });

    document.querySelectorAll('.card-toggle-3d').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var card = this.closest('.product-card');
            var imageLink = card.querySelector('.card-image-link');
            var imageWrap = card.querySelector('.card-image');
            var card3d = card.querySelector('.card-3d');

            if (card3d.classList.contains('hidden')) {
                // Abrir 3D en esta tarjeta: primero cerrar cualquier otra que tenga 3D abierto
                document.querySelectorAll('.product-card').forEach(function(other) {
                    if (other !== card && other.querySelector('.card-3d') && !other.querySelector('.card-3d').classList.contains('hidden')) {
                        closeCard3D(other);
                    }
                });

                card3d.classList.remove('hidden');
                if (imageLink) imageLink.style.pointerEvents = 'none';
                if (imageWrap) imageWrap.style.visibility = 'hidden';
                this.textContent = 'Ver imagen';

                function waitForSize(el, cb, tries) {
                    if (tries === undefined) tries = 60;
                    if (!el) return cb();
                    var r = el.getBoundingClientRect();
                    if (r.width > 10 && r.height > 10) return cb();
                    if (tries <= 0) return cb();
                    requestAnimationFrame(function() { waitForSize(el, cb, tries - 1); });
                }

                var viewerDiv = card3d.querySelector('.static-3d-viewer');
                if (viewerDiv && !card._viewerInstance) {
                    viewerDiv.style.position = 'absolute';
                    viewerDiv.style.inset = '0';
                    viewerDiv.style.width = '100%';
                    viewerDiv.style.height = '100%';

                    waitForSize(viewerDiv, function() {
                        if (!card._viewerInstance && viewerDiv.parentNode) {
                            var viewer = typeof initOneStatic3DViewer === 'function' ? initOneStatic3DViewer(viewerDiv) : null;
                            if (viewer) {
                                card._viewerInstance = viewer;
                                setTimeout(function() { if (viewer.onWindowResize) viewer.onWindowResize(); }, 0);
                                setTimeout(function() { if (viewer.onWindowResize) viewer.onWindowResize(); }, 150);
                            }
                        }
                    });
                }
            } else {
                closeCard3D(card);
            }
        });
    });

    function updateMaterialFilter(checkbox) {
        var checkboxes = document.querySelectorAll('input[name="material"]');
        if (checkbox.checked) {
            checkboxes.forEach(function(cb) {
                if (cb !== checkbox) cb.checked = false;
            });
            document.getElementById('filterForm').submit();
        } else {
            checkbox.checked = false;
            document.getElementById('filterForm').submit();
        }
    }
    window.updateMaterialFilter = updateMaterialFilter;
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>