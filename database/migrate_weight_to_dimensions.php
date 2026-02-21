<?php
/**
 * Migración: reemplazar columna weight por dimensiones dim_x, dim_y, dim_z (en mm).
 * Ejecutar una vez: php database/migrate_weight_to_dimensions.php
 */
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

if (!$conn) {
    die("Error: no se pudo conectar a la base de datos.\n");
}

echo "Migración: weight -> dim_x, dim_y, dim_z\n";

// Añadir dim_x si no existe
$r = $conn->query("SHOW COLUMNS FROM products LIKE 'dim_x'");
if ($r && $r->num_rows === 0) {
    $conn->query("ALTER TABLE products ADD COLUMN dim_x DECIMAL(10,2) NULL COMMENT 'Dimension X en mm' AFTER dimensions");
    echo "✓ Columna dim_x añadida.\n";
} else {
    echo "  dim_x ya existe.\n";
}

// Añadir dim_y si no existe
$r = $conn->query("SHOW COLUMNS FROM products LIKE 'dim_y'");
if ($r && $r->num_rows === 0) {
    $conn->query("ALTER TABLE products ADD COLUMN dim_y DECIMAL(10,2) NULL COMMENT 'Dimension Y en mm' AFTER dim_x");
    echo "✓ Columna dim_y añadida.\n";
} else {
    echo "  dim_y ya existe.\n";
}

// Añadir dim_z si no existe
$r = $conn->query("SHOW COLUMNS FROM products LIKE 'dim_z'");
if ($r && $r->num_rows === 0) {
    $conn->query("ALTER TABLE products ADD COLUMN dim_z DECIMAL(10,2) NULL COMMENT 'Dimension Z en mm' AFTER dim_y");
    echo "✓ Columna dim_z añadida.\n";
} else {
    echo "  dim_z ya existe.\n";
}

// Eliminar weight si existe
$r = $conn->query("SHOW COLUMNS FROM products LIKE 'weight'");
if ($r && $r->num_rows > 0) {
    $conn->query("ALTER TABLE products DROP COLUMN weight");
    echo "✓ Columna weight eliminada.\n";
} else {
    echo "  weight no existía.\n";
}

echo "Migración completada.\n";
