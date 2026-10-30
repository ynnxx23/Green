# Pruebas de portabilidad — SportSlot

> **Estado:** planificacion (documentacion de referencia, versionada).  
> **Idioma:** espanol.  
> **Objetivo:** comprobar que la misma aplicación funciona correctamente en los tres entornos (XAMPP, Docker Compose, hosting/servidor remoto) sin cambios en la lógica.

---

## 1. Criterios generales de portabilidad

La aplicación se considera portable cuando se cumplen todos los siguientes puntos:

1. Se puede instalar y ejecutar con XAMPP sin Docker.
2. Se puede ejecutar con Docker Compose sin XAMPP.
3. Se puede desplegar en un hosting compatible siguiendo la documentación.
4. Los tres entornos utilizan la misma lógica de negocio y reglas de validación.
5. Las migraciones producen un esquema compatible.
6. Las funcionalidades críticas (autenticación, permisos, reservas simultáneas) funcionan correctamente en cada modalidad.
7. Ningún entorno depende accidentalmente de secretos, archivos o servicios exclusivos de otro.
8. La configuración de producción no utiliza credenciales ni opciones inseguras propias del desarrollo.

---

## 2. Lista de pruebas de portabilidad

| # | Prueba | Entorno(s) | Resultado esperado verificable |
|---|--------|------------|--------------------------------|
| P-01 | Instalación y arranque con XAMPP (sin Docker) | XAMPP | Apache y MariaDB funcionan; `http://localhost/sportslot/public/` responde; no se requiere Docker instalado. |
| P-02 | Instalación y arranque con Docker Compose (sin XAMPP) | Docker | `docker compose up -d` levanta `web` y `mariadb`; `http://localhost:8000` responde; no se requiere XAMPP. |
| P-03 | Despliegue en hosting siguiendo la documentación | Hosting | Sitio accesible por `https://dominio.real/`; PHP 8.1+ y MariaDB disponibles; migraciones aplicadas mediante panel/consola. |
| P-04 | Misma lógica de negocio en los tres entornos | XAMPP, Docker, Hosting | Crear una reserva, consultar disponibilidad y cancelar produce el mismo comportamiento y resultados en los tres. |
| P-05 | Migraciones producen esquema compatible | XAMPP, Docker, Hosting | Ejecutar `001_create_schema.sql`, `002_migraciones_control.sql`, `003_datos_iniciales.sql` en cada entorno; las mismas tablas y columnas existen. |
| P-06 | Autenticación funciona en cada modalidad | XAMPP, Docker, Hosting | Registro, login con bcrypt, logout y sesión con cookie `HttpOnly`/`SameSite` funcionan en los tres. |
| P-07 | Permisos y roles funcionan en cada modalidad | XAMPP, Docker, Hosting | Cliente no accede a endpoints de administración (403); administrador sí. Verificado en los tres entornos. |
| P-08 | Reservas simultáneas funcionan en cada modalidad | XAMPP, Docker, Hosting | Dos usuarios intentando reservar la misma cancha y horario al mismo tiempo: solo una se confirma; no hay duplicados. |
| P-09 | Ningún entorno depende de secretos/archivos exclusivos de otro | XAMPP, Docker, Hosting | Cada entorno usa su propio `.env` con valores de ese entorno; no hay rutas IP ni puertos hardcodeados en la lógica. |
| P-10 | Producción no usa credenciales inseguras de desarrollo | Hosting | `SESSION_COOKIE_SECURE=1`, `APP_ENV=production`, `DB_*` con credenciales de producción, HTTPS activo. |
| P-11 | `DB_HOST` correcto por entorno | XAMPP: `127.0.0.1`; Docker: `mariadb`; Hosting: dirección del proveedor | La aplicación se conecta a la base de datos en cada entorno sin modificar el código. |
| P-12 | Rate limiting funciona en servidor (no solo frontend) | XAMPP, Docker, Hosting | Superar el límite de un endpoint devuelve HTTP 429 con `Retry-After`, en los tres entornos. |

---

## 3. Procedimiento de ejecución de las pruebas

1. Preparar el entorno a probar (XAMPP, Docker o hosting).
2. Copiar `.env.example` a `.env` con valores de ese entorno.
3. Aplicar migraciones en orden.
4. Ejecutar cada prueba de la tabla P-01 a P-12.
5. Registrar el resultado (pasó / no pasó) y evidencia (captura, log o consulta SQL).
6. Si alguna prueba falla, corregir la configuración o el código (sin cambiar la lógica por entorno) y repetir.

---

## 4. Evidencia sugerida por prueba

- P-01 / P-02 / P-03: captura del navegador mostrando la aplicación cargando; `docker compose ps` o `php -v`.
- P-05: consulta `SHOW TABLES;` y `DESCRIBE reservas;` en cada entorno.
- P-06 / P-07: capturas de login exitoso, intento de acceso denegado (403) para cliente en admin.
- P-08: dos peticiones simultáneas (script o dos navegadores) y consulta `SELECT COUNT(*) FROM reservas WHERE id_cancha=X AND fecha_reserva=Y AND ...` → resultado = 1.
- P-10: revisar que `SESSION_COOKIE_SECURE=1` y `APP_ENV=production` en `.env` de producción; cookie con bandera Secure en herramientas de desarrollador del navegador.
- P-12: enviando N+1 peticiones a `/api/login` y observando respuesta 429.

---

## 5. Criterio de aprobación de portabilidad

La portabilidad se aprueba cuando todas las pruebas P-01 a P-12 han pasado con resultado esperado verificable y se ha documentado la evidencia. Cualquier fallo bloquea la consideración del entorno como listo.
