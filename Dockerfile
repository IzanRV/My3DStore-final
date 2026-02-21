# My3DStore - Despliegue en Railway (PHP + Apache)
FROM php:8.2-apache

# Extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
    libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo_mysql zip mbstring xml \
    && rm -rf /var/lib/apt/lists/*

# Virtual host por defecto: DocumentRoot public/ y acceso permitido
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
# Dejar solo un MPM (evitar "More than one MPM loaded")
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork \
    && a2enmod rewrite headers

WORKDIR /var/www/html

# Script de arranque: Apache escucha en PORT (Railway lo inyecta)
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copiar aplicación (sin .env; se inyecta por Railway)
COPY . /var/www/html/
# config/database.php está en .gitignore; en el contenedor usamos el example (solo getenv)
RUN cp /var/www/html/config/database.example.php /var/www/html/config/database.php

# Permisos para uploads y sesiones
RUN mkdir -p /var/www/html/public/images /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/public/images /var/www/html/storage 2>/dev/null || true

EXPOSE 80
CMD ["/usr/local/bin/docker-entrypoint.sh"]
