<?php
$pageTitle = $product['name'];
$loadStatic3D = true; // Cargar modelos 3D estáticos
include __DIR__ . '/../../includes/header.php';
?>

<div class="product-detail">
    <div class="product-detail-container">
        <div class="product-images">
            <div id="product-detail-3d" class="static-3d-viewer" style="width: 100%; height: 500px;"></div>
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
                <?php if (!empty($product['dimensions'])): ?>
                    <div class="spec-item">
                        <span><strong>Dimensiones:</strong></span>
                        <span><?php echo htmlspecialchars($product['dimensions']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($product['weight'])): ?>
                    <div class="spec-item">
                        <span><strong>Peso:</strong></span>
                        <span><?php echo htmlspecialchars($product['weight']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($product['material'])): ?>
                    <div class="spec-item">
                        <span><strong>Material:</strong></span>
                        <span><?php echo htmlspecialchars($product['material']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="product-stock">
                <?php if ($product['stock'] > 0): ?>
                    <p class="stock-available">✓ En stock (<?php echo $product['stock']; ?> disponibles)</p>
                <?php else: ?>
                    <p class="stock-unavailable">✗ Agotado</p>
                <?php endif; ?>
            </div>
            
            <?php if (isLoggedIn() && $product['stock'] > 0): ?>
                <div class="product-actions-detail">
                    <a href="/My3DStore/?action=checkout&product_id=<?php echo $product['id']; ?>&quantity=1" class="btn btn-primary btn-large">Compra ya</a>
                    <form method="POST" action="/My3DStore/?action=cart-add" style="flex: 1;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-secondary btn-large" style="width: 100%;">Añadir a la cesta</button>
                    </form>
                </div>
            <?php elseif (!isLoggedIn()): ?>
                <div class="product-actions-detail">
                    <a href="/My3DStore/?action=login" class="btn btn-primary btn-large">Compra ya</a>
                    <a href="/My3DStore/?action=login" class="btn btn-secondary btn-large">Añadir a la cesta</a>
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
    initStatic3DViewers();
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

