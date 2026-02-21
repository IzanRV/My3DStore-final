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
    stl_url VARCHAR(500),
    material VARCHAR(100),
    dimensions VARCHAR(100),
    dim_x DECIMAL(10, 2) NULL COMMENT 'Dimension X en mm',
    dim_y DECIMAL(10, 2) NULL COMMENT 'Dimension Y en mm',
    dim_z DECIMAL(10, 2) NULL COMMENT 'Dimension Z en mm',
    author VARCHAR(255) NULL COMMENT 'Nombre del autor que publica el producto',
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

-- Insertar productos de ejemplo (sin stock ni category; author NULL para ejemplos)
INSERT INTO products (name, description, price, image_url, material, dimensions, dim_x, dim_y, dim_z, author) VALUES
('Figura de Dinosaurio T-Rex', 'Figura detallada de T-Rex impresa en 3D. Perfecta para coleccionistas y amantes de los dinosaurios.', 24.99, '', 'PLA', '15cm x 10cm x 8cm', 150, 100, 80, NULL),
('Porta Lápices Personalizado', 'Porta lápices moderno con diseño único. Ideal para escritorio u oficina. Disponible en varios colores.', 12.50, '', 'PLA', '10cm x 10cm x 12cm', 100, 100, 120, NULL),
('Soporte para Smartphone', 'Soporte ergonómico para smartphone con diseño minimalista. Compatible con todos los modelos. Ángulo ajustable.', 8.99, '', 'PLA', '8cm x 5cm x 3cm', 80, 50, 30, NULL),
('Maceta Decorativa con Patrón', 'Maceta decorativa con hermosos patrones geométricos. Perfecta para plantas pequeñas.', 15.75, '', 'PLA', '12cm diámetro x 10cm altura', 120, 120, 100, NULL),
('Engranajes Educativos', 'Set de engranajes educativos para aprender mecánica básica. Incluye 5 engranajes de diferentes tamaños. Ideal para niños y educadores.', 18.00, '', 'PLA', 'Variable', 100, 100, 80, NULL),
('Caja de Almacenamiento Modular', 'Sistema de cajas modulares apilables para organización. Incluye 3 cajas de diferentes tamaños.', 22.50, '', 'PLA', '15cm x 15cm x 10cm', 150, 150, 100, NULL),
('Lámpara de Escritorio Moderna', 'Lámpara de escritorio con diseño moderno y minimalista. Base estable y brazo ajustable. LED incluido.', 29.99, '', 'PLA', 'Base: 10cm x 10cm, Altura: 35cm', 100, 100, 350, NULL),
('Juguetes de Construcción', 'Set de bloques de construcción para niños. Incluye 50 piezas de diferentes formas. Edad recomendada: 3+ años.', 19.99, '', 'PLA', 'Variable', 150, 150, 100, NULL),
('Protector de Esquinas', 'Protectores de esquinas para muebles. Pack de 8 unidades. Protege a niños y mascotas. Fácil instalación.', 6.50, '', 'TPU', '5cm x 5cm x 2cm', 50, 50, 20, NULL),
('Estatuilla Personalizada', 'Estatuilla personalizada impresa en 3D. Puedes enviarnos tu diseño o elegir de nuestro catálogo. Tamaño personalizable.', 35.00, '', 'PLA', 'Personalizable', 100, 100, 150, NULL),
('Organizador de Cables', 'Organizador de cables para escritorio. Mantén tus cables ordenados y organizados. Incluye 5 clips organizadores.', 9.99, '', 'PLA', '20cm x 5cm x 3cm', 200, 50, 30, NULL),
('Marco de Fotos 3D', 'Marco de fotos con diseño 3D único. Perfecto para regalos. Compatible con fotos estándar.', 14.50, '', 'PLA', '10cm x 15cm x 2cm', 100, 150, 20, NULL),
('Herramientas de Reparación', 'Set de herramientas de reparación impresas en 3D. Incluye destornilladores, llaves y herramientas especiales.', 16.75, '', 'PLA', 'Variable', 120, 80, 40, NULL),
('Juguetes para Mascotas', 'Juguetes interactivos para perros y gatos. Diseño seguro y resistente. Varios modelos disponibles.', 11.99, '', 'TPU', 'Variable', 80, 60, 50, NULL),
('Soporte para Auriculares', 'Soporte elegante para auriculares. Mantén tus auriculares organizados y protegidos. Diseño universal.', 7.50, '', 'PLA', '15cm x 10cm x 8cm', 150, 100, 80, NULL),
('Decoración Navideña', 'Set de decoraciones navideñas impresas en 3D. Incluye estrellas, árboles y figuras. Pack de 10 piezas.', 17.99, '', 'PLA', 'Variable', 100, 100, 80, NULL),
('Prototipo de Producto', 'Servicio de prototipado rápido. Envíanos tu diseño y lo imprimimos en 3D. Tamaño máximo: 20cm x 20cm x 20cm.', 45.00, '', 'PETG', 'Hasta 20cm x 20cm x 20cm', 200, 200, 200, NULL),
('Juguetes Educativos STEM', 'Juguetes educativos STEM (Ciencia, Tecnología, Ingeniería, Matemáticas). Incluye modelos de moléculas, planetas y más.', 21.50, '', 'PLA', 'Variable', 150, 150, 100, NULL),
('Organizador de Maquillaje', 'Organizador de maquillaje con compartimentos. Perfecto para baño o tocador.', 19.99, '', 'PLA', '25cm x 15cm x 8cm', 250, 150, 80, NULL),
('Soporte para Tablet', 'Soporte robusto para tablet. Ángulo ajustable y base estable. Compatible con tablets de 7" a 12".', 13.75, '', 'PLA', '20cm x 15cm x 5cm', 200, 150, 50, NULL);

