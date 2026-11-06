#!/bin/bash
set -e

echo "=== Entrypoint: iniciando sistema de restaurante ==="

# Puerto para Railway
HTTP_PORT="${PORT:-8080}"
if ! [[ "$HTTP_PORT" =~ ^[0-9]+$ ]] || [ "$HTTP_PORT" -lt 1 ] || [ "$HTTP_PORT" -gt 65535 ]; then
    HTTP_PORT=8080
fi

# Configurar Apache para escuchar en el puerto correcto
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

# Esperar a MySQL y aplicar migraciones (reintentos durante 60 segundos)
if [ -n "${DB_HOST}" ] && [ -n "${DB_USER}" ]; then
    echo "Esperando a MySQL en ${DB_HOST}:${DB_PORT:-3306} (máx. 60 segundos)..."
    for i in $(seq 1 30); do
        if mysqladmin ping -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl --silent 2>/dev/null; then
            echo "MySQL lista. Aplicando migraciones..."
            MIGRATIONS_DIR="/var/www/html/migrations"
            if [ -d "$MIGRATIONS_DIR" ]; then
                for migration in "$MIGRATIONS_DIR"/0*.sql; do
                    if [ -f "$migration" ]; then
                        echo "  Aplicando: $(basename "$migration")"
                        mysql -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl "${DB_NAME}" < "$migration" 2>/dev/null || true
                    fi
                done
                echo "  Migraciones aplicadas."
            fi
            break
        fi
        echo "  Intento $i/30: MySQL no disponible todavía..."
        sleep 2
    done
else
    echo "Advertencia: variables de BD no definidas, saltando migraciones."
fi

echo "Iniciando Apache en el puerto ${HTTP_PORT}..."
exec apache2-foreground
