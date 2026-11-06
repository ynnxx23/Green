#!/bin/bash
set -e

HTTP_PORT="${PORT:-8080}"

# Configurar Apache para escuchar en el puerto de Railway
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

a2enmod rewrite 2>/dev/null || true
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Intentar migraciones (no bloquear)
if [ -n "${DB_HOST}" ] && [ -n "${DB_USER}" ]; then
    MIGRATIONS_DIR="/var/www/html/migrations"
    if [ -d "$MIGRATIONS_DIR" ]; then
        for migration in "$MIGRATIONS_DIR"/0*.sql; do
            [ -f "$migration" ] && mysql -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl "${DB_NAME}" < "$migration" 2>/dev/null || true
        done
    fi
fi

echo "Apache iniciado en puerto ${HTTP_PORT}"
exec apache2-foreground
