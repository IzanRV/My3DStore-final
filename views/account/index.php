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
            
            <form method="POST" action="<?php echo htmlspecialchars(url('account-update')); ?>" id="accountForm">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                <div class="info-section">
                    <div class="info-item">
                        <label>Nombre:</label>
                        <span><?php echo htmlspecialchars(trim($user['name'] ?? '') ?: 'No especificado'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars(trim($user['email'] ?? '') ?: 'No especificado'); ?></span>
                    </div>
                    
                    <div class="info-item" id="phone-row">
                        <label>Teléfono:</label>
                        <div class="info-value-wrap">
                            <span class="info-display" id="phone-display"><?php echo htmlspecialchars(trim($user['phone'] ?? '')); ?></span>
                            <span class="info-display info-empty" id="phone-empty" style="<?php echo trim($user['phone'] ?? '') !== '' ? 'display:none' : ''; ?>">—</span>
                            <button type="button" class="btn-add" id="phone-add-btn" data-field="phone" style="<?php echo trim($user['phone'] ?? '') !== '' ? 'display:none' : ''; ?>">Añadir</button>
                            <button type="button" class="btn-edit" id="phone-edit-btn" data-field="phone" style="<?php echo trim($user['phone'] ?? '') === '' ? 'display:none' : ''; ?>">Editar</button>
                            <div class="info-edit-wrap" id="phone-edit-wrap" style="display:none;">
                                <input type="tel" name="phone" id="phone-input" class="info-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Ej: +34 612 345 678" maxlength="50">
                                <button type="submit" class="btn-save">Guardar</button>
                                <button type="button" class="btn-cancel" data-field="phone">Cancelar</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item" id="address-row">
                        <label>Dirección:</label>
                        <div class="info-value-wrap">
                            <span class="info-display" id="address-display" style="<?php echo trim($user['address'] ?? '') === '' ? 'display:none' : ''; ?>"><?php echo nl2br(htmlspecialchars($user['address'] ?? '')); ?></span>
                            <span class="info-display info-empty" id="address-empty" style="<?php echo trim($user['address'] ?? '') !== '' ? 'display:none' : ''; ?>">—</span>
                            <button type="button" class="btn-add" id="address-add-btn" data-field="address" style="<?php echo trim($user['address'] ?? '') !== '' ? 'display:none' : ''; ?>">Añadir</button>
                            <button type="button" class="btn-edit" id="address-edit-btn" data-field="address" style="<?php echo trim($user['address'] ?? '') === '' ? 'display:none' : ''; ?>">Editar</button>
                            <div class="info-edit-wrap" id="address-edit-wrap" style="display:none;">
                                <textarea name="address" id="address-input" class="info-input info-textarea" placeholder="Calle, número, código postal, ciudad" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                <button type="submit" class="btn-save">Guardar</button>
                                <button type="button" class="btn-cancel" data-field="address">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
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

.info-value-wrap {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.info-display {
    color: var(--text-light);
}
.info-display.info-empty {
    color: var(--gray-medium);
}

.btn-add, .btn-edit {
    padding: 0.35rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 8px;
    cursor: pointer;
    border: 1px solid var(--blue-primary);
    background: #fff;
    color: var(--blue-primary);
    font-weight: 500;
}
.btn-add:hover, .btn-edit:hover {
    background: var(--blue-primary);
    color: #fff;
}

.info-edit-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    flex: 1;
    justify-content: flex-end;
}

.info-input {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--gray-medium);
    border-radius: 8px;
    font-size: 0.9rem;
    min-width: 180px;
}
.info-textarea {
    min-width: 240px;
    resize: vertical;
}

.btn-save {
    padding: 0.35rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    background: var(--blue-primary);
    color: #fff;
    font-weight: 500;
}
.btn-save:hover {
    opacity: 0.9;
}

.btn-cancel {
    padding: 0.35rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 8px;
    cursor: pointer;
    border: 1px solid var(--gray-medium);
    background: #f5f5f5;
    color: var(--text-dark);
}
.btn-cancel:hover {
    background: #eee;
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

<script>
(function() {
    var form = document.getElementById('accountForm');
    if (!form) return;

    function showEdit(field) {
        var wrap = document.getElementById(field + '-edit-wrap');
        var display = document.getElementById(field + '-display');
        var empty = document.getElementById(field + '-empty');
        var addBtn = document.getElementById(field + '-add-btn');
        var editBtn = document.getElementById(field + '-edit-btn');
        var input = document.getElementById(field + '-input');
        if (!wrap || !input) return;
        wrap.style.display = 'flex';
        if (display) display.style.display = 'none';
        if (empty) empty.style.display = 'none';
        if (addBtn) addBtn.style.display = 'none';
        if (editBtn) editBtn.style.display = 'none';
        input.focus();
    }

    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function hideEdit(field) {
        var wrap = document.getElementById(field + '-edit-wrap');
        var display = document.getElementById(field + '-display');
        var empty = document.getElementById(field + '-empty');
        var addBtn = document.getElementById(field + '-add-btn');
        var editBtn = document.getElementById(field + '-edit-btn');
        var input = document.getElementById(field + '-input');
        if (!wrap || !input) return;
        wrap.style.display = 'none';
        var val = (input.value || '').trim();
        if (field === 'address') {
            if (display) {
                display.innerHTML = val ? escapeHtml(val).replace(/\n/g, '<br>') : '';
                display.style.display = val ? '' : 'none';
            }
            if (empty) empty.style.display = val ? 'none' : '';
            if (addBtn) addBtn.style.display = val ? 'none' : '';
            if (editBtn) editBtn.style.display = val ? '' : 'none';
        } else {
            if (display) {
                display.textContent = val || '';
                display.style.display = val ? '' : 'none';
            }
            if (empty) empty.style.display = val ? 'none' : '';
            if (addBtn) addBtn.style.display = val ? 'none' : '';
            if (editBtn) editBtn.style.display = val ? '' : 'none';
        }
    }

    ['phone', 'address'].forEach(function(field) {
        var addBtn = document.getElementById(field + '-add-btn');
        var editBtn = document.getElementById(field + '-edit-btn');
        var cancelBtn = form.querySelector('.btn-cancel[data-field="' + field + '"]');
        if (addBtn) addBtn.addEventListener('click', function() { showEdit(field); });
        if (editBtn) editBtn.addEventListener('click', function() { showEdit(field); });
        if (cancelBtn) cancelBtn.addEventListener('click', function() { hideEdit(field); });
    });
})();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
