<?php
$pageTitle = $product['name'];
$loadStatic3D = true; // Cargar modelos 3D estáticos
include __DIR__ . '/../../includes/header.php';

$productImages = productImageAssets($product);
$productModels = productModelAssets($product);
$productMedia = [];
foreach ($productImages as $url) {
    $productMedia[] = ['type' => 'image', 'url' => $url];
}
foreach ($productModels as $url) {
    $productMedia[] = ['type' => 'model', 'url' => $url];
}
if (empty($productMedia)) {
    $productMedia[] = ['type' => 'model', 'url' => asset('glb/pato.glb')];
}
$productMediaJson = json_encode($productMedia);
$fallbackModelUrl = htmlspecialchars(asset('glb/pato.glb'));
?>

<div class="product-detail">
    <div class="product-detail-container">
        <div class="product-images product-detail-carousel">
            <div class="product-carousel-wrap" style="position:relative; width:100%; height:500px; background:#e2e8f0; border-radius:0.75rem; overflow:hidden;" data-media-count="<?php echo count($productMedia); ?>" data-models-count="<?php echo count($productModels); ?>">
                <?php if (count($productMedia) > 1): ?>
                <button type="button" class="product-carousel-prev absolute left-2 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/90 dark:bg-slate-800/90 shadow-lg flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Anterior">
                    <span class="material-icons-outlined">chevron_left</span>
                </button>
                <button type="button" class="product-carousel-next absolute right-2 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/90 dark:bg-slate-800/90 shadow-lg flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Siguiente">
                    <span class="material-icons-outlined">chevron_right</span>
                </button>
                <?php endif; ?>
                <div class="product-carousel-slide product-carousel-slide-image absolute inset-0 flex items-center justify-center bg-[#e2e8f0] dark:bg-slate-800" style="z-index:5; display:none;">
                    <img src="" alt="<?php echo htmlspecialchars($product['name']); ?>" class="max-w-full max-h-full w-full h-full object-contain">
                </div>
                <div class="product-carousel-slide product-carousel-slide-model absolute inset-0" style="z-index:5;">
                    <div id="product-detail-3d" class="static-3d-viewer w-full h-full" style="width: 100%; height: 500px;"
                        data-model-path="<?php echo htmlspecialchars(($productModels[0] ?? asset('glb/pato.glb'))); ?>"
                        data-fallback-model-path="<?php echo $fallbackModelUrl; ?>"
                        <?php if (!empty($product['color'])): ?>data-color="<?php echo htmlspecialchars($product['color']); ?>"<?php endif; ?>
                        <?php if (isset($product['dim_x']) && $product['dim_x'] !== null && $product['dim_x'] !== ''): ?>data-dim-x="<?php echo htmlspecialchars($product['dim_x']); ?>" data-dim-y="<?php echo htmlspecialchars($product['dim_y'] ?? $product['dim_x']); ?>" data-dim-z="<?php echo htmlspecialchars($product['dim_z'] ?? $product['dim_x']); ?>"<?php endif; ?>
                        <?php if (!empty($product['logo_url'])): ?>data-logo-url="<?php echo htmlspecialchars(asset($product['logo_url'])); ?>"<?php endif; ?>
                        <?php if (!empty($product['logo_side'])): ?>data-logo-side="<?php echo htmlspecialchars($product['logo_side']); ?>"<?php endif; ?>
                    ></div>
                </div>
                <?php if (count($productMedia) > 1): ?>
                <div class="product-carousel-index text-center absolute bottom-2 left-0 right-0 z-20 text-sm text-white font-medium rounded px-3 py-1 bg-black/60 shadow-sm w-fit mx-auto">1 / <?php echo count($productMedia); ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="product-details">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <?php if ($ratingInfo['count'] > 0): ?>
                <div class="product-rating">
                    <span class="rating-stars">
                        <?php 
                        $avgRating = round($ratingInfo['avg_rating']);
                        for ($i = 1; $i <= 5; $i++): 
                        ?>
                            <span class="star <?php echo $i <= $avgRating ? 'filled' : ''; ?>">★</span>
                        <?php endfor; ?>
                    </span>
                    <span class="rating-text">(<?php echo $ratingInfo['count']; ?> reseñas)</span>
                </div>
            <?php endif; ?>
            
            <p class="product-price-large"><?php echo formatPrice($product['price']); ?></p>
            
            <div class="product-description">
                <h3>Descripción</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
            
            <div class="product-specs">
                <h3>Especificaciones</h3>
                <?php if (isset($product['dim_x']) && $product['dim_x'] !== null && $product['dim_x'] !== ''): ?>
                    <div class="spec-item">
                        <span><strong>Dimensiones (X × Y × Z):</strong></span>
                        <span><?php echo htmlspecialchars(formatDimensions($product['dim_x'], $product['dim_y'] ?? null, $product['dim_z'] ?? null)); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($product['material'])): ?>
                    <div class="spec-item">
                        <span><strong>Material:</strong></span>
                        <span><?php echo htmlspecialchars($product['material']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($product['author'])): ?>
            <div class="product-author text-sm text-slate-500 dark:text-slate-400">
                <strong>Autor:</strong> <?php echo htmlspecialchars($product['author']); ?>
            </div>
            <?php endif; ?>
            
            <?php if (isLoggedIn()): ?>
                <div class="product-actions-detail">
                    <a href="/My3DStore/?action=checkout&product_id=<?php echo $product['id']; ?>&quantity=1" class="btn btn-primary btn-large">Compra ya</a>
                    <form method="POST" action="/My3DStore/?action=cart-add" style="flex: 1;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-secondary btn-large" style="width: 100%;">Añadir a la cesta</button>
                    </form>
                    <a href="<?php echo htmlspecialchars(url('customize', ['product_id' => $product['id']])); ?>" class="btn btn-secondary btn-large">Personalizar</a>
                </div>
            <?php elseif (!isLoggedIn()): ?>
                <div class="product-actions-detail">
                    <a href="/My3DStore/?action=login" class="btn btn-primary btn-large">Compra ya</a>
                    <a href="/My3DStore/?action=login" class="btn btn-secondary btn-large">Añadir a la cesta</a>
                    <a href="<?php echo htmlspecialchars(url('customize', ['product_id' => $product['id']])); ?>" class="btn btn-secondary btn-large">Personalizar</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="product-reviews">
        <h2>Reseñas</h2>
        
        <?php if (isLoggedIn() && $canReview): ?>
            <div class="review-form-container">
                <h3>Deja tu reseña</h3>
                <form method="POST" action="/My3DStore/?action=create-review" class="review-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="form-group">
                        <label>Calificación:</label>
                        <select name="rating" required>
                            <option value="">Selecciona...</option>
                            <option value="5">5 - Excelente</option>
                            <option value="4">4 - Muy bueno</option>
                            <option value="3">3 - Bueno</option>
                            <option value="2">2 - Regular</option>
                            <option value="1">1 - Malo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="comment">Comentario:</label>
                        <textarea id="comment" name="comment" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Publicar Reseña</button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if (empty($reviews)): ?>
            <p>No hay reseñas aún. Sé el primero en dejar una reseña.</p>
        <?php else: ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <strong><?php echo htmlspecialchars($review['user_name']); ?></strong>
                            <span class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </span>
                            <span class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                        </div>
                        <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var media = <?php echo $productMediaJson; ?>;
    var total = media.length;
    var currentIndex = 0;
    var wrap = document.querySelector('.product-carousel-wrap');
    if (!wrap) return;
    var slideImage = wrap.querySelector('.product-carousel-slide-image');
    var slideModel = wrap.querySelector('.product-carousel-slide-model');
    var imgEl = wrap.querySelector('.product-carousel-slide-image img');
    var viewerContainer = document.getElementById('product-detail-3d');
    var indexEl = wrap.querySelector('.product-carousel-index');
    var btnPrev = wrap.querySelector('.product-carousel-prev');
    var btnNext = wrap.querySelector('.product-carousel-next');

    function toAbsoluteUrl(url) {
        if (!url) return url;
        if (url.indexOf('http') === 0) return url;
        var origin = window.location.origin;
        var path = (url.charAt(0) === '/' ? url : '/' + url);
        return origin + path;
    }

    function showSlide(index) {
        // #region agent log
        (function(d){fetch('http://127.0.0.1:7243/ingest/15fdd762-84aa-46d7-b990-12290b881392',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)}).catch(function(){});console.log('[DEBUG H1]',d);})({location:'show.php:showSlide',message:'showSlide called',data:{index:index,total:total,nextIndex:(index+total)%total},timestamp:Date.now(),hypothesisId:'H1'});
        // #endregion
        if (total === 0) return;
        currentIndex = (index + total) % total;
        var item = media[currentIndex];
        if (!item) return;
        if (indexEl) indexEl.textContent = (currentIndex + 1) + ' / ' + total;
        if (item.type === 'image') {
            slideImage.style.display = 'flex';
            slideModel.style.display = 'none';
            if (imgEl) imgEl.src = toAbsoluteUrl(item.url);
        } else {
            slideImage.style.display = 'none';
            slideModel.style.display = 'block';
            var hasViewer = !!(window.productDetailViewer && typeof window.productDetailViewer.loadModelFromUrl === 'function');
            var itemUrlSlug = item.url ? item.url.replace(/^.*\//,'') : '';
            // #region agent log
            (function(d){fetch('http://127.0.0.1:7243/ingest/15fdd762-84aa-46d7-b990-12290b881392',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)}).catch(function(){});console.log('[DEBUG H2 H3]',d);})({location:'show.php:model branch',message:'model slide',data:{currentIndex:currentIndex,itemUrlSlug:itemUrlSlug,hasViewer:hasViewer,hasItemUrl:!!item.url},timestamp:Date.now(),hypothesisId:'H2 H3'});
            // #endregion
            if (item.url && window.productDetailViewer && typeof window.productDetailViewer.loadModelFromUrl === 'function') {
                var modelUrl = toAbsoluteUrl(item.url);
                window.productDetailViewer.loadModelFromUrl(modelUrl);
            }
        }
    }

    if (total <= 1) {
        if (btnPrev) btnPrev.style.display = 'none';
        if (btnNext) btnNext.style.display = 'none';
    }

    var firstItem = media[0];
    if (firstItem.type === 'image') {
        slideModel.style.display = 'none';
        slideImage.style.display = 'flex';
        if (imgEl) imgEl.src = toAbsoluteUrl(firstItem.url);
    } else {
        slideImage.style.display = 'none';
        slideModel.style.display = 'block';
    }
    if (viewerContainer) {
        window.productDetailViewer = new Static3DViewer(viewerContainer, {
            modelPath: firstItem.type === 'model' ? firstItem.url : (media.find(function(m) { return m.type === 'model'; }) || {}).url || '<?php echo $fallbackModelUrl; ?>',
            autoRotate: true,
            rotationSpeed: 0.5
        });
    }

    if (btnPrev) btnPrev.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); showSlide(currentIndex - 1); });
    if (btnNext) btnNext.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); showSlide(currentIndex + 1); });
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

