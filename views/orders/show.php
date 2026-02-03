<?php
$pageTitle = 'Detalle del Pedido #' . $order['id'];
include __DIR__ . '/../../includes/header.php';
?>

<div class="order-detail-page">
    <h1>Pedido #<?php echo $order['id']; ?></h1>
    
    <div class="order-detail-container">
        <div class="order-info-section">
            <h2>Información del Pedido</h2>
            <div class="info-row">
                <strong>Fecha:</strong>
                <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
            </div>
            <div class="info-row">
                <strong>Estado:</strong>
                <span class="status-badge status-<?php echo $order['status']; ?>">
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
            <div class="info-row">
                <strong>Dirección de envío:</strong>
                <span><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></span>
            </div>
        </div>
        
        <div class="order-items-section">
            <h2>Productos</h2>
            <div class="order-items-list">
                <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <div class="order-item-image">
                            <?php if ($item['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="order-item-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p>Cantidad: <?php echo $item['quantity']; ?></p>
                            <p>Precio unitario: <?php echo formatPrice($item['price']); ?></p>
                        </div>
                        <div class="order-item-total">
                            <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-total">
                <p><strong>Total: <?php echo formatPrice($order['total']); ?></strong></p>
            </div>
        </div>
    </div>
    
    <div class="order-actions">
        <a href="/My3DStore/?action=orders" class="btn btn-secondary">Volver a Pedidos</a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

