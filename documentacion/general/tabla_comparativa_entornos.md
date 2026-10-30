# Tabla comparativa de entornos — Sistema de gestión de un restaurante

> **Estado:** planificación (documentación de referencia, versionada).  
> **Idioma:** español.

---

## 1. Resumen comparativo

| Aspecto | XAMPP local | Docker Compose local | Hosting / servidor remoto |
|---------|-------------|----------------------|---------------------------|
| **Docker requerido** | No | Sí (Docker Desktop) | No |
| **XAMPP requerido** | Sí | No | No |
| **Servidor web** | Apache (XAMPP) | Apache + PHP en contenedor | Apache u otro compatible del proveedor |
| **PHP** | Instalado con XAMPP | En contenedor `php:8.2-apache` | Del proveedor (PHP 8.1+) |
| **Base de datos** | MariaDB/MySQL local de XAMPP | MariaDB en contenedor `mariadb:10.11` | MariaDB/MySQL del proveedor |
| **Host de BD** | `127.0.0.1` o `localhost` | `mariadb` (nombre del servicio) | Dirección configurada del proveedor |
| **Persistencia de BD** | Archivos locales de MariaDB/XAMPP | Volumen `mariadb_data` | Gestión del proveedor / dump externo |
| **Puerto de acceso** | `http://localhost/restaurante/public/` | `http://localhost:8000` | `https://dominio.real/` |
| **HTTPS en desarrollo** | No (HTTP local, `SESSION_COOKIE_SECURE=0`) | No (HTTP local, `SESSION_COOKIE_SECURE=0`) | Sí obligatorio (`SESSION_COOKIE_SECURE=1`) |
| **Secretos en `.env`** | Local, ignorado por Git | Local, ignorado por Git | En servidor, fuera de la raíz web |
| **Migraciones** | SQL vía phpMyAdmin/consola | SQL vía contenedor o host | SQL vía panel de control/consola |
| **Docker en producción** | No aplica | No obligatorio | No se presupone |
| **Procesos permanentes** | No | No (request-response) | No se presupone |
| **Tareas programadas (cron)** | No | No | No se presupone |
| **Portabilidad** | Misma lógica y migraciones | Misma lógica y migraciones | Misma lógica y migraciones |

---

## 2. Limitaciones por entorno

### XAMPP local
- Requiere instalar XAMPP en la máquina.
- Solo accesible desde la máquina local (salvo configurar red).
- La base de datos vive en los archivos locales de XAMPP; hacer respaldos manuales.
- No aislamiento de contenedor; PHP y MariaDB comparten el sistema host.

### Docker Compose local
- Requiere Docker Desktop (consume más recursos).
- `DB_HOST` es `mariadb` (nombre de servicio), no `localhost`.
- Los datos de BD persisten en el volumen `mariadb_data` (no se pierden al reiniciar contenedores, salvo `down -v`).
- Puerto 8000 configurable; puede entrar en conflicto con otros servicios.

### Hosting / servidor remoto
- No se presupone Docker; la aplicación corre con PHP nativo.
- Puede no tener acceso SSH ni consola; las migraciones se aplican por phpMyAdmin o panel de control.
- Puede no tener tareas programadas (cron); las operaciones de vencimiento se hacen bajo demanda.
- Puede no permitir procesos permanentes ni colas.
- HTTPS obligatorio en producción; `SESSION_COOKIE_SECURE=1`.

---

## 3. Qué es igual en todos los entornos

- Mismo código fuente (PHP, JS, HTML, CSS).
- Misma lógica de negocio y reglas de negocio (RB-01 a RB-12).
- Misma estructura de base de datos y mismas migraciones versionadas.
- Mismos endpoints y contratos de API.
- Mismas medidas de seguridad (bcrypt, consultas preparadas, cookies seguras, rate limiting en servidor).
- Misma configuración por variables de entorno (`.env`).
- Mismos criterios de aceptación y pruebas (06_pruebas_y_calidad.md).

---

## 4. Qué cambia entre entornos (solo configuración)

| Elemento | XAMPP | Docker | Hosting |
|----------|-------|--------|---------|
| `DB_HOST` | `127.0.0.1` | `mariadb` | dirección del proveedor |
| `DB_PORT` | `3306` | `3306` | `3306` (o la del proveedor) |
| `DB_NAME` | `restaurante_dev` | `restaurante` | `restaurante_prod` |
| `DB_USER` | `root` (XAMPP) | `restaurante` | usuario de producción |
| `DB_PASSWORD` | (vacío o XAMPP) | secreto | secreto de producción |
| `APP_URL` | `http://localhost/...` | `http://localhost:8000` | `https://dominio.real/` |
| `SESSION_COOKIE_SECURE` | `0` | `0` | `1` |
| `SESSION_COOKIE_SAMESITE` | `Lax` | `Lax` | `Lax` o `Strict` |
| `APP_ENV` | `development` | `development` | `production` |

**Ningún cambio se hace en la lógica de la aplicación por entorno.** El entorno es una decisión de configuración e instalación.

---

## 5. Matriz de portabilidad (qué verificar)

| Prueba | XAMPP | Docker | Hosting |
|--------|-------|--------|---------|
| Instalar y ejecutar sin Docker | Sí | N/A | N/A |
| Ejecutar con Docker Compose sin XAMPP | N/A | Sí | N/A |
| Desplegar en hosting siguiendo documentación | N/A | N/A | Sí |
| Misma lógica de negocio en los tres | Sí | Sí | Sí |
| Migraciones producen esquema compatible | Sí | Sí | Sí |
| Autenticación funciona | Sí | Sí | Sí |
| Permisos y roles funcionan | Sí | Sí | Sí |
| Reservas simultáneas funcionan | Sí | Sí | Sí |
| Pedidos y cambio de estado funcionan | Sí | Sí | Sí |
| Ningún entorno depende de secretos/archivos exclusivos de otro | Sí | Sí | Sí |
| Producción no usa credenciales inseguras de desarrollo | Sí | Sí | Sí |
