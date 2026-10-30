# Guía de instalación — Sistema de gestión de un restaurante con XAMPP

> **Modalidad:** XAMPP local (Apache + PHP + MariaDB/MySQL), sin necesidad de Docker.  
> **Estado:** planificación (documentación de referencia).  
> **Idioma:** español.

---

## 1. Requisitos previos

- XAMPP instalado (versión que incluya Apache, PHP y MariaDB/MySQL).
- PHP versión compatible: PHP 8.1 o superior (recomendado PHP 8.2).
- MariaDB versión 10.11 o MySQL 8.0 (compatibles entre sí).
- Extensiones PHP necesarias: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `session`.
- Navegador web moderno.
- Editor de texto o IDE (opcional, para revisar código).

No se requiere Docker ni Docker Compose para esta modalidad.

---

## 2. Ubicación del proyecto

1. Descargar o clonar el repositorio del proyecto.
2. Copiar la carpeta del proyecto dentro de `htdocs` de XAMPP.
   - Ejemplo: `C:\xampp\htdocs\restaurante\`
3. La raíz del proyecto debe contener `public/index.php` como front controller.

Estructura esperada dentro de `htdocs\restaurante\`:
```
restaurante/
├── public/
│   └── index.php
├── src/
├── public/assets/
├── documentacion/
├── documentacion_desarrollo/   # ignorada por Git (interna)
├── .env.example
├── .env                        # crear localmente (no versionar)
├── .gitignore
├── docker-compose.yml          # no usado en XAMPP, pero presente
└── README.md
```

---

## 3. Configuración de Apache

- XAMPP trae Apache preconfigurado. No es necesario modificar `httpd.conf` salvo que se quiera cambiar el puerto o el directorio raíz.
- Asegurarse de que el módulo `mod_rewrite` esté habilitado si se usa reescritura de URLs limpias (opcional).
- El proyecto se accede desde `http://localhost/restaurante/public/` o según la carpeta donde se haya colocado.

Si se desea usar una URL amigable (virtual host), se puede agregar en `httpd-vhosts.conf` de XAMPP, pero no es obligatorio.

---

## 4. Configuración de la conexión a la base de datos

1. Copiar `.env.example` a `.env` en la raíz del proyecto.
2. Editar `.env` con los valores reales del entorno XAMPP:

```
APP_ENV=development
APP_URL=http://localhost/restaurante/public
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=restaurante
DB_USER=root
DB_PASSWORD=
SESSION_COOKIE_SECURE=0
SESSION_COOKIE_SAMESITE=Lax
```

- En XAMPP predeterminado, `DB_USER=root` y `DB_PASSWORD` vacío. Cambiar según la configuración de tu XAMPP.
- `SESSION_COOKIE_SECURE=0` porque en XAMPP local se usa HTTP, no HTTPS.

**Importante:** `.env` es local y NO se sube al repositorio (está en `.gitignore`).

---

## 5. Creación e inicialización de la base de datos

1. Iniciar Apache y MariaDB desde el panel de control de XAMPP.
2. Abrir phpMyAdmin: `http://localhost/phpmyadmin/`.
3. Crear una base de datos llamada `restaurante` (o el nombre definido en `DB_NAME`).
   - Colación recomendada: `utf8mb4_unicode_ci`.
4. Ejecutar las migraciones en orden:
   - `migrations/001_create_schema.sql`
   - `migrations/002_migraciones_control.sql` (tabla de control de migraciones)
   - `migrations/003_datos_iniciales.sql` (roles y usuario administrador)

   Se pueden ejecutar desde phpMyAdmin (pestaña SQL) copiando el contenido de cada archivo, o mediante consola:
   ```
   mysql -u root -p restaurante < migrations/001_create_schema.sql
   ```

5. Verificar que las tablas se crearon correctamente: `roles`, `usuarios`, `areas`, `mesas`, `horarios_funcionamiento`, `bloques_mesa`, `reservas`, `pedidos`, `detalle_pedidos`, `productos`, `intentos_login`, `rate_limit_state`, `migraciones_aplicadas`.

---

## 6. Ejecución de migraciones

Las migraciones son archivos SQL versionados en `migrations/`. Deben aplicarse en orden numérico y una sola vez por entorno.

- No se recrea ni borra la base de datos automáticamente al iniciar la aplicación.
- Si ya existe una base de datos con tablas, aplicar solo las migraciones pendientes (consultar `migraciones_aplicadas`).

---

## 7. Configuración de permisos y directorios

- La carpeta del proyecto debe ser legible por Apache.
- Si en el futuro se aprueba la mejora de subida de archivos, la carpeta `public/uploads/` debe ser escribible por el usuario bajo el cual corre Apache (en Windows/XAMPP generalmente no requiere cambios adicionales; en Linux asignar propietario adecuado).
- Los archivos de configuración y código no necesitan permisos de escritura para Apache.

---

## 8. Acceso desde el navegador

1. Abrir el navegador.
2. Ir a: `http://localhost/restaurante/public/` (ajustar según la ruta real).
3. Debería verse la pantalla de inicio de sesión o la página principal.
4. Probar:
   - Registro de usuario.
   - Inicio de sesión con el administrador creado en las migraciones.
   - Consulta de áreas y mesas.
   - Creación de una reserva de mesa.
   - Registro de un pedido.
   - Verificación de que no se permiten reservas superpuestas.

---

## 9. Resolución de problemas habituales

| Problema | Posible causa | Solución |
|----------|---------------|----------|
| No se muestra la página (error 404) | Ruta incorrecta o `index.php` no accesible | Verificar que el proyecto está en `htdocs` y la URL apunta a `public/`. |
| Error de conexión a la BD | `.env` mal configurado o MariaDB detenido | Revisar `DB_HOST`, `DB_USER`, `DB_PASSWORD` y que MariaDB esté iniciado en XAMPP. |
| Tabla no encontrada | Migraciones no aplicadas | Ejecutar los archivos SQL de `migrations/` en orden. |
| Error de permisos al escribir | Carpeta sin permisos | Ajustar permisos de la carpeta del proyecto (en Windows suele no ser necesario). |
| Contraseña no funciona | Hash no bcrypt o contraseña distinta | Verificar que se usó `password_hash` y que se ingresa la contraseña correcta. |
| Sesión no persiste | Cookies bloqueadas o `SESSION_COOKIE_SECURE=1` en HTTP | En XAMPP local usar `SESSION_COOKIE_SECURE=0`. |

---

## 10. Notas importantes

- Esta guía es para el entorno de desarrollo local con XAMPP. Los mismos archivos de configuración (`.env.example`) y lógica de la aplicación sirven para Docker y hosting, cambiando solo los valores de `.env`.
- No incluir `documentacion_desarrollo/` en el servidor de producción (es documentación interna del equipo).
- No subir `.env` con credenciales reales al repositorio.
