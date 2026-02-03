<?php
$pageTitle = 'Gestionar Productos';
include __DIR__ . '/../../../includes/header.php';
?>

<div class="admin-page">
    <div class="admin-header">
        <h1>Gestionar Productos</h1>
        <a href="/My3DStore/?action=admin-product-create" class="btn btn-primary">Crear Producto</a>
    </div>
    
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7">No hay productos.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <?php if ($product['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="" class="table-image">
                                <?php else: ?>
                                    <span class="no-image">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><?php echo htmlspecialchars($product['category'] ?? '-'); ?></td>
                            <td class="actions">
                                <a href="/My3DStore/?action=admin-product-edit&id=<?php echo $product['id']; ?>" class="btn btn-small">Editar</a>
                                <form method="POST" action="/My3DStore/?action=admin-product-delete" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('¿Estás seguro?');">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>

