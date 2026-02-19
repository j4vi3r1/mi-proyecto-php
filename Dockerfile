FROM php:8.2-apache

# Instalamos extensiones para base de datos
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Configuramos Apache para que su carpeta principal sea /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/apache2!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilitamos el modo de reescritura (útil para frameworks o rutas limpias)
RUN a2enmod rewrite

# Copiamos todos tus archivos al servidor
COPY . /var/www/html/

# Damos permisos para que PHP pueda escribir si es necesario
RUN chown -R www-data:www-data /var/www/html