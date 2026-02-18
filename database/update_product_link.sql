-- Actualiza el producto "Brazo derecho de Link" (id 53) para que:
-- - Se llame "Link"
-- - Tenga la imagen de Link (carrusel mostrará esta imagen primero)
-- - stl_url contenga 1471971 para que el carrusel cargue todos los modelos de public/stl/1471971/
-- Ejecutar: mysql -u usuario -p tienda_3d < database/update_product_link.sql
-- O copiar y pegar en phpMyAdmin / cliente MySQL.

UPDATE products
SET
  name = 'Link',
  image_url = 'images/printables_1471971_1771330083.png',
  stl_url = 'stl/printables_1471971_Linkright_arm.stl'
WHERE id = 53;

-- Si no conoces el id, usa el nombre:
-- UPDATE products
-- SET
--   name = 'Link',
--   image_url = 'images/printables_1471971_1771330083.png',
--   stl_url = 'stl/printables_1471971_Linkright_arm.stl'
-- WHERE name LIKE '%Brazo derecho%Link%' OR name LIKE '%Link%brazo%';
