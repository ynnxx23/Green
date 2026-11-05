#!/bin/bash
set -e

echo "=== Entrypoint: iniciando sistema de restaurante ==="

# Esperar a que MySQL/MariaDB esté disponible (desactivar verificación SSL para certificados autofirmados)
echo "Esperando a la base de datos en ${DB_HOST}:${DB_PORT}..."
for i in {1..30}; do
  if mysqladmin ping -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl --silent 2>/dev/null; then
    echo "Base de datos lista."
    break
  fi
  echo "Intento $i/30: base de datos no disponible todavía..."
  sleep 2
done

# Aplicar migraciones en orden si existen (desactivar verificación SSL)
MIGRATIONS_DIR="/var/www/html/migrations"
if [ -d "$MIGRATIONS_DIR" ]; then
  echo "Aplicando migraciones de base de datos..."
  for migration in "$MIGRATIONS_DIR"/0*.sql; do
    if [ -f "$migration" ]; then
      echo "Aplicando: $(basename "$migration")"
      mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl "${DB_NAME}" < "$migration" || {
        echo "Advertencia: error aplicando $(basename "$migration"), continuando..."
      }
    fi
  done
  echo "Migraciones procesadas."
else
  echo "Advertencia: no se encontró el directorio de migraciones."
fi

echo "Iniciando Apache..."
exec apache2-foreground
