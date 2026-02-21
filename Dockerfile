# My3DStore - Despliegue en Railway (PHP + Apache)
FROM php:8.2-apache

# Extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
    libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo_mysql zip mbstring xml \
    && rm -rf /var/lib/apt/lists/*

# Document root = public/ (front controller en public/index.php)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
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

# Permisos para uploads y sesiones
RUN mkdir -p /var/www/html/public/images /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/public/images /var/www/html/storage 2>/dev/null || true

EXPOSE 80
CMD ["/usr/local/bin/docker-entrypoint.sh"]
