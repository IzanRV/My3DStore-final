-- Base de datos para tienda de impresión 3D
CREATE DATABASE IF NOT EXISTS tienda_3d;
USE tienda_3d;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(50),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de productos
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(500),
    stock INT DEFAULT 0,
    category VARCHAR(100),
    material VARCHAR(100),
    dimensions VARCHAR(100),
    weight VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de items del carrito
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de pedidos
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de items de pedido
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de reseñas
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (product_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar usuario administrador por defecto (password: admin123)
INSERT INTO users (email, password, name, role) VALUES 
('admin@tienda3d.com', '$2y$10$hLAOQzjCk2oMNl3EZxFGvuin.ykJUzDZwwkgsLAu9Z.dnyoTG4.mW', 'Administrador', 'admin');

-- Insertar productos de ejemplo
INSERT INTO products (name, description, price, image_url, stock, category, material, dimensions, weight) VALUES
('Figura de Dinosaurio T-Rex', 'Figura detallada de T-Rex impresa en 3D. Perfecta para coleccionistas y amantes de los dinosaurios.', 24.99, '', 15, 'Figuras', 'PLA', '15cm x 10cm x 8cm', '150g'),
('Porta Lápices Personalizado', 'Porta lápices moderno con diseño único. Ideal para escritorio u oficina. Disponible en varios colores.', 12.50, '', 25, 'Organizadores', 'PLA', '10cm x 10cm x 12cm', '80g'),
('Soporte para Smartphone', 'Soporte ergonómico para smartphone con diseño minimalista. Compatible con todos los modelos. Ángulo ajustable.', 8.99, '', 30, 'Accesorios', 'PLA', '8cm x 5cm x 3cm', '50g'),
('Maceta Decorativa con Patrón', 'Maceta decorativa con hermosos patrones geométricos. Perfecta para plantas pequeñas.', 15.75, '', 20, 'Decoración', 'PLA', '12cm diámetro x 10cm altura', '120g'),
('Engranajes Educativos', 'Set de engranajes educativos para aprender mecánica básica. Incluye 5 engranajes de diferentes tamaños. Ideal para niños y educadores.', 18.00, '', 18, 'Educativo', 'PLA', 'Variable', '200g'),
('Caja de Almacenamiento Modular', 'Sistema de cajas modulares apilables para organización. Incluye 3 cajas de diferentes tamaños.', 22.50, '', 12, 'Organizadores', 'PLA', '15cm x 15cm x 10cm', '180g'),
('Lámpara de Escritorio Moderna', 'Lámpara de escritorio con diseño moderno y minimalista. Base estable y brazo ajustable. LED incluido.', 29.99, '', 10, 'Iluminación', 'PLA', 'Base: 10cm x 10cm, Altura: 35cm', '300g'),
('Juguetes de Construcción', 'Set de bloques de construcción para niños. Incluye 50 piezas de diferentes formas. Edad recomendada: 3+ años.', 19.99, '', 22, 'Juguetes', 'PLA', 'Variable', '250g'),
('Protector de Esquinas', 'Protectores de esquinas para muebles. Pack de 8 unidades. Protege a niños y mascotas. Fácil instalación.', 6.50, '', 40, 'Seguridad', 'TPU', '5cm x 5cm x 2cm', '30g'),
('Estatuilla Personalizada', 'Estatuilla personalizada impresa en 3D. Puedes enviarnos tu diseño o elegir de nuestro catálogo. Tamaño personalizable.', 35.00, '', 5, 'Personalizado', 'PLA', 'Personalizable', 'Variable'),
('Organizador de Cables', 'Organizador de cables para escritorio. Mantén tus cables ordenados y organizados. Incluye 5 clips organizadores.', 9.99, '', 28, 'Organizadores', 'PLA', '20cm x 5cm x 3cm', '60g'),
('Marco de Fotos 3D', 'Marco de fotos con diseño 3D único. Perfecto para regalos. Compatible con fotos estándar.', 14.50, '', 15, 'Decoración', 'PLA', '10cm x 15cm x 2cm', '100g'),
('Herramientas de Reparación', 'Set de herramientas de reparación impresas en 3D. Incluye destornilladores, llaves y herramientas especiales.', 16.75, '', 14, 'Herramientas', 'PLA', 'Variable', '150g'),
('Juguetes para Mascotas', 'Juguetes interactivos para perros y gatos. Diseño seguro y resistente. Varios modelos disponibles.', 11.99, '', 20, 'Mascotas', 'TPU', 'Variable', '80g'),
('Soporte para Auriculares', 'Soporte elegante para auriculares. Mantén tus auriculares organizados y protegidos. Diseño universal.', 7.50, '', 35, 'Accesorios', 'PLA', '15cm x 10cm x 8cm', '70g'),
('Decoración Navideña', 'Set de decoraciones navideñas impresas en 3D. Incluye estrellas, árboles y figuras. Pack de 10 piezas.', 17.99, '', 25, 'Decoración', 'PLA', 'Variable', '180g'),
('Prototipo de Producto', 'Servicio de prototipado rápido. Envíanos tu diseño y lo imprimimos en 3D. Tamaño máximo: 20cm x 20cm x 20cm.', 45.00, '', 3, 'Servicios', 'PETG', 'Hasta 20cm x 20cm x 20cm', 'Variable'),
('Juguetes Educativos STEM', 'Juguetes educativos STEM (Ciencia, Tecnología, Ingeniería, Matemáticas). Incluye modelos de moléculas, planetas y más.', 21.50, '', 16, 'Educativo', 'PLA', 'Variable', '220g'),
('Organizador de Maquillaje', 'Organizador de maquillaje con compartimentos. Perfecto para baño o tocador.', 19.99, '', 18, 'Organizadores', 'PLA', '25cm x 15cm x 8cm', '200g'),
('Soporte para Tablet', 'Soporte robusto para tablet. Ángulo ajustable y base estable. Compatible con tablets de 7" a 12".', 13.75, '', 20, 'Accesorios', 'PLA', '20cm x 15cm x 5cm', '150g');

