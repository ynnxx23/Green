# Guía de despliegue — Sistema de gestión de un restaurante en hosting o servidor remoto

> **Modalidad:** Hosting compartido o servidor propio con PHP y MariaDB/MySQL.  
> **Estado:** planificación (documentación de referencia).  
> **Idioma:** español.

---

## 1. Requisitos mínimos del proveedor de hosting

El proveedor debe cumplir lo siguiente:

| Requisito | Detalle |
|-----------|---------|
| Versión de PHP | PHP 8.1 o superior (recomendado PHP 8.2). |
| Extensiones PHP | `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `session`, `json`, `filter`. |
| Base de datos | MariaDB 10.11 o MySQL 8.0 (compatibles). |
| Acceso a la base de datos | Usuario con privilegios para crear tablas, insertar, actualizar, eliminar y consultar. |
| Servidor web | Apache u otro servidor web compatible con PHP (nginx con PHP-FPM también es válido si se configura). |
| HTTPS | Soporte para HTTPS (certificado SSL/TLS) para producción. `SESSION_COOKIE_SECURE=1`. |
| Variables de entorno / secretos | Capacidad de definir variables de entorno o un archivo de configuración local fuera del repositorio. |
| Permisos de archivos | Permisos adecuados para que el servidor web lea el proyecto y escriba en directorios de sesiones/subidos (si aplica). |
| Operaciones de inicialización | Capacidad de ejecutar scripts SQL (phpMyAdmin, consola SSH, panel de control, etc.) para aplicar migraciones. |

**No se presupone:**
- Que el hosting permita Docker (la aplicación debe funcionar sin Docker).
- Que permita procesos permanentes, comandos de consola o tareas programadas (cron). Si no los tiene, la aplicación debe desplegarse sin depender de ellos.

---

## 2. Estructura para subir al servidor

Subir todos los archivos del proyecto excepto:
- `documentacion_desarrollo/` (documentación interna, ignorada por Git).
- `.env` con secretos reales (subir solo `.env.example`; crear `.env` en el servidor con valores de producción).
- `vendor/` (si se usa Composer y no se instala en el servidor; subir dependencias si es necesario).
- Archivos de entorno de desarrollo local.

Estructura recomendada en el servidor:
```
/home/usuario/restaurante/
├── public/
├── src/
├── public/assets/
├── migrations/
├── documentacion/
├── .env                  # creado en el servidor, con valores de producción
├── .gitignore
├── README.md
```

Colocar `public/` como raíz del documento web del servidor web (o configurar el servidor para que apunte a `public/`).

---

## 3. Procedimiento de despliegue

1. **Preparar el servidor:**
   - Verificar versión de PHP y extensiones necesarias.
   - Crear una base de datos y un usuario con contraseña segura.
   - Configurar un dominio o subdominio con HTTPS (certificado SSL).

2. **Subir los archivos:**
   - Subir el proyecto (menos los elementos excluidos) al servidor mediante FTP, SFTP, panel de control o Git.

3. **Configurar el entorno:**
   - Crear `.env` en la raíz con valores de producción:
     ```
     APP_ENV=production
     APP_URL=https://dominio.real/
     DB_HOST=direccion-del-servidor-de-bd
     DB_PORT=3306
     DB_NAME=restaurante_prod
     DB_USER=usuario_produccion
     DB_PASSWORD=contraseña_segura_de_produccion
     SESSION_COOKIE_SECURE=1
     SESSION_COOKIE_SAMESITE=Lax
     ```
   - `SESSION_COOKIE_SECURE=1` porque en producción se usa HTTPS.

4. **Aplicar migraciones:**
   - Ejecutar en orden los archivos SQL de `migrations/` contra la base de datos de producción:
     - `001_create_schema.sql`
     - `002_migraciones_control.sql`
     - `003_datos_iniciales.sql`
   - Usar phpMyAdmin, consola SSH o el panel de control del hosting.
   - Verificar que las tablas se crearon y que los datos iniciales (roles, admin) existen.

5. **Configurar permisos:**
   - El servidor web debe poder leer los archivos de PHP, CSS, JS.
   - Si se usa almacenamiento de sesiones en archivos, el directorio de sesiones debe ser escribible por el usuario del servidor web.
   - No otorgar permisos de escritura innecesarios al código.

6. **Validar el despliegue:**
   - Acceder a `https://dominio.real/` y probar login, registro, consulta de mesas, creación de reservas, registro de pedidos, panel de administración.
   - Verificar que las cookies de sesión tienen `Secure` y `HttpOnly`.
   - Verificar que no se muestran errores internos ni consultas SQL al usuario (`display_errors=Off` en producción).

---

## 4. Procedimiento de actualización

1. Subir los nuevos archivos (sobreescribiendo los cambiados).
2. Aplicar migraciones pendientes (consultar `migraciones_aplicadas`; no reejecutar las ya aplicadas).
3. Probar funcionalidades críticas (autenticación, reservas, pedidos, permisos).
4. Si algo falla, revertir a la versión estable anterior (restaurar archivos y base de datos desde copia de seguridad).

---

## 5. Copias de seguridad y vuelta a versión estable

- **Base de datos:** exportar periódicamente con `mysqldump` o el panel de control del hosting. Almacenar fuera del servidor si es posible.
- **Archivos:** copiar la carpeta del proyecto (sin secretos) como respaldo de la versión estable.
- **Vuelta atrás:** restaurar archivos y base de datos desde la copia de seguridad de la versión estable conocida y funcionando.

---

## 6. Limitaciones de hosting compartido y cómo abordarlas

| Limitación | Impacto | Solución en el sistema |
|------------|---------|------------------------|
| No hay Docker | No se puede usar `docker compose` | La aplicación funciona sin Docker (PHP nativo + MariaDB). |
| No hay comandos de consola / SSH | No se pueden ejecutar migraciones por CLI | Aplicar migraciones mediante phpMyAdmin o panel de control (scripts SQL). |
| No hay tareas programadas (cron) | No se pueden ejecutar limpiezas automáticas | Las operaciones de vencimiento de reservas se hacen bajo demanda o al acceder; documentar como límite. |
| No hay procesos permanentes | No se pueden usar colas ni workers | Todo es request-response síncrono de PHP. |
| Espacio de almacenamiento limitado | Subidas de archivos podrían llenar el disco | No se implementa subida de archivos en la versión base (mejora opcional). |

---

## 7. Seguridad en producción

- `display_errors=Off` en `php.ini` o en `.htaccess`; errores se escriben a `error_log`, no se muestran al usuario.
- `SESSION_COOKIE_SECURE=1` y `SESSION_COOKIE_SAMESITE=Lax` (o `Strict`).
- `.env` fuera de la raíz web (no accesible por URL).
- No incluir `documentacion_desarrollo/` en el servidor.
- HTTPS obligatorio; redirigir HTTP a HTTPS si el servidor lo permite.

---

## 8. Resolución de problemas habituales en hosting

| Problema | Posible causa | Solución |
|----------|---------------|----------|
| Error 500 interno | Error de PHP sin mostrar | Revisar `error_log` del servidor; activar `display_errors` temporalmente solo en desarrollo. |
| No se conecta a la BD | `DB_HOST` incorrecto o credenciales erradas | Verificar dirección del servidor de BD y usuario/contraseña. |
| Migraciones fallan | Usuario sin privilegios para crear tablas | Conceder privilegios al usuario de BD en el panel de control. |
| Sesión no funciona | `session.save_path` sin permisos de escritura | Contactar al proveedor o configurar `session.save_path` en un directorioWritable. |
| Cookies no se envían | `SESSION_COOKIE_SECURE=1` sin HTTPS | Asegurar que el sitio se sirva por HTTPS o ajustar la variable en desarrollo. |
