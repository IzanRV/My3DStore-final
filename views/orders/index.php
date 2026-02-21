<?php
$pageTitle = 'Mis Pedidos';
include __DIR__ . '/../../includes/header.php';
?>

<div class="orders-page">
    <h1>Mis Pedidos</h1>
    
    <?php if (empty($orders)): ?>
        <div class="no-orders">
            <p>No tienes pedidos aún.</p>
            <a href="<?php echo htmlspecialchars(url('products')); ?>" class="btn btn-primary">Ver Productos</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Pedido #<?php echo $order['id']; ?></h3>
                            <p class="order-date">Fecha: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="order-status">
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
                    </div>
                    <div class="order-details">
                        <p><strong>Total:</strong> <?php echo formatPrice($order['total']); ?></p>
                        <p><strong>Dirección:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    </div>
                    <div class="order-actions">
                        <a href="<?php echo htmlspecialchars(url('order', ['id' => $order['id']])); ?>" class="btn btn-primary">Ver Detalles</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

