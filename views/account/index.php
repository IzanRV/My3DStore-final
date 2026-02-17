<?php
$pageTitle = 'Mi Cuenta - My3DStore';
include __DIR__ . '/../../includes/header.php';

$user = getUser();
if (!$user) {
    header('Location: ' . url('login'));
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
                    <span><?php echo htmlspecialchars(trim($user['name'] ?? '') ?: 'No especificado'); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars(trim($user['email'] ?? '') ?: 'No especificado'); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Teléfono:</label>
                    <span><?php echo htmlspecialchars(trim($user['phone'] ?? '') ?: 'No especificado'); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Dirección:</label>
                    <span><?php echo nl2br(htmlspecialchars(trim($user['address'] ?? '') ?: 'No especificado')); ?></span>
                </div>
            </div>
            
            <div class="account-actions">
                <a href="<?php echo url('orders'); ?>" class="btn btn-primary">Ver Mis Pedidos</a>
                <a href="<?php echo url('cart'); ?>" class="btn btn-secondary">Ver Carrito</a>
                <a href="<?php echo url('logout'); ?>" class="btn btn-secondary">Cerrar Sesión</a>
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
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.account-info h2 {
    color: var(--blue-primary);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--blue-primary);
    font-weight: 600;
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
    text-align: right;
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
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    display: inline-block;
    font-weight: 500;
    transition: background 0.2s, box-shadow 0.2s;
}

.account-actions .btn-primary {
    background: linear-gradient(135deg, #4A90E2 0%, #5BA3F5 100%);
    color: #fff;
    box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
}

.account-actions .btn-primary:hover {
    box-shadow: 0 4px 12px rgba(74, 144, 226, 0.4);
}

.account-actions .btn-secondary {
    background: #F5F5F5;
    color: #2C3E50;
}

.account-actions .btn-secondary:hover {
    background: #E0E0E0;
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
