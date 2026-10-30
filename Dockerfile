FROM php:8.2-apache

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mbstring

# Habilitar mod_rewrite para URLs limpias (opcional)
RUN a2enmod rewrite

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar código de la aplicación
COPY . /var/www/html/

# Exponer puerto Apache
EXPOSE 80

# Apache se ejecuta en foreground por defecto en la imagen oficial
CMD ["apache2-foreground"]
