FROM php:8.2-apache

# Instalamos dependencias para PostgreSQL y las extensiones de PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql pdo_pgsql pgsql

# Configuramos Apache para que su carpeta principal sea /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/apache2!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilitamos el modo de reescritura
RUN a2enmod rewrite

# Copiamos todos tus archivos al servidor
COPY . /var/www/html/

# Aseguramos permisos
RUN chown -R www-data:www-data /var/www/html
