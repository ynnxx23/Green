#!/bin/bash

echo "=== Entrypoint: iniciando sistema de restaurante ==="

# Configurar puerto para Railway (variable PORT asignada automáticamente)
HTTP_PORT="${PORT:-80}"
if [ -z "$HTTP_PORT" ] || [ "$HTTP_PORT" = "0" ]; then
    HTTP_PORT=80
fi

echo "Configurando Apache para escuchar en el puerto ${HTTP_PORT}..."

# Configurar Apache para escuchar en el puerto correcto
sed -i "s/^Listen .*/Listen ${HTTP_PORT}/" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${HTTP_PORT}>/g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true
# Cambiar DocumentRoot a /var/www/html/public/
sed -i "s#DocumentRoot .*#DocumentRoot /var/www/html/public#g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true
sed -i "s#/var/www/html#/var/www/html/public#g" /etc/apache2/apache2.conf 2>/dev/null || true

# Forzar solo MPM prefork (PHP no es thread-safe)
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Esperar a que MySQL/MariaDB esté disponible (si las variables de BD están definidas)
if [ -n "${DB_HOST}" ] && [ -n "${DB_USER}" ]; then
    echo "Esperando a la base de datos en ${DB_HOST}:${DB_PORT}..."
    for i in $(seq 1 30); do
        if mysqladmin ping -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl --silent 2>/dev/null; then
            echo "Base de datos lista."
            break
        fi
        echo "Intento $i/30: base de datos no disponible todavía..."
        sleep 2
    done

    # Aplicar migraciones en orden si existen
    MIGRATIONS_DIR="/var/www/html/migrations"
    if [ -d "$MIGRATIONS_DIR" ]; then
        echo "Aplicando migraciones de base de datos..."
        for migration in "$MIGRATIONS_DIR"/0*.sql; do
            if [ -f "$migration" ]; then
                echo "Aplicando: $(basename "$migration")"
                mysql -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl "${DB_NAME}" < "$migration" 2>/dev/null || {
                    echo "Advertencia: error aplicando $(basename "$migration"), continuando..."
                }
            fi
        done
        echo "Migraciones procesadas."
    else
        echo "Advertencia: no se encontró el directorio de migraciones."
    fi
else
    echo "Advertencia: variables de base de datos no definidas, saltando migraciones."
fi

echo "Iniciando Apache en el puerto ${HTTP_PORT}..."
exec apache2-foreground
