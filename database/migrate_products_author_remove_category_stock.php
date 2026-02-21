<?php
/**
 * Añade columna author a products y elimina columnas category y stock.
 * Ejecutar: php database/migrate_products_author_remove_category_stock.php
 */
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();
if (!$conn) {
    die("Error de conexión a la base de datos.\n");
}

echo "Migración: author en products, quitar category y stock\n";

// Añadir author si no existe
$r = $conn->query("SHOW COLUMNS FROM products LIKE 'author'");
if ($r && $r->num_rows === 0) {
    $ok = $conn->query("ALTER TABLE products ADD COLUMN author VARCHAR(255) NULL");
    if ($ok) {
        echo "✓ Columna author añadida.\n";
    } else {
        echo "✗ Error al añadir author: " . $conn->error . "\n";
    }
} else {
    echo "  author ya existe.\n";
}

// Eliminar category si existe
$r = $conn->query("SHOW COLUMNS FROM products LIKE 'category'");
if ($r && $r->num_rows > 0) {
    $conn->query("ALTER TABLE products DROP COLUMN category");
    echo "✓ Columna category eliminada.\n";
} else {
    echo "  category no existía.\n";
}

// Eliminar stock si existe
$r = $conn->query("SHOW COLUMNS FROM products LIKE 'stock'");
if ($r && $r->num_rows > 0) {
    $conn->query("ALTER TABLE products DROP COLUMN stock");
    echo "✓ Columna stock eliminada.\n";
} else {
    echo "  stock no existía.\n";
}

echo "Migración completada.\n";
