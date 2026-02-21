<?php
/**
 * Asigna dimensiones (dim_x, dim_y, dim_z) a productos que las tengan NULL.
 * Valores en mm. Ejecutar: php database/set_products_dimensions.php
 */
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

if (!$conn) {
    die("Error: no se pudo conectar a la base de datos.\n");
}

// Productos que tienen dim_x NULL: asignar dimensiones por defecto
$result = $conn->query("SELECT id, name, dimensions FROM products WHERE dim_x IS NULL");
if (!$result || $result->num_rows === 0) {
    echo "No hay productos sin dimensiones, o la tabla no tiene datos.\n";
    exit(0);
}

$defaults = [80, 80, 80]; // mm por defecto
$updated = 0;
$stmt = $conn->prepare("UPDATE products SET dim_x = ?, dim_y = ?, dim_z = ? WHERE id = ?");

while ($row = $result->fetch_assoc()) {
    $dim = $row['dimensions'];
    $x = $y = $z = null;
    // Intentar extraer números en cm (ej. "15cm x 10cm x 8cm" o "10 x 10 x 12")
    if ($dim && preg_match('/[\d.,]+\s*(?:cm|mm)?\s*[x×]\s*[\d.,]+\s*(?:cm|mm)?\s*[x×]\s*[\d.,]+/i', $dim)) {
        if (preg_match_all('/(\d+(?:[.,]\d+)?)/', $dim, $m) && count($m[1]) >= 3) {
            $x = (float) str_replace(',', '.', $m[1][0]);
            $y = (float) str_replace(',', '.', $m[1][1]);
            $z = (float) str_replace(',', '.', $m[1][2]);
            if (stripos($dim, 'cm') !== false) {
                $x *= 10; $y *= 10; $z *= 10;
            }
        }
    }
    if ($x === null || $x <= 0) {
        $x = $defaults[0];
        $y = $defaults[1];
        $z = $defaults[2];
    } elseif ($y === null || $y <= 0) {
        $y = $x;
        $z = $x;
    } elseif ($z === null || $z <= 0) {
        $z = $y;
    }
    $stmt->bind_param("dddi", $x, $y, $z, $row['id']);
    $stmt->execute();
    $updated++;
    echo "  ID {$row['id']} ({$row['name']}): {$x} × {$y} × {$z} mm\n";
}

$stmt->close();
echo "✓ Dimensiones asignadas a $updated producto(s).\n";
