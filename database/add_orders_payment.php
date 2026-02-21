<?php
/**
 * Añade columnas de pago a la tabla orders (payment_method, payment_last4).
 * Ejecutar una vez: php database/add_orders_payment.php
 */
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$cols = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
if ($cols->num_rows === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(20) NOT NULL DEFAULT 'card' AFTER shipping_address");
    echo "Columna payment_method añadida.\n";
} else {
    echo "Columna payment_method ya existe.\n";
}

$cols = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_last4'");
if ($cols->num_rows === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN payment_last4 VARCHAR(4) NULL AFTER payment_method");
    echo "Columna payment_last4 añadida.\n";
} else {
    echo "Columna payment_last4 ya existe.\n";
}

echo "Listo.\n";
