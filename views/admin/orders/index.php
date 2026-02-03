<?php
require_once __DIR__ . '/../../../config/database.php';
$pageTitle = 'Gestionar Pedidos';
include __DIR__ . '/../../../includes/header.php';
?>

<div class="admin-page">
    <h1>Gestionar Pedidos</h1>
    
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6">No hay pedidos.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $db = Database::getInstance();
                    foreach ($orders as $order): 
                        $user = $db->query("SELECT name FROM users WHERE id = " . $order['user_id'])->fetch_assoc();
                    ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></td>
                            <td><?php echo formatPrice($order['total']); ?></td>
                            <td>
                                <form method="POST" action="/My3DStore/?action=admin-order-update-status" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>En proceso</option>
                                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Enviado</option>
                                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Entregado</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td class="actions">
                                <a href="/My3DStore/?action=order&id=<?php echo $order['id']; ?>" class="btn btn-small">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>

