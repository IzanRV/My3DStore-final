<?php
// Script de migración para añadir campos a la base de datos

// Configuración
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'tienda_3d';

try {
    // Conectar a MySQL (sin seleccionar base de datos primero)
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Crear base de datos si no existe
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->select_db($dbname);
    
    echo "Base de datos '$dbname' verificada.\n";
    
    // Verificar si la tabla products existe
    $result = $conn->query("SHOW TABLES LIKE 'products'");
    
    if ($result->num_rows > 0) {
        echo "Tabla 'products' encontrada. Verificando columnas...\n";
        
        // Verificar y añadir columnas si no existen
        $columns = $conn->query("SHOW COLUMNS FROM products LIKE 'material'");
        if ($columns->num_rows == 0) {
            $conn->query("ALTER TABLE products ADD COLUMN material VARCHAR(100) AFTER category");
            echo "✓ Columna 'material' añadida.\n";
        } else {
            echo "✓ Columna 'material' ya existe.\n";
        }
        
        $columns = $conn->query("SHOW COLUMNS FROM products LIKE 'dimensions'");
        if ($columns->num_rows == 0) {
            $conn->query("ALTER TABLE products ADD COLUMN dimensions VARCHAR(100) AFTER material");
            echo "✓ Columna 'dimensions' añadida.\n";
        } else {
            echo "✓ Columna 'dimensions' ya existe.\n";
        }
        
        foreach (['dim_x' => "DECIMAL(10,2) NULL COMMENT 'Dimension X en mm'", 'dim_y' => "DECIMAL(10,2) NULL COMMENT 'Dimension Y en mm'", 'dim_z' => "DECIMAL(10,2) NULL COMMENT 'Dimension Z en mm'"] as $col => $def) {
            $columns = $conn->query("SHOW COLUMNS FROM products LIKE '$col'");
            if ($columns->num_rows == 0) {
                $after = $col === 'dim_x' ? 'dimensions' : ($col === 'dim_y' ? 'dim_x' : 'dim_y');
                $conn->query("ALTER TABLE products ADD COLUMN $col $def AFTER $after");
                echo "✓ Columna '$col' añadida.\n";
            } else {
                echo "✓ Columna '$col' ya existe.\n";
            }
        }
        
        // Verificar si hay productos sin material y actualizar algunos
        $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE material IS NULL OR material = ''");
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            echo "\nActualizando productos existentes con información de material...\n";
            
            // Actualizar productos con materiales por defecto
            $conn->query("UPDATE products SET material = 'PLA', dimensions = '10cm x 10cm x 10cm', dim_x = 100, dim_y = 100, dim_z = 100 WHERE (material IS NULL OR material = '') LIMIT 20");
            echo "✓ Productos actualizados.\n";
        }
        
    } else {
        echo "Tabla 'products' no encontrada. Por favor, ejecuta primero el archivo schema.sql completo.\n";
    }
    
    echo "\n¡Migración completada exitosamente!\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

