<?php
$pageTitle = 'Cesta de Compra - My3DStore';
$useTailwindBody = true;
include __DIR__ . '/../../includes/header.php';
?>

<style>
    body { font-family: 'Inter', system-ui, sans-serif; }
</style>

<main class="container mx-auto px-4 py-8 flex-grow">
    <h1 class="text-2xl font-bold mb-8">Tu Cesta de Compra</h1>
    
    <?php if (empty($items)): ?>
        <div class="text-center py-12">
            <p class="text-slate-500 text-lg mb-4">Tu carrito está vacío.</p>
            <a href="/My3DStore/?action=products" class="inline-block bg-primary hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-colors">
                Ver Productos
            </a>
        </div>
    <?php else: ?>
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Lista de productos en el carrito -->
            <div class="flex-1 space-y-4">
                <?php foreach ($items as $item): 
                    $itemTotal = $item['price'] * $item['quantity'];
                    $itemProduct = array_merge($item, ['id' => $item['product_id']]);
                    $cardImages = productImageAssets($itemProduct);
                    $cardModels = productModelAssets($itemProduct);
                    $cardMedia = [];
                    foreach ($cardImages as $u) { $cardMedia[] = ['type' => 'image', 'url' => $u]; }
                    $hasOnlyModel = (empty($cardImages) && !empty($cardModels));
                    if (empty($cardMedia)) {
                        $cardMedia[] = ['type' => 'image', 'url' => $hasOnlyModel ? '' : 'https://via.placeholder.com/400x400?text=Producto'];
                    }
                    $firstImage = $cardMedia[0]['url'] ?? '';
                    $cartItemMediaJson = htmlspecialchars(json_encode($cardMedia), ENT_QUOTES, 'UTF-8');
                    $cartModelPath = $hasOnlyModel ? productModelAsset($itemProduct) : '';
                    $cartFallbackPath = asset('glb/pato.glb');
                    
                    $description = [];
                    if (!empty($item['material'])) {
                        $description[] = 'Material: ' . htmlspecialchars($item['material']);
                    }
                    if (isset($item['dim_x']) && $item['dim_x'] !== null && $item['dim_x'] !== '') {
                        $description[] = 'Dimensiones: ' . htmlspecialchars(formatDimensions($item['dim_x'] ?? null, $item['dim_y'] ?? null, $item['dim_z'] ?? null));
                    }
                    $productDescription = !empty($description) ? implode(' | ', $description) : 'Producto personalizado';
                ?>
                    <div class="cart-item bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-4" data-cart-media="<?php echo $cartItemMediaJson; ?>">
                        <!-- Carrusel imagen / 3D del producto con flechas -->
                        <div class="w-full sm:w-32 h-32 rounded-lg overflow-hidden flex-shrink-0 relative bg-slate-100 dark:bg-slate-700">
                            <?php if ($hasOnlyModel): ?>
                            <div class="cart-item-3d-wrap absolute inset-0 w-full h-full">
                                <div class="cart-static-3d-viewer static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($cartModelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($cartFallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"<?php if (!empty($item['color'])): ?> data-color="<?php echo htmlspecialchars($item['color']); ?>"<?php endif; ?><?php if (isset($item['dim_x']) && $item['dim_x'] !== null && $item['dim_x'] !== ''): ?> data-dim-x="<?php echo htmlspecialchars($item['dim_x']); ?>" data-dim-y="<?php echo htmlspecialchars($item['dim_y'] ?? $item['dim_x']); ?>" data-dim-z="<?php echo htmlspecialchars($item['dim_z'] ?? $item['dim_x']); ?>"<?php endif; ?><?php if (!empty($item['logo_url'])): ?> data-logo-url="<?php echo htmlspecialchars(asset($item['logo_url'])); ?>"<?php endif; ?><?php if (!empty($item['logo_side'])): ?> data-logo-side="<?php echo htmlspecialchars($item['logo_side']); ?>"<?php endif; ?>></div>
                            </div>
                            <?php else: ?>
                            <div class="cart-item-image absolute inset-0 flex items-center justify-center bg-slate-100 dark:bg-slate-700">
                                <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
                                <span class="material-icons-outlined text-4xl text-slate-400 absolute inset-0 flex items-center justify-center" style="display:none">image</span>
                            </div>
                            <?php endif; ?>
                            <?php if (count($cardMedia) > 1 && !$hasOnlyModel): ?>
                            <button type="button" class="cart-item-prev absolute left-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Anterior">
                                <span class="material-icons-outlined text-lg">chevron_left</span>
                            </button>
                            <button type="button" class="cart-item-next absolute right-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Siguiente">
                                <span class="material-icons-outlined text-lg">chevron_right</span>
                            </button>
                            <div class="cart-item-dot absolute bottom-1 left-1/2 -translate-x-1/2 z-10 text-[10px] text-white font-medium rounded px-2 py-0.5 bg-black/50">1/<?php echo count($cardMedia); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Información del producto -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-lg"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <form method="POST" action="/My3DStore/?action=cart-remove" class="inline">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <button 
                                            type="submit" 
                                            class="text-slate-400 hover:text-red-500 transition-colors"
                                            onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?');"
                                        >
                                            <span class="material-icons-outlined">delete</span>
                                        </button>
                                    </form>
                                </div>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                                    <?php echo $productDescription; ?>
                                </p>
                            </div>
                            
                            <!-- Controles de cantidad y precio -->
                            <div class="flex justify-between items-center mt-4">
                                <!-- Selector de cantidad -->
                                <div class="flex items-center border border-slate-200 dark:border-slate-600 rounded-lg">
                                    <form method="POST" action="/My3DStore/?action=cart-update" class="flex items-center" id="quantityForm<?php echo $item['product_id']; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <button 
                                            type="button"
                                            class="px-3 py-1 hover:bg-slate-50 dark:hover:bg-slate-700 border-r border-slate-200 dark:border-slate-600"
                                            onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1, <?php echo isset($item['stock']) ? (int)$item['stock'] : 999; ?>)"
                                        >
                                            -
                                        </button>
                                        <input 
                                            type="number" 
                                            name="quantity" 
                                            value="<?php echo $item['quantity']; ?>" 
                                            min="1" 
                                            max="<?php echo isset($item['stock']) ? (int)$item['stock'] : 999; ?>"
                                            class="px-4 py-1 font-medium w-16 text-center border-0 focus:ring-0 focus:outline-none bg-transparent"
                                            id="quantity<?php echo $item['product_id']; ?>"
                                            onchange="document.getElementById('quantityForm<?php echo $item['product_id']; ?>').submit();"
                                        />
                                        <button 
                                            type="button"
                                            class="px-3 py-1 hover:bg-slate-50 dark:hover:bg-slate-700 border-l border-slate-200 dark:border-slate-600"
                                            onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1, <?php echo isset($item['stock']) ? (int)$item['stock'] : 999; ?>)"
                                        >
                                            +
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Precio total del item -->
                                <span class="font-bold text-lg text-primary">
                                    <?php echo formatPrice($itemTotal); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Resumen del pedido -->
            <div class="lg:w-96">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md border border-slate-200 dark:border-slate-700 sticky top-8">
                    <h2 class="text-xl font-bold mb-6">Resumen del pedido</h2>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Envío</span>
                            <span class="text-green-500">Gratis</span>
                        </div>
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex justify-between font-bold text-xl">
                            <span>Total</span>
                            <span class="text-primary"><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                    
                    <a 
                        href="/My3DStore/?action=checkout" 
                        class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition-colors shadow-lg shadow-blue-500/20 mb-4 block text-center"
                    >
                        Tramitar pedido
                    </a>
                    
                    <div class="flex items-center justify-center gap-2 text-xs text-slate-400">
                        <span class="material-icons-outlined text-sm">lock</span>
                        <span>Pago 100% seguro y encriptado</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var MAX_CART_VIEWERS = 5;

    function toAbsoluteUrl(url) {
        if (!url || url.indexOf('http') === 0) return url;
        var origin = window.location.origin;
        return origin + (url.charAt(0) === '/' ? url : '/' + url);
    }

    // Inicializar visores 3D en items de la cesta (solo modelo, sin imagen)
    var cartViewerContainers = document.querySelectorAll('.cart-static-3d-viewer');
    cartViewerContainers.forEach(function(container, i) {
        if (i >= MAX_CART_VIEWERS) return;
        if (typeof initOneStatic3DViewer !== 'function') return;
        var viewer = initOneStatic3DViewer(container);
        if (viewer) setTimeout(function() { if (viewer.onWindowResize) viewer.onWindowResize(); }, 0);
    });

    // Carrusel con flechas en cada item de la cesta
    document.querySelectorAll('.cart-item').forEach(function(row) {
        var mediaJson = row.dataset.cartMedia;
        if (!mediaJson) return;
        var media = [];
        try { media = JSON.parse(mediaJson); } catch (e) { return; }
        if (media.length <= 1) return;
        var index = 0;
        var imgEl = row.querySelector('.cart-item-img');
        var dotEl = row.querySelector('.cart-item-dot');
        function showSlide(i) {
            index = (i + media.length) % media.length;
            var item = media[index];
            if (item.type === 'image' && imgEl) {
                imgEl.src = toAbsoluteUrl(item.url);
            }
            if (dotEl) dotEl.textContent = (index + 1) + '/' + media.length;
        }
        var btnPrev = row.querySelector('.cart-item-prev');
        var btnNext = row.querySelector('.cart-item-next');
        if (btnPrev) btnPrev.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); showSlide(index - 1); });
        if (btnNext) btnNext.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); showSlide(index + 1); });
    });

    function updateQuantity(productId, change, maxStock) {
        const quantityInput = document.getElementById('quantity' + productId);
        let currentQuantity = parseInt(quantityInput.value) || 1;
        let newQuantity = currentQuantity + change;
        
        // Validar límites
        if (newQuantity < 1) {
            newQuantity = 1;
        }
        if (newQuantity > maxStock) {
            newQuantity = maxStock;
            alert('No hay suficiente stock disponible');
        }
        
        // Actualizar el valor
        quantityInput.value = newQuantity;
        
        // Enviar el formulario
        document.getElementById('quantityForm' + productId).submit();
    }
    window.updateQuantity = updateQuantity;
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
