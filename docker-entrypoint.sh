#!/bin/bash
set -e

echo "=== Entrypoint: iniciando sistema de restaurante ==="

# Puerto para Railway
HTTP_PORT="${PORT:-8080}"
if ! [[ "$HTTP_PORT" =~ ^[0-9]+$ ]] || [ "$HTTP_PORT" -lt 1 ] || [ "$HTTP_PORT" -gt 65535 ]; then
    HTTP_PORT=8080
fi

# Crear configuración de Apache con el puerto correcto
echo "Listen ${HTTP_PORT}" > /etc/apache2/ports.conf
cat > /etc/apache2/sites-available/000-default.conf << EOF
<VirtualHost *:${HTTP_PORT}>
    DocumentRoot /var/www/html/public
    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Habilitar mod_rewrite y prefork MPM
a2enmod rewrite 2>/dev/null || true
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Intentar migraciones si MySQL está disponible (no bloquear)
if [ -n "${DB_HOST}" ] && [ -n "${DB_USER}" ]; then
    echo "Verificando base de datos en ${DB_HOST}:${DB_PORT:-3306}..."
    if mysqladmin ping -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl --silent 2>/dev/null; then
        echo "Base de datos lista. Aplicando migraciones..."
        MIGRATIONS_DIR="/var/www/html/migrations"
        if [ -d "$MIGRATIONS_DIR" ]; then
            for migration in "$MIGRATIONS_DIR"/0*.sql; do
                if [ -f "$migration" ]; then
                    mysql -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl "${DB_NAME}" < "$migration" 2>/dev/null || true
                fi
            done
            echo "Migraciones procesadas."
        fi
    else
        echo "Base de datos no disponible, continuando sin migraciones."
    fi
fi

echo "Iniciando Apache en el puerto ${HTTP_PORT}..."
exec apache2-foreground
