<?php
$pageTitle = 'Detalle del Pedido #' . $order['id'];
$useTailwindBody = true;
$loadStatic3D = true;
include __DIR__ . '/../../includes/header.php';
?>

<main class="container mx-auto px-4 py-8 flex-grow">
    <h1 class="text-2xl font-bold mb-8">Pedido #<?php echo $order['id']; ?></h1>
    
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Información del pedido -->
        <div class="lg:w-96 flex-shrink-0">
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md border border-slate-200 dark:border-slate-700">
                <h2 class="text-xl font-bold mb-4">Información del Pedido</h2>
                <div class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1">
                        <strong class="text-slate-600 dark:text-slate-400">Fecha:</strong>
                        <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1">
                        <strong class="text-slate-600 dark:text-slate-400">Estado:</strong>
                        <span class="status-badge status-<?php echo $order['status']; ?> inline-flex px-3 py-1 rounded-full text-sm font-medium">
                            <?php 
                            $statusLabels = [
                                'pending' => 'Pendiente',
                                'processing' => 'En proceso',
                                'shipped' => 'Enviado',
                                'delivered' => 'Entregado',
                                'cancelled' => 'Cancelado'
                            ];
                            echo $statusLabels[$order['status']] ?? $order['status'];
                            ?>
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <strong class="text-slate-600 dark:text-slate-400">Dirección de envío:</strong>
                        <span class="text-slate-700 dark:text-slate-300"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></span>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <a href="<?php echo htmlspecialchars(url('orders')); ?>" class="inline-block bg-slate-200 dark:bg-slate-600 hover:bg-slate-300 dark:hover:bg-slate-500 text-slate-800 dark:text-slate-100 font-medium py-2 px-4 rounded-xl transition-colors">
                        Volver a Pedidos
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Productos (mismo aspecto que cesta y checkout) -->
        <div class="flex-1 space-y-4">
            <h2 class="text-xl font-bold mb-4">Productos</h2>
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
                $orderItemMediaJson = htmlspecialchars(json_encode($cardMedia), ENT_QUOTES, 'UTF-8');
                $orderModelPath = $hasOnlyModel ? productModelAsset($itemProduct) : '';
                $orderFallbackPath = asset('glb/pato.glb');
                
                $description = [];
                if (!empty($item['material'])) {
                    $description[] = 'Material: ' . htmlspecialchars($item['material']);
                }
                if (isset($item['dim_x']) && $item['dim_x'] !== null && $item['dim_x'] !== '') {
                    $description[] = 'Dimensiones: ' . htmlspecialchars(formatDimensions($item['dim_x'] ?? null, $item['dim_y'] ?? null, $item['dim_z'] ?? null));
                }
                $productDescription = !empty($description) ? implode(' | ', $description) : 'Producto personalizado';
            ?>
                <div class="order-show-item bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-4" data-order-media="<?php echo $orderItemMediaJson; ?>">
                    <div class="w-full sm:w-32 h-32 rounded-lg overflow-hidden flex-shrink-0 relative bg-slate-100 dark:bg-slate-700">
                        <?php if ($hasOnlyModel): ?>
                        <div class="order-show-3d-wrap absolute inset-0 w-full h-full">
                            <div class="order-show-static-3d-viewer static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($orderModelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($orderFallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"<?php if (!empty($item['color'])): ?> data-color="<?php echo htmlspecialchars($item['color']); ?>"<?php endif; ?><?php if (isset($item['dim_x']) && $item['dim_x'] !== null && $item['dim_x'] !== ''): ?> data-dim-x="<?php echo htmlspecialchars($item['dim_x']); ?>" data-dim-y="<?php echo htmlspecialchars($item['dim_y'] ?? $item['dim_x']); ?>" data-dim-z="<?php echo htmlspecialchars($item['dim_z'] ?? $item['dim_x']); ?>"<?php endif; ?><?php if (!empty($item['logo_url'])): ?> data-logo-url="<?php echo htmlspecialchars(asset($item['logo_url'])); ?>"<?php endif; ?><?php if (!empty($item['logo_side'])): ?> data-logo-side="<?php echo htmlspecialchars($item['logo_side']); ?>"<?php endif; ?>></div>
                        </div>
                        <?php else: ?>
                        <div class="order-show-image absolute inset-0 flex items-center justify-center bg-slate-100 dark:bg-slate-700">
                            <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-show-img w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
                            <span class="material-icons-outlined text-4xl text-slate-400 absolute inset-0 flex items-center justify-center" style="display:none">image</span>
                        </div>
                        <?php endif; ?>
                        <?php if (count($cardMedia) > 1 && !$hasOnlyModel): ?>
                        <button type="button" class="order-show-prev absolute left-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Anterior">
                            <span class="material-icons-outlined text-lg">chevron_left</span>
                        </button>
                        <button type="button" class="order-show-next absolute right-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Siguiente">
                            <span class="material-icons-outlined text-lg">chevron_right</span>
                        </button>
                        <div class="order-show-dot absolute bottom-1 left-1/2 -translate-x-1/2 z-10 text-[10px] text-white font-medium rounded px-2 py-0.5 bg-black/50">1/<?php echo count($cardMedia); ?></div>
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
            
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <strong class="text-lg">Total</strong>
                <span class="font-bold text-xl text-primary"><?php echo formatPrice($order['total']); ?></span>
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
    var containers = document.querySelectorAll('.order-show-static-3d-viewer');
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
    document.querySelectorAll('.order-show-item').forEach(function(row) {
        var mediaJson = row.dataset.orderMedia;
        if (!mediaJson) return;
        var media = [];
        try { media = JSON.parse(mediaJson); } catch (e) { return; }
        if (media.length <= 1) return;
        var index = 0;
        var imgEl = row.querySelector('.order-show-img');
        var dotEl = row.querySelector('.order-show-dot');
        function showSlide(i) {
            index = (i + media.length) % media.length;
            var item = media[index];
            if (item.type === 'image' && imgEl) imgEl.src = toAbsoluteUrl(item.url);
            if (dotEl) dotEl.textContent = (index + 1) + '/' + media.length;
        }
        var btnPrev = row.querySelector('.order-show-prev');
        var btnNext = row.querySelector('.order-show-next');
        if (btnPrev) btnPrev.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); showSlide(index - 1); });
        if (btnNext) btnNext.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); showSlide(index + 1); });
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
