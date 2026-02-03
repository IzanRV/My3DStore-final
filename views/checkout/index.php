<?php
$pageTitle = 'Checkout';
include __DIR__ . '/../../includes/header.php';
?>

<div class="checkout-page">
    <h1>Finalizar Pedido</h1>
    
    <div class="checkout-container">
        <div class="checkout-items">
            <h2>Resumen del Pedido</h2>
            <?php foreach ($items as $item): ?>
                <div class="checkout-item">
                    <div class="checkout-item-image">
                        <?php if ($item['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="checkout-item-info">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Cantidad: <?php echo $item['quantity']; ?></p>
                        <p>Precio: <?php echo formatPrice($item['price']); ?></p>
                    </div>
                    <div class="checkout-item-total">
                        <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="checkout-total">
                <p><strong>Total: <?php echo formatPrice($total); ?></strong></p>
            </div>
        </div>
        
        <div class="checkout-form-container">
            <h2>Dirección de Envío</h2>
            <form method="POST" action="/My3DStore/?action=checkout" class="checkout-form">
                <div class="form-group">
                    <label for="shipping_address">Dirección completa:</label>
                    <textarea id="shipping_address" name="shipping_address" rows="4" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-large btn-block">Confirmar Pedido</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

