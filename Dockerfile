FROM php:8.2-apache

# Instalar extensiones PHP necesarias y cliente de MySQL para aplicar migraciones
RUN apt-get update && apt-get install -y mysql-client && docker-php-ext-install pdo pdo_mysql mbstring

# Habilitar mod_rewrite para URLs limpias
RUN a2enmod rewrite

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar código de la aplicación
COPY . /var/www/html/

# Copiar y dar permisos al script de entrada
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Exponer puerto Apache
EXPOSE 80

# Usar script de entrada que aplica migraciones antes de iniciar Apache
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
