<?php
/**
 * Elimina todos los productos y sus referencias (cart_items, order_items, reviews).
 * Ejecutar una vez: php database/clear_products.php
 */
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Orden: eliminar referencias primero por si no hay ON DELETE CASCADE
$conn->query("DELETE FROM cart_items");
$conn->query("DELETE FROM order_items");
$conn->query("DELETE FROM orders");
$conn->query("DELETE FROM reviews");
$conn->query("DELETE FROM products");

echo "OK: Productos, pedidos y referencias (cart_items, order_items, orders, reviews, products) eliminados.\n";
