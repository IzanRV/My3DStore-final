<?php
$pageTitle = 'Mi Cuenta - My3DStore';
include __DIR__ . '/../../includes/header.php';

$user = getUser();
if (!$user) {
    header('Location: /My3DStore/?action=login');
    exit;
}
?>

<div class="account-page">
    <h1>Mi Cuenta</h1>
    
    <div class="account-container">
        <div class="account-info">
            <h2>Información Personal</h2>
            
            <div class="info-section">
                <div class="info-item">
                    <label>Nombre:</label>
                    <span><?php echo htmlspecialchars($user['name'] ?? 'No especificado'); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                
                <?php if (!empty($user['phone'])): ?>
                <div class="info-item">
                    <label>Teléfono:</label>
                    <span><?php echo htmlspecialchars($user['phone']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($user['address'])): ?>
                <div class="info-item">
                    <label>Dirección:</label>
                    <span><?php echo nl2br(htmlspecialchars($user['address'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="account-actions">
                <a href="/My3DStore/?action=orders" class="btn btn-primary">Ver Mis Pedidos</a>
                <a href="/My3DStore/?action=cart" class="btn btn-secondary">Ver Carrito</a>
                <a href="/My3DStore/?action=logout" class="btn btn-secondary">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</div>

<style>
.account-page {
    padding: 2rem;
    max-width: 800px;
    margin: 0 auto;
}

.account-page h1 {
    margin-bottom: 2rem;
    color: var(--text-dark);
}

.account-container {
    background: var(--white);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.account-info h2 {
    color: var(--blue-primary);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--blue-lighter);
}

.info-section {
    margin-bottom: 2rem;
}

.info-item {
    display: flex;
    padding: 1rem 0;
    border-bottom: 1px solid var(--gray-medium);
}

.info-item:last-child {
    border-bottom: none;
}

.info-item label {
    font-weight: 600;
    color: var(--text-dark);
    min-width: 120px;
    margin-right: 1rem;
}

.info-item span {
    color: var(--text-light);
    flex: 1;
}

.account-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.account-actions .btn {
    flex: 1;
    min-width: 150px;
    text-align: center;
}

@media (max-width: 768px) {
    .account-page {
        padding: 1rem;
    }
    
    .info-item {
        flex-direction: column;
    }
    
    .info-item label {
        margin-bottom: 0.5rem;
    }
    
    .account-actions {
        flex-direction: column;
    }
    
    .account-actions .btn {
        width: 100%;
    }
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
