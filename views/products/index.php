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
                    $cardImages = productImageAssets($product);
                    $cardModels = productModelAssets($product);
                    $cardMedia = [];
                    foreach ($cardImages as $u) { $cardMedia[] = ['type' => 'image', 'url' => $u]; }
                    foreach ($cardModels as $u) { $cardMedia[] = ['type' => 'model', 'url' => $u]; }
                    if (empty($cardMedia)) {
                        $cardMedia[] = ['type' => 'image', 'url' => 'https://via.placeholder.com/400x400?text=3D+Product'];
                    }
                    $productImage = $cardMedia[0]['type'] === 'image' ? $cardMedia[0]['url'] : (count($cardImages) ? $cardImages[0] : 'https://via.placeholder.com/400x400?text=3D+Product');
                    $productPrice = formatPrice($product['price']);
                    $hasModel = count($cardModels) > 0;
                    $modelPath = $hasModel ? $cardModels[0] : '';
                    $fallbackPath = asset('glb/pato.glb');
                    $cardMediaJson = htmlspecialchars(json_encode($cardMedia), ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="bg-card-light dark:bg-card-dark rounded-xl overflow-hidden shadow-md group transition-all hover:shadow-xl hover:-translate-y-1 product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" data-product-media="<?php echo $cardMediaJson; ?>">
                        <div class="relative overflow-hidden aspect-square bg-[#003d7e] card-carousel-wrap">
                            <a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0">
                                <div class="card-image relative w-full h-full flex items-center justify-center" style="min-height:200px;">
                                    <img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-carousel-img w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
                                    <span class="material-icons-outlined text-white/90 text-5xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
                                </div>
                            </a>
                            <div class="card-3d absolute inset-0 w-full h-full min-h-0 hidden z-10 pointer-events-auto bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden">
                                <div class="card-static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($modelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($fallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"></div>
                            </div>
                            <?php if (count($cardMedia) > 1): ?>
                            <button type="button" class="card-carousel-prev absolute left-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Anterior">
                                <span class="material-icons-outlined text-lg">chevron_left</span>
                            </button>
                            <button type="button" class="card-carousel-next absolute right-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Siguiente">
                                <span class="material-icons-outlined text-lg">chevron_right</span>
                            </button>
                            <div class="card-carousel-dot absolute bottom-1 left-0 right-0 flex justify-center gap-1 z-20 text-[10px] text-white/90">1/<?php echo count($cardMedia); ?></div>
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
    var MAX_VIEWERS = 6;
    var activeViewerCards = [];

    document.querySelectorAll('.card-3d').forEach(function(el) {
        el.addEventListener('click', function(ev) { ev.stopPropagation(); });
        el.addEventListener('mousedown', function(ev) { ev.stopPropagation(); });
        el.addEventListener('touchstart', function(ev) { ev.stopPropagation(); }, { passive: true });
    });

    function toAbsoluteUrl(url) {
        if (!url || url.indexOf('http') === 0) return url;
        var origin = window.location.origin;
        return origin + (url.charAt(0) === '/' ? url : '/' + url);
    }

    function ensureViewerForCard(card, modelUrl, callback) {
        var card3d = card.querySelector('.card-3d');
        var viewerDiv = card3d ? card3d.querySelector('.card-static-3d-viewer') : null;
        if (!viewerDiv) return callback(null);
        if (card._viewerInstance) {
            if (card._viewerInstance.loadModelFromUrl) card._viewerInstance.loadModelFromUrl(toAbsoluteUrl(modelUrl));
            return callback(card._viewerInstance);
        }
        while (activeViewerCards.length >= MAX_VIEWERS && activeViewerCards.length > 0) {
            var old = activeViewerCards.shift();
            if (old._viewerInstance && typeof disposeStatic3DViewer === 'function') {
                disposeStatic3DViewer(old._viewerInstance);
                old._viewerInstance = null;
            }
        }
        viewerDiv.dataset.modelPath = modelUrl;
        var viewer = typeof initOneStatic3DViewer === 'function' ? initOneStatic3DViewer(viewerDiv) : null;
        if (viewer) {
            card._viewerInstance = viewer;
            activeViewerCards.push(card);
            setTimeout(function() { if (viewer.onWindowResize) viewer.onWindowResize(); }, 0);
        }
        callback(viewer);
    }

    function showCardSlide(card, index) {
        var mediaJson = card.dataset.productMedia;
        if (!mediaJson) return;
        var media = [];
        try { media = JSON.parse(mediaJson); } catch (e) { return; }
        if (media.length === 0) return;
        index = (index + media.length) % media.length;
        card._carouselIndex = index;
        var item = media[index];
        var wrap = card.querySelector('.card-carousel-wrap');
        var imageLink = card.querySelector('.card-image-link');
        var imageWrap = card.querySelector('.card-image');
        var imgEl = card.querySelector('.card-carousel-img');
        var card3d = card.querySelector('.card-3d');
        var dotEl = card.querySelector('.card-carousel-dot');

        if (item.type === 'image') {
            if (imageLink) imageLink.style.pointerEvents = '';
            if (imageWrap) imageWrap.style.visibility = 'visible';
            if (imgEl) imgEl.src = toAbsoluteUrl(item.url);
            if (card3d) card3d.classList.add('hidden');
        } else {
            if (imageLink) imageLink.style.pointerEvents = 'none';
            if (imageWrap) imageWrap.style.visibility = 'hidden';
            if (card3d) card3d.classList.remove('hidden');
            ensureViewerForCard(card, item.url, function() {
                if (dotEl) dotEl.textContent = (index + 1) + '/' + media.length;
            });
        }
        if (dotEl) dotEl.textContent = (index + 1) + '/' + media.length;
    }

    document.querySelectorAll('.product-card').forEach(function(card) {
        card._carouselIndex = 0;

        card.addEventListener('click', function(e) {
            var productUrl = card.dataset.productUrl;
            if (!productUrl) return;
            if (e.target.closest('button') || e.target.closest('form') || e.target.closest('a')) return;
            e.preventDefault();
            window.location.href = productUrl;
        });

        var btnPrev = card.querySelector('.card-carousel-prev');
        var btnNext = card.querySelector('.card-carousel-next');
        if (btnPrev) btnPrev.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showCardSlide(card, (card._carouselIndex || 0) - 1);
        });
        if (btnNext) btnNext.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showCardSlide(card, (card._carouselIndex || 0) + 1);
        });

        var mediaJson = card.dataset.productMedia;
        if (mediaJson) {
            try {
                var media = JSON.parse(mediaJson);
                if (media.length > 0 && media[0].type === 'model') {
                    card._carouselIndex = 0;
                    var imageWrap = card.querySelector('.card-image');
                    var card3d = card.querySelector('.card-3d');
                    if (imageWrap) imageWrap.style.visibility = 'hidden';
                    if (card.querySelector('.card-image-link')) card.querySelector('.card-image-link').style.pointerEvents = 'none';
                    if (card3d) card3d.classList.remove('hidden');
                    ensureViewerForCard(card, media[0].url, function() {});
                }
            } catch (e) {}
        }
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