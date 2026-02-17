FROM php:8.2-apache

# Instalar dependencias del sistema (incl. para GD)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    git \
    unzip \
    python3 \
    python3-pip \
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar GD (sintaxis válida para PHP 8.2)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd

# Resto de extensiones PHP (curl no se instala así; ya viene en la imagen)
RUN docker-php-ext-install -j$(nproc) \
    mysqli \
    pdo_mysql \
    zip \
    mbstring \
    xml

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Configurar Apache
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copiar configuración de Apache personalizada
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de la aplicación
COPY . /var/www/html/

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/public/glb/generated \
    && mkdir -p /var/www/html/public/stl/generated \
    && chown -R www-data:www-data /var/www/html/public/glb/generated \
    && chown -R www-data:www-data /var/www/html/public/stl/generated

# Exponer puerto 80
EXPOSE 80

# Comando por defecto
CMD ["apache2-foreground"]
