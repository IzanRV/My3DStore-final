<?php
/**
 * Añade columna stl_url a products, la rellena desde dimensions,
 * y asigna material cada 5 productos: PLA, Madera, Metal, Cerámica.
 * Ejecutar una vez: php database/add_stl_url_and_materials.php
 */
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// 1. Añadir columna stl_url si no existe
$check = $conn->query("SHOW COLUMNS FROM products LIKE 'stl_url'");
if ($check->num_rows === 0) {
    $conn->query("ALTER TABLE products ADD COLUMN stl_url VARCHAR(500) NULL AFTER image_url");
    echo "Columna stl_url añadida.\n";
}

// 2. Rellenar stl_url desde dimensions (nombre de archivo -> ruta stl/xxx.stl)
$conn->query("UPDATE products SET stl_url = CONCAT('stl/', dimensions) WHERE dimensions IS NOT NULL AND dimensions != '' AND (dimensions LIKE '%.stl' OR dimensions LIKE '%.glb') AND (stl_url IS NULL OR stl_url = '')");
echo "stl_url actualizado desde dimensions.\n";

// 3. Asignar material cada 5 productos (orden por id)
$materials = ['PLA', 'Madera', 'Metal', 'Cerámica'];
$result = $conn->query("SELECT id FROM products ORDER BY id ASC");
$index = 0;
while ($row = $result->fetch_assoc()) {
    $material = $materials[$index % 4];
    $id = (int) $row['id'];
    $stmt = $conn->prepare("UPDATE products SET material = ? WHERE id = ?");
    $stmt->bind_param("si", $material, $id);
    $stmt->execute();
    $index++;
}
echo "Material asignado a " . $index . " productos (cada 5: PLA, Madera, Metal, Cerámica).\n";

echo "OK.\n";
