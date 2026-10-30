# Guía de instalación — Sistema de gestión de un restaurante con Docker Compose

> **Modalidad:** Docker local (Docker Compose), sin necesidad de XAMPP.  
> **Estado:** planificación (documentación de referencia).  
> **Idioma:** español.

---

## 1. Requisitos previos

- Docker Desktop instalado (incluye Docker Engine y Docker Compose).
- Docker Compose versión 2.x (o 1.29+).
- Navegador web moderno.
- No se requiere XAMPP ni PHP/MySQL instalados en el host.

---

## 2. Ubicación del proyecto

Clonar o descargar el repositorio en cualquier carpeta del host. La raíz del proyecto debe contener `docker-compose.yml`, `Dockerfile`, `public/index.php` y `src/`.

Estructura relevante:
```
restaurante/
├── public/
│   └── index.php
├── src/
├── public/assets/
├── migrations/
├── documentacion/
├── documentacion_desarrollo/
├── .env.example
├── .env                        # crear localmente (no versionar)
├── .gitignore
├── docker-compose.yml
├── Dockerfile
└── README.md
```

---

## 3. Configuración de variables de entorno

1. Copiar `.env.example` a `.env` en la raíz del proyecto.
2. Editar `.env` con los valores para Docker. Ejemplo:

```
APP_ENV=development
APP_URL=http://localhost:8000
DB_HOST=mariadb
DB_PORT=3306
DB_NAME=restaurante
DB_USER=restaurante
DB_PASSWORD=contraseña_segura_de_ejemplo
SESSION_COOKIE_SECURE=0
SESSION_COOKIE_SAMESITE=Lax
```

**Clave:** `DB_HOST=mariadb` (nombre del servicio de Docker Compose, no `localhost`). La aplicación se conecta al contenedor de MariaDB mediante el nombre del servicio en la red interna.

**Importante:** `.env` es local y NO se sube al repositorio (está en `.gitignore`).

---

## 4. Servicios definidos en docker-compose.yml

| Servicio | Imagen | Responsabilidad | Puerto | Volumen |
|----------|--------|-----------------|--------|---------|
| `web` | `php:8.2-apache` (o equivalente) | Ejecutar PHP y Apache, servir la aplicación | 8000 -> 80 | Código montado (bind mount en desarrollo) |
| `mariadb` | `mariadb:10.11` | Base de datos | solo red interna (no expuesto al host por defecto) | `mariadb_data` en `/var/lib/mysql` |

### Red interna
- Red `restaurante_net` entre `web` y `mariadb`.
- `web` depende de `mariadb` y espera a que esté saludable (`depends_on` con `condition: service_healthy`).

### Health check
- `mariadb`: `mysqladmin ping` o equivalente para verificar que la base de datos está lista antes de que `web` intente conectarse.

---

## 5. Volúmenes persistentes

- `mariadb_data`: volcado de datos de MariaDB en `/var/lib/mysql`. Garantiza que los datos de la base de datos persistan aunque se detenga o elimine el contenedor `mariadb`. **No se almacenan datos solo en la capa temporal del contenedor.**
- El código de la aplicación se monta como bind mount (`./` o `./public` y `./src`) en desarrollo para editar en caliente sin reconstruir la imagen.

---

## 6. Puertos expuestos

- `web`: puerto 8000 del host mapeado al 80 del contenedor (configurable). Acceso: `http://localhost:8000`.
- `mariadb`: NO expuesto al host por defecto (solo accesible desde `web` por la red interna). Esto evita exponer la base de datos innecesariamente. Si se necesita acceso desde una herramienta de administración del host, se puede exponer el 3306 solo en desarrollo y documentarlo.

---

## 7. Procedimiento de inicio

Desde la raíz del proyecto, en terminal:

```bash
# 1. Copiar variables de entorno (si no se hizo)
cp .env.example .env
# editar .env con valores reales

# 2. Levantar los servicios
docker compose up -d

# 3. Verificar que los servicios estén saludables
docker compose ps

# 4. Aplicar migraciones (dentro del contenedor web o desde host con cliente MySQL)
# Ejemplo desde el contenedor web:
docker compose exec web bash
# dentro del contenedor:
# php migrations_aplicador.php   # o ejecutar SQL de migrations/ contra la BD
```

> Nota: el comando exacto para aplicar migraciones depende del instalador que se implemente en la fase F2. Se documentará al desarrollar.

---

## 8. Acceso desde el navegador

1. Abrir el navegador.
2. Ir a: `http://localhost:8000` (o el puerto configurado en `APP_URL`).
3. Probar las mismas funcionalidades que en XAMPP: login, registro, consulta de mesas, creación de reservas, registro de pedidos, prevención de superposiciones.

---

## 9. Procedimiento de detención y recuperación

```bash
# Detener servicios (los datos persisten en el volumen mariadb_data)
docker compose stop

# Detener y eliminar contenedores (los datos persisten en el volumen)
docker compose down

# Volver a levantar
docker compose up -d

# Si se desea eliminar también el volumen (PIERDE LOS DATOS DE LA BD):
docker compose down -v   # usar solo si se quiere reiniciar desde cero
```

**Recuperación:** al volver a levantar con `docker compose up -d`, el volumen `mariadb_data` restaura los datos de la base de datos. No se pierden los registros.

---

## 10. Gestión de registros y archivos persistentes

- Logs de PHP/Apache se envían a stdout/stderr (capturados por Docker, visibles con `docker compose logs`).
- Logs de la aplicación: definir un directorio dentro de un volumen o stdout, sin incluir secretos.
- Si en el futuro se aprueba subida de archivos, la carpeta `public/uploads/` debe montarse en un volumen persistente o bind mount para no perder archivos al reiniciar contenedores.

---

## 11. Exclusión de secretos

- `DB_PASSWORD` y cualquier clave se definen solo en `.env` (local, ignorado por Git).
- `.env.example` va versionado sin valores reales.
- Nunca incluir contraseñas en `docker-compose.yml` ni en `Dockerfile`.

---

## 12. Resolución de problemas habituales

| Problema | Posible causa | Solución |
|----------|---------------|----------|
| `web` no puede conectar a `mariadb` | `DB_HOST=localhost` en lugar de `mariadb` | Cambiar `DB_HOST=mariadb` en `.env`. |
| Error de base de datos no lista | `web` inicia antes que `mariadb` | Verificar `depends_on` con `condition: service_healthy` y health check de MariaDB. |
| No se ven cambios al editar código | Bind mount no montado o caché | Verificar volúmenes en `docker-compose.yml`; reiniciar contenedor `web`. |
| Puerto 8000 ocupado | Otro servicio usando el puerto | Cambiar el mapeo de puertos en `docker-compose.yml` y `APP_URL`. |
| Datos perdidos al reiniciar | Se usó `docker compose down -v` | No usar `-v` salvo que se quiera borrar todo. Los datos persisten en `mariadb_data`. |

---

## 13. Diferencias con XAMPP y hosting

| Aspecto | Docker | XAMPP | Hosting |
|---------|--------|-------|---------|
| Servidor web | Contenedor PHP+Apache | Apache de XAMPP | Apache del servidor |
| Host de BD | `mariadb` (servicio Docker) | `127.0.0.1` | Dirección del hosting |
| Persistencia BD | Volumen `mariadb_data` | Archivos locales de MariaDB | Gestión del proveedor |
| Instalación requerida | Docker Desktop | XAMPP | Ninguna (compatibilidad PHP+MariaDB) |

La misma lógica de aplicación y las mismas migraciones funcionan en los tres entornos; solo cambian los valores de `.env`.
