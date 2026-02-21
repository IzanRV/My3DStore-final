<?php
$pageTitle = 'Crear Producto';
include __DIR__ . '/../../../includes/header.php';
?>

<div class="admin-page">
    <h1>Crear Producto</h1>
    
    <form method="POST" action="/My3DStore/?action=admin-product-create" class="admin-form">
        <div class="form-group">
            <label for="name">Nombre del producto:</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="description">Descripción:</label>
            <textarea id="description" name="description" rows="5" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="price">Precio (€):</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required>
        </div>
        
        <div class="form-group">
            <label for="image_url">URL de la imagen:</label>
            <input type="url" id="image_url" name="image_url">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Crear Producto</button>
            <a href="/My3DStore/?action=admin-products" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>

