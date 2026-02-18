<?php
/**
 * Renombra el producto "Brazo derecho de Link" (o el que use la carpeta 1471971) a "Link".
 * Así ese producto mostrará todos los modelos de public/stl/1471971/ y se verá como "Link".
 * Ejecutar una vez: php database/rename_product_to_link.php
 */
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Buscar producto por stl_url o dimensions que contengan 1471971 (carpeta de Link)
$stmt = $conn->prepare("SELECT id, name, stl_url, dimensions FROM products WHERE (stl_url LIKE ? OR dimensions LIKE ?) LIMIT 1");
$like = '%1471971%';
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Alternativa: buscar por nombre tipo "brazo derecho" y "link"
    $stmt2 = $conn->prepare("SELECT id, name FROM products WHERE (LOWER(name) LIKE ? OR LOWER(name) LIKE ?) LIMIT 1");
    $b1 = '%brazo%link%';
    $b2 = '%link%brazo%';
    $stmt2->bind_param('ss', $b1, $b2);
    $stmt2->execute();
    $result = $stmt2->get_result();
}

if ($result->num_rows === 0) {
    echo "No se encontró ningún producto con 1471971 en stl_url/dimensions ni nombre con 'brazo' y 'link'.\n";
    echo "Puedes actualizar manualmente: UPDATE products SET name = 'Link' WHERE id = <id_del_producto>;\n";
    exit(1);
}

$row = $result->fetch_assoc();
$id = (int) $row['id'];
$oldName = $row['name'];

$up = $conn->prepare("UPDATE products SET name = 'Link' WHERE id = ?");
$up->bind_param('i', $id);
$up->execute();

if ($conn->affected_rows > 0) {
    echo "Producto actualizado: \"$oldName\" -> \"Link\" (id=$id).\n";
    echo "Ese producto usará todos los modelos de la carpeta public/stl/1471971/.\n";
} else {
    echo "El producto ya se llamaba \"Link\" o no se pudo actualizar (id=$id).\n";
}
