# Lista de variables de configuración — Sistema de gestión de un restaurante

> **Estado:** planificación (documentación de referencia, versionada).  
> **Idioma:** español.  
> **Importante:** este archivo documenta los NOMBRES de las variables y su propósito. Los valores reales y secretos NO se incluyen aquí; viven en `.env` local (ignorado por Git).

---

## 1. Variables de entorno por entorno

| Variable | Descripción | Ejemplo (no es valor real) | Desarrollo XAMPP | Desarrollo Docker | Pruebas | Producción |
|----------|-------------|----------------------------|------------------|-------------------|---------|------------|
| `APP_ENV` | Entorno de ejecución | `development`, `testing`, `production` | `development` | `development` | `testing` | `production` |
| `APP_URL` | Dirección pública de la aplicación | `http://localhost/restaurante/public` | `http://localhost/restaurante/public` | `http://localhost:8000` | `http://tests.local` | `https://dominio.real/` |
| `DB_HOST` | Servidor de base de datos | `127.0.0.1` o nombre de servicio Docker | `127.0.0.1` | `mariadb` | `db-tests` | dirección del proveedor |
| `DB_PORT` | Puerto de la base de datos | `3306` | `3306` | `3306` | `3306` | `3306` |
| `DB_NAME` | Nombre de la base de datos | `restaurante` | `restaurante_dev` | `restaurante` | `restaurante_test` | `restaurante_prod` |
| `DB_USER` | Usuario de la base de datos | `restaurante` | `root` | `restaurante` | `restaurante_test` | usuario de producción |
| `DB_PASSWORD` | Contraseña de la base de datos | (secreto) | (vacío o XAMPP) | secreto | secreto | secreto de producción |
| `SESSION_COOKIE_SECURE` | Cookie de sesión solo sobre HTTPS | `0` o `1` | `0` | `0` | `0` | `1` |
| `SESSION_COOKIE_SAMESITE` | Política SameSite de la cookie de sesión | `Lax` o `Strict` | `Lax` | `Lax` | `Lax` | `Lax` o `Strict` |
| `RATE_LIMIT_CAPACITY` | Capacidad máxima del depósito Token Bucket | `60` | `60` | `60` | `60` | `60` |
| `RATE_LIMIT_REFILL_RATE` | Velocidad de reposición (tokens por segundo) | `1` | `1` | `1` | `1` | `1` |

---

## 2. Variables opcionales / pendientes (según decisiones A-04, SEG-01)

Estas variables solo se definen si se confirma el uso de JWT o mejoras relacionadas:

| Variable | Descripción | ¿Usada en versión base? |
|----------|-------------|-------------------------|
| `JWT_SECRET` | Clave de firma de JWT | No (solo si se aprueba JWT como mejora) |
| `JWT_ISSUER` | Emisor del JWT | No |
| `JWT_AUDIENCE` | Audiencia del JWT | No |
| `JWT_ACCESS_TTL` | Tiempo de vida del access token | No |
| `JWT_REFRESH_TTL` | Tiempo de vida del refresh token | No |

---

## 3. Cómo configurar cada entorno

### Desarrollo con XAMPP
1. Copiar `.env.example` a `.env`.
2. Completar `DB_HOST=127.0.0.1`, `DB_USER=root`, `DB_PASSWORD=` (según tu XAMPP), `SESSION_COOKIE_SECURE=0`.
3. El resto con valores de ejemplo de la tabla anterior.

### Desarrollo con Docker Compose
1. Copiar `.env.example` a `.env`.
2. Completar `DB_HOST=mariadb` (nombre del servicio), `DB_NAME=restaurante`, `DB_USER=restaurante`, `DB_PASSWORD` (secreto local), `SESSION_COOKIE_SECURE=0`.

### Pruebas automatizadas
1. `.env` de pruebas con base de datos aislada (`restaurante_test`).
2. `SESSION_COOKIE_SECURE=0` (HTTP en pruebas locales).

### Producción (hosting o servidor remoto)
1. Crear `.env` en el servidor (fuera de la raíz web accesible por URL).
2. Valores reales de producción: `APP_ENV=production`, `SESSION_COOKIE_SECURE=1`, `DB_*` con credenciales de producción, `APP_URL=https://dominio.real/`.
3. Nunca subir `.env` al repositorio.

---

## 4. Archivos asociados

- `.env.example`: ejemplo versionado en la raíz del proyecto, sin secretos reales.
- `.env`: archivo local con valores reales, ignorado por Git (`.gitignore`).
- `documentacion/variables_configuracion.md`: este documento.
- `src/Config.php`: clase que carga y expone estas variables (no contiene valores hardcodeados).

---

## 5. Reglas de manejo de secretos

- Ninguna variable con valor real o secreto se versiona en Git.
- `.env.example` solo muestra nombres de variables y ejemplos ilustrativos (no credenciales reales).
- Los secretos de producción se gestionan en el servidor o mediante los secretos cifrados de GitHub Actions (para CI/CD), nunca en archivos del repositorio.
- No se muestran secretos en logs, mensajes de error ni respuestas HTTP.

---

## 6. Claves de configuración del sistema (tabla `configuracion_sistema`)

Estas claves se gestionan desde el panel de administración (RF-101) y se almacenan en la tabla `configuracion_sistema`. No son variables de entorno `.env`; son configuraciones de negocio editables en runtime.

| Clave | Tipo | Descripción | Valor por defecto | Ejemplo |
|-------|------|-------------|-------------------|---------|
| `restaurante_nombre` | string | Nombre comercial del restaurante | `Green en la Casa Blanca` | `Green en la Casa Blanca` |
| `restaurante_telefono` | string | Teléfono de contacto principal | `+57 300 000 0000` | `+57 300 123 4567` |
| `restaurante_email` | string | Email de contacto/reservas | `reservas@greencasablanca.com` | `info@restaurante.com` |
| `restaurante_direccion` | string | Dirección física completa | `Av. Principal 123, Ciudad` | `Calle 45 #10-20, Bogotá` |
| `restaurante_horario_apertura` | string | Hora de apertura diaria (formato HH:MM) | `11:00` | `12:00` |
| `restaurante_horario_cierre` | string | Hora de cierre diaria (formato HH:MM) | `23:00` | `22:30` |
| `moneda_codigo` | string | Código ISO 4217 de la moneda | `COP` | `COP`, `USD`, `EUR` |
| `moneda_simbolo` | string | Símbolo de la moneda | `$` | `$`, `US$`, `€` |
| `iva_porcentaje` | float | Porcentaje de IVA aplicable | `19.0` | `19.0`, `21.0`, `0` |
| `capacidad_maxima_total` | int | Capacidad máxima total del restaurante | `100` | `120` |
| `tiempo_preparacion_estandar_min` | int | Tiempo estándar de preparación (minutos) | `30` | `25`, `40` |
| `politica_sena_porcentaje` | float | Porcentaje de seña para pedidos anticipados | `50.0` | `50.0`, `30.0` |
| `politica_cancelacion_horas` | int | Horas mínimas para cancelar sin penalidad | `2` | `2`, `4`, `24` |
| `limite_pedidos_sin_reserva_periodo` | string | Período para límite: `dia`, `semana`, `mes` | `dia` | `dia`, `semana`, `mes` |
| `limite_pedidos_sin_reserva_maximo` | int | Límite máximo de pedidos sin reserva por período (0 = sin límite) | `50` | `50`, `100`, `0` |
| `puntos_fidelidad_por_monto` | float | Puntos ganados por cada unidad monetaria gastada | `0.001` | `0.001` (1 pt / $1000 COP) |
| `puntos_fidelidad_valor_canje` | float | Valor monetario por punto al canjear | `10.0` | `10.0` (100 pts = $1000) |
| `puntos_fidelidad_expiracion_meses` | int | Meses hasta que expiran los puntos sin uso | `12` | `12`, `6`, `24` |
| `delivery_tiempo_estimado_min` | int | Tiempo estimado estándar de delivery (minutos) | `30` | `30`, `45`, `60` |
| `notificaciones_whatsapp_habilitado` | bool | Habilitar notificaciones por WhatsApp | `0` | `0`, `1` |
| `notificaciones_email_habilitado` | bool | Habilitar notificaciones por Email | `0` | `0`, `1` |
| `notificaciones_push_habilitado` | bool | Habilitar notificaciones Push | `0` | `0`, `1` |

> **Nota:** Estas claves se leen/escriben vía endpoints `GET/PUT /api/configuracion` y `GET/PUT /api/configuracion/limite-pedidos-sin-reserva`. El backend usa valores por defecto si la clave no existe en la tabla.
