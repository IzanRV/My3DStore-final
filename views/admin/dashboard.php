<?php
$pageTitle = 'Panel de Administración';
include __DIR__ . '/../../../includes/header.php';
?>

<div class="admin-page">
    <h1>Panel de Administración</h1>
    
    <div class="admin-stats">
        <div class="stat-card">
            <h3>Productos</h3>
            <p class="stat-number"><?php echo $totalProducts; ?></p>
            <a href="<?php echo htmlspecialchars(url('admin-products')); ?>" class="stat-link">Gestionar</a>
        </div>
        <div class="stat-card">
            <h3>Pedidos</h3>
            <p class="stat-number"><?php echo $totalOrders; ?></p>
            <a href="<?php echo htmlspecialchars(url('admin-orders')); ?>" class="stat-link">Gestionar</a>
        </div>
        <div class="stat-card">
            <h3>Usuarios</h3>
            <p class="stat-number"><?php echo $totalUsers; ?></p>
            <a href="<?php echo htmlspecialchars(url('admin-users')); ?>" class="stat-link">Ver</a>
        </div>
        <div class="stat-card">
            <h3>Ingresos</h3>
            <p class="stat-number"><?php echo formatPrice($totalRevenue); ?></p>
        </div>
    </div>
    
    <div class="admin-links">
        <a href="<?php echo htmlspecialchars(url('admin-products')); ?>" class="btn btn-primary">Gestionar Productos</a>
        <a href="<?php echo htmlspecialchars(url('admin-orders')); ?>" class="btn btn-primary">Gestionar Pedidos</a>
        <a href="<?php echo htmlspecialchars(url('admin-users')); ?>" class="btn btn-primary">Ver Usuarios</a>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>

