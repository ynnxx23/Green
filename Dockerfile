FROM php:8.2-apache

# Instalar extensiones PHP necesarias y cliente de MySQL para aplicar migraciones
RUN apt-get update && apt-get install -y mariadb-client libonig-dev && docker-php-ext-install pdo pdo_mysql mbstring

# Habilitar solo MPM prefork (PHP no es thread-safe) y mod_rewrite
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork \
    && a2enmod rewrite \
    && a2enmod headers

# Configurar Apache para servir archivos estáticos con MIME types correctos
# Esto evita que los archivos .js y .css se sirvan como text/html en Railway
RUN echo 'AddType application/javascript .js' >> /etc/apache2/apache2.conf \
    && echo 'AddType text/css .css' >> /etc/apache2/apache2.conf \
    && echo 'AddType image/png .png' >> /etc/apache2/apache2.conf \
    && echo 'AddType image/jpeg .jpg .jpeg' >> /etc/apache2/apache2.conf \
    && echo 'AddType image/gif .gif' >> /etc/apache2/apache2.conf \
    && echo 'AddType image/svg+xml .svg' >> /etc/apache2/apache2.conf \
    && echo 'AddType image/webp .webp' >> /etc/apache2/apache2.conf \
    && echo 'AddType font/woff .woff' >> /etc/apache2/apache2.conf \
    && echo 'AddType font/woff2 .woff2' >> /etc/apache2/apache2.conf \
    && echo 'AddType font/ttf .ttf' >> /etc/apache2/apache2.conf \
    && echo 'AddType font/eot .eot' >> /etc/apache2/apache2.conf

# Crear configuración de VirtualHost que sirve archivos estáticos directamente
RUN cat > /etc/apache2/sites-available/000-default.conf << 'EOF'
<VirtualHost *:80>
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Servir archivos estáticos directamente sin pasar por el front controller
    <LocationMatch \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|webp)$>
        Require all granted
    </LocationMatch>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar código de la aplicación
COPY . /var/www/html/

# Copiar y dar permisos al script de entrada
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Exponer puerto Apache
EXPOSE 80

# Forzar rebuild para aplicar correcciones de transacciones y MIME types
RUN echo "Rebuild v2 - RateLimiter fix + static files first" > /tmp/rebuild-marker

# Usar script de entrada que aplica migraciones antes de iniciar Apache
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
