<?php
$pageTitle = 'Finalizar Pedido - My3DStore';
$useTailwindBody = true;
$loadStatic3D = true;
include __DIR__ . '/../../includes/header.php';
?>

<main class="container mx-auto px-4 py-8 flex-grow">
    <h1 class="text-2xl font-bold mb-8">Finalizar Pedido</h1>
    
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Lista de productos (mismo aspecto que en la cesta) -->
        <div class="flex-1 space-y-4">
            <h2 class="text-xl font-bold mb-4">Resumen del Pedido</h2>
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
                $checkoutItemMediaJson = htmlspecialchars(json_encode($cardMedia), ENT_QUOTES, 'UTF-8');
                $checkoutModelPath = $hasOnlyModel ? productModelAsset($itemProduct) : '';
                $checkoutFallbackPath = asset('glb/pato.glb');
                
                $description = [];
                if (!empty($item['material'])) {
                    $description[] = 'Material: ' . htmlspecialchars($item['material']);
                }
                if (isset($item['dim_x']) && $item['dim_x'] !== null && $item['dim_x'] !== '') {
                    $description[] = 'Dimensiones: ' . htmlspecialchars(formatDimensions($item['dim_x'] ?? null, $item['dim_y'] ?? null, $item['dim_z'] ?? null));
                }
                $productDescription = !empty($description) ? implode(' | ', $description) : 'Producto personalizado';
            ?>
                <div class="checkout-item-cart bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-4" data-checkout-media="<?php echo $checkoutItemMediaJson; ?>">
                    <div class="w-full sm:w-32 h-32 rounded-lg overflow-hidden flex-shrink-0 relative bg-slate-100 dark:bg-slate-700">
                        <?php if ($hasOnlyModel): ?>
                        <div class="checkout-item-3d-wrap absolute inset-0 w-full h-full">
                            <div class="checkout-static-3d-viewer static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($checkoutModelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($checkoutFallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"<?php if (!empty($item['color'])): ?> data-color="<?php echo htmlspecialchars($item['color']); ?>"<?php endif; ?><?php if (isset($item['dim_x']) && $item['dim_x'] !== null && $item['dim_x'] !== ''): ?> data-dim-x="<?php echo htmlspecialchars($item['dim_x']); ?>" data-dim-y="<?php echo htmlspecialchars($item['dim_y'] ?? $item['dim_x']); ?>" data-dim-z="<?php echo htmlspecialchars($item['dim_z'] ?? $item['dim_x']); ?>"<?php endif; ?><?php if (!empty($item['logo_url'])): ?> data-logo-url="<?php echo htmlspecialchars(asset($item['logo_url'])); ?>"<?php endif; ?><?php if (!empty($item['logo_side'])): ?> data-logo-side="<?php echo htmlspecialchars($item['logo_side']); ?>"<?php endif; ?>></div>
                        </div>
                        <?php else: ?>
                        <div class="checkout-item-image absolute inset-0 flex items-center justify-center bg-slate-100 dark:bg-slate-700">
                            <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="checkout-item-img w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
                            <span class="material-icons-outlined text-4xl text-slate-400 absolute inset-0 flex items-center justify-center" style="display:none">image</span>
                        </div>
                        <?php endif; ?>
                        <?php if (count($cardMedia) > 1 && !$hasOnlyModel): ?>
                        <button type="button" class="checkout-item-prev absolute left-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Anterior">
                            <span class="material-icons-outlined text-lg">chevron_left</span>
                        </button>
                        <button type="button" class="checkout-item-next absolute right-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Siguiente">
                            <span class="material-icons-outlined text-lg">chevron_right</span>
                        </button>
                        <div class="checkout-item-dot absolute bottom-1 left-1/2 -translate-x-1/2 z-10 text-[10px] text-white font-medium rounded px-2 py-0.5 bg-black/50">1/<?php echo count($cardMedia); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-lg"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1"><?php echo $productDescription; ?></p>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-slate-600 dark:text-slate-400">Cantidad: <?php echo $item['quantity']; ?> · <?php echo formatPrice($item['price']); ?> ud.</span>
                            <span class="font-bold text-lg text-primary"><?php echo formatPrice($itemTotal); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Formulario: dirección, pago y confirmar -->
        <div class="lg:w-96">
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md border border-slate-200 dark:border-slate-700 sticky top-8 checkout-form-container">
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
                <form method="POST" action="/My3DStore/?action=checkout" class="checkout-form">
                <h2 class="text-lg font-bold mt-4 mb-2">Dirección de Envío</h2>
                <div class="form-group">
                    <label for="shipping_address">Dirección completa:</label>
                    <textarea id="shipping_address" name="shipping_address" rows="4" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>

                <h2 class="text-lg font-bold mt-4 mb-2">Método de pago</h2>
                <div class="form-group payment-methods">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="card" checked>
                        <span>Tarjeta</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="paypal">
                        <span>PayPal</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="bizum">
                        <span>Bizum</span>
                    </label>
                </div>
                <div id="card-fields" class="form-group card-fields">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="card_number">Número de tarjeta</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="cc-number">
                        </div>
                        <div class="form-group">
                            <label for="card_expiry">Caducidad (MM/AA)</label>
                            <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/AA" maxlength="5" autocomplete="cc-exp">
                        </div>
                        <div class="form-group">
                            <label for="card_cvv">CVV</label>
                            <input type="text" id="card_cvv" name="card_cvv" placeholder="123" maxlength="4" autocomplete="cc-csc">
                        </div>
                    </div>
                </div>
                <div id="paypal-note" class="payment-note" style="display:none;">
                    <p>Completarás el pago con PayPal después de confirmar el pedido.</p>
                </div>
                <div id="bizum-note" class="payment-note" style="display:none;">
                    <p>Completarás el pago con Bizum después de confirmar el pedido.</p>
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition-colors shadow-lg shadow-blue-500/20 mt-4">
                    Confirmar Pedido
                </button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var MAX_VIEWERS = 5;
    function toAbsoluteUrl(url) {
        if (!url || url.indexOf('http') === 0) return url;
        var origin = window.location.origin;
        return origin + (url.charAt(0) === '/' ? url : '/' + url);
    }
    var containers = document.querySelectorAll('.checkout-static-3d-viewer');
    var viewers = [];
    containers.forEach(function(container, i) {
        if (i >= MAX_VIEWERS) return;
        if (typeof initOneStatic3DViewer !== 'function') return;
        var viewer = initOneStatic3DViewer(container);
        if (viewer) {
            viewers.push(viewer);
            setTimeout(function() { if (viewer.onWindowResize) viewer.onWindowResize(); }, 0);
        }
    });
    setTimeout(function() {
        viewers.forEach(function(v) { if (v && v.onWindowResize) v.onWindowResize(); });
    }, 200);
    document.querySelectorAll('.checkout-item-cart').forEach(function(row) {
        var mediaJson = row.dataset.checkoutMedia;
        if (!mediaJson) return;
        var media = [];
        try { media = JSON.parse(mediaJson); } catch (e) { return; }
        if (media.length <= 1) return;
        var index = 0;
        var imgEl = row.querySelector('.checkout-item-img');
        var dotEl = row.querySelector('.checkout-item-dot');
        function showSlide(i) {
            index = (i + media.length) % media.length;
            var item = media[index];
            if (item.type === 'image' && imgEl) imgEl.src = toAbsoluteUrl(item.url);
            if (dotEl) dotEl.textContent = (index + 1) + '/' + media.length;
        }
        var btnPrev = row.querySelector('.checkout-item-prev');
        var btnNext = row.querySelector('.checkout-item-next');
        if (btnPrev) btnPrev.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); showSlide(index - 1); });
        if (btnNext) btnNext.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); showSlide(index + 1); });
    });
});
</script>
<script>
(function() {
    var methodRadios = document.querySelectorAll('input[name="payment_method"]');
    var cardFields = document.getElementById('card-fields');
    var paypalNote = document.getElementById('paypal-note');
    var bizumNote = document.getElementById('bizum-note');
    function updatePaymentUI() {
        var v = document.querySelector('input[name="payment_method"]:checked').value;
        cardFields.style.display = v === 'card' ? 'block' : 'none';
        paypalNote.style.display = v === 'paypal' ? 'block' : 'none';
        bizumNote.style.display = v === 'bizum' ? 'block' : 'none';
    }
    methodRadios.forEach(function(r) { r.addEventListener('change', updatePaymentUI); });
    updatePaymentUI();
})();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

