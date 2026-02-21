-- Añadir columnas color (hex), logo_url y logo_side a la tabla products.
-- Ejecutar en la base de datos tienda_3d (o la que uses).

USE tienda_3d;

-- Color del producto en hexadecimal (ej: #003d7a)
ALTER TABLE products ADD COLUMN color VARCHAR(20) NULL COMMENT 'Color en hexadecimal' AFTER author;

-- URL o ruta del logo (ej: images/logos/logo_1234_abc.png)
ALTER TABLE products ADD COLUMN logo_url VARCHAR(500) NULL COMMENT 'Ruta del logo del producto' AFTER color;

-- Lado donde se muestra el logo: front, back, left, right, top, bottom
ALTER TABLE products ADD COLUMN logo_side VARCHAR(20) NULL COMMENT 'Lado del logo: front, back, left, right, top, bottom' AFTER logo_url;
