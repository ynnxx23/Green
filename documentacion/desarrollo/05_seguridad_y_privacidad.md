# 05. Seguridad y privacidad — Sistema de gestion de un restaurante

> **Estado:** borrador de planificacion (etapa 1 - sin programar).  
> **Idioma:** espanol.

---

## 1. Distinciones conceptuales (no confundir)

| Concepto | Qué es | Uso en el sistema |
|----------|--------|-------------------|
| Autenticación | Verificar identidad de quien accede | Inicio de sesión con email + contraseña |
| Autorización | Verificar si ese identity puede hacer una operación | Roles cliente/mesero/cocinero/administrador, verificar en servidor |
| Cifrado | Ocultar contenido de datos | HTTPS (TLS) en transporte; no hay cifrado de datos en reposo requerido por el alcance |
| Hashing | Transformación irreversible de contraseñas | bcrypt (`password_hash` / `password_verify`) para contraseñas |
| Firma digital | Garantizar integridad y autoría | No requerida por el alcance base (no hay JWT ni documentos firmados) |

**Regla firme:** No se almacenan contraseñas con SHA-256. Solo bcrypt. No se usa SHA-256 como firma de JWT. SHA-256 solo si surge una necesidad técnica concreta y justificada (documentada).

---

## 2. Autenticación y sesiones

### Mecanismo elegido (temporal, sujeta a confirmación A-04)
**Sesión nativa de PHP con cookie de sesión.** No se usa JWT ni access/refresh/ID tokens en la versión base.

Justificación:
- Es una aplicación web servidor-renderizada (PHP) consumida por navegador. La cookie de sesión de PHP es suficiente, simple y segura si se configura bien.
- Agregar JWT sin una necesidad real (API stateless para clientes externos) añade complejidad, almacenamiento de tokens y superficie de ataque sin beneficio.
- Si en el futuro se requiere una API stateless, se evaluará JWT como mejora aprobada (ver 01, mejora 2).

### Almacenamiento de contraseñas
- Registro: `password_hash($contrasena, PASSWORD_BCRYPT)`.
- Inicio de sesión: `password_verify($contrasena, $hash_almacenado)`.
- Nunca texto plano, nunca SHA-256 para contraseñas.
- Longitud mínima de contraseña a definir (recomendado ≥ 8 caracteres).

### Cookie de sesión
- Nombre de sesión: configurable (por defecto PHPSESSID, renombrable).
- `HttpOnly`: sí (JavaScript no puede leerla).
- `Secure`: sí cuando la aplicación se sirve por HTTPS (en desarrollo HTTP local puede ser `false`, controlado por variable de entorno `SESSION_COOKIE_SECURE`).
- `SameSite`: `Lax` (protección CSRF razonable para navegación normal; `Strict` si se requiere mayor restricción y se confirma que no rompe flujos legítimos).
- `path=/`, dominio apropiado al entorno.
- Tiempo de vida de la sesión y expiración: configurable (recomendado, ej. 30 minutos de inactividad).

### Cierre y rotación de sesión
- Cierre de sesión: `session_destroy()` + eliminación de la cookie.
- Rotación de ID de sesión (`session_regenerate_id(true)`) al iniciar sesión y al cambiar de rol/privilegio, para fijación de sesión.
- Expiración: la sesión expira tras inactividad configurada; el servidor la invalida.

### Recuperación de contraseña (RF-04) — protegida
- Solicitud de restablecimiento: el usuario ingresa su email; el sistema genera un **token de recuperación único, aleatorio y de un solo uso**, almacena su hash con expiración corta (ej. 15-60 minutos) y envía un enlace (mejora: por email; si no hay servicio de correo, se documenta el bloqueo y no se simula envío).
- El enlace restablece la contraseña tras validar el token y su expiración; el token se invalida tras usarse.
- No se revela si el email existe o no (mensaje genérico: "Si la cuenta existe, recibirá instrucciones") para evitar enumeración de usuarios.

### Intentos automatizados de inicio de sesión (fuerza bruta)
- Tabla `intentos_login` para registrar intentos fallidos por email y dirección IP.
- Rate limiting específico en el endpoint de login (Token Bucket, ver sección 5).
- Bloqueo temporal de la cuenta/IP tras N intentos fallidos consecutivos (ej. 5 fallos → bloqueo de 15 minutos), definido como regla de negocio configurable.
- No se revela si el error es contraseña incorrecta o usuario inexistente (mensaje genérico "Credenciales inválidas").

---

## 3. Autorización

- Los permisos se comprueban **en el servidor** en cada operación protegida (middleware + controlador/servicio).
- El backend verifica rol y permiso antes de ejecutar la operación (ej. solo `administrador` puede gestionar usuarios/areas/mesas/productos; solo `mesero` puede registrar pedidos; solo `cocinero` y `administrador` pueden cambiar estado de pedidos; solo el dueño de una reserva o un administrador puede cancelarla).
- Nunca se confía en botones ocultos, rutas del frontend ni variables manipulables por el cliente.
- Impedir acceso a recursos ajenos mediante ID manipulado: al consultar/cancelar una reserva (`/api/reservas/{id}`), el servidor verifica que el usuario autenticado sea el dueño de la reserva o tenga rol `administrador`.
- Las operaciones administrativas cuentan con autorización real en el servidor (middleware `EsAdministrador`).

---

## 4. Protección frente a ataques y vulnerabilidades

| Ataque / vulnerabilidad | Medida planificada |
|-------------------------|--------------------|
| Inyección SQL | Consultas preparadas con PDO en todas las consultas. Nunca concatenar datos de usuario en SQL. |
| Cross-Site Scripting (XSS) | Escape de salida según contexto (HTML) al renderizar datos de usuario; `htmlspecialchars()` con `ENT_QUOTES | ENT_HTML5` y `UTF-8`. Validación de entrada no sustituye al escape de salida. |
| Cross-Site Request Forgery (CSRF) | Token CSRF en formularios que mutan estado (ver cookies/sesión). Verificación en servidor. Las peticiones fetch a la API usan la cookie de sesión; se evalúa SameSite + token CSRF para endpoints sensibles. |
| Falsificación de solicitudes / manipulación de parámetros | Validar y sanitizar toda entrada en servidor. No confiar en `id`, `estado`, `precio` etc. enviados por el cliente. |
| Control de acceso incorrecto | Autorización en servidor por rol y propiedad del recurso (ver sección 3). |
| Fuerza bruta y abuso de endpoints | Rate limiting Token Bucket (sección 5), bloqueo de intentos de login, mensajes genéricos. |
| Exposición de información sensible | Mensajes de error genéricos al usuario; logs internos sin secretos ni contraseñas; no mostrar consultas SQL ni stack traces al cliente. |
| Configuraciones inseguras | `display_errors=Off` en producción; `error_log` a archivo; secretos solo en variables de entorno; `.gitignore` correcto. |
| Subida de archivos maliciosos | Solo si se aprueba la mejora de subida de imágenes: validar extensión, MIME, tamaño, renombrar archivo, almacenar fuera de la raíz web, no ejecutar como PHP. |
| Fuga de secretos | Variables de entorno, `.env` ignorado, `.env.example` sin secretos, nunca en logs ni respuestas HTTP. |
| Errores que revelen infraestructura | Manejador centralizado de errores que devuelve respuestas HTTP estandarizadas sin detalles internos. |

---

## 5. Rate limiting mediante Token Bucket

**Algoritmo:** Token Bucket implementado en PHP (middleware `RateLimiter`).

### Parámetros
| Parámetro | Valor propuesto | Descripción |
|-----------|-----------------|-------------|
| Capacidad máxima del depósito | 60 tokens | Tokens máximos acumulados por cliente/endpoint. |
| Velocidad de reposición | 1 token por segundo | Un token se añade cada segundo, hasta la capacidad. |
| Costo por petición | 1 token | Cada petición consume 1 token. |

### Identificación del cliente y de los endpoints
- Cliente: dirección IP (o identificador de sesión autenticada cuando exista). Se combina IP + endpoint para evitar que un cliente agote el depósito de todos los endpoints.
- Endpoint: ruta + método (ej. `POST /api/login`, `POST /api/reservas`, `POST /api/pedidos`, `GET /api/mesas`).

### Límites diferenciados (valores propuestos, pendiente de ajuste en pruebas)
| Endpoint / operación | Capacidad | Reposición | Razón |
|----------------------|-----------|------------|-------|
| `POST /api/login` | 5 | 1 por 10 segundos | Endpoint sensible (fuerza bruta). Muy restrictivo. |
| `POST /api/registro` | 5 | 1 por 10 segundos | Evitar creación masiva de cuentas. |
| `POST /api/reservas` (crear) | 20 | 1 por segundo | Operación sensible de negocio; evitar reservas masivas. |
| `POST /api/pedidos` (crear) | 20 | 1 por segundo | Operación sensible de negocio. |
| `POST /api/reservas/{id}/cancelar` | 20 | 1 por segundo | Operación sensible. |
| `GET /api/mesas`, `GET /api/disponibilidad` | 60 | 1 por segundo | Lectura, menos restrictivo. |
| Resto de endpoints API | 60 | 1 por segundo | Límite general. |

### Comportamiento al superar el límite
- Se devuelve **HTTP 429 Demasiadas solicitudes**.
- Cuerpo JSON: `{"exito": false, "codigo": 429, "mensaje": "Demasiadas solicitudes. Intente de nuevo más tarde."}`.
- Cabecera `Retry-After` con segundos estimados hasta que haya un token disponible.
- No se ejecuta la operación protegida.

### Almacenamiento del estado del depósito
- Entorno único (XAMPP / un contenedor): almacenamiento en memoria (array de PHP) o en archivo/redis si se requiere persistencia entre requests. Dado que PHP no comparte memoria entre requests por defecto, se usa un almacenamiento persistente ligero (archivo JSON bloqueado o base de datos `rate_limit_state`) para que el depósito persista entre peticiones del mismo cliente.
- Se documenta la limitación: en entornos con múltiples servidores sin almacenamiento compartido, el rate limiter es por-instancia (no global). Alternativa realista: almacenar el estado en la base de datos compartida (tabla `rate_limit_state`) o usar Redis si está disponible.

### Tratamiento de solicitudes simultáneas
- Al consultar y consumir un token se usa transacción o bloqueo sobre la fila de estado del depósito (tabla `rate_limit_state`) para evitar condiciones de carrera (dos peticiones simultáneas del mismo cliente leen el mismo saldo y ambas consumen).

### Límites específicos contra evasión
- Límite por IP y por endpoint combinados.
- Límite más estricto para endpoints de autenticación y reservas/pedidos.
- No se expone la lógica del depósito al cliente (no se envían tokens restantes salvo `Retry-After`).
- Los límites se aplican en servidor; no hay rate limiting solo en frontend.

---

## 6. Cookies y consentimiento

### Cookies utilizadas
| Cookie | Finalidad | Duración | Alcance | Seguridad |
|--------|-----------|----------|---------|-----------|
| Sesión PHP (`PHPSESSID` o nombre configurado) | Mantener la sesión de usuario autenticado | Hasta cierre del navegador / expiración de sesión | path=/ | `HttpOnly`, `Secure` (HTTPS), `SameSite=Lax` |
| (Opcional) Preferencias de consentimiento de cookies | Recordar elección del usuario sobre cookies opcionales | 12 meses | path=/ | `HttpOnly`, `SameSite=Lax` |

### Cookies propias y de terceros
- **Cookies propias:** solo la de sesión y, opcionalmente, la de preferencias de consentimiento.
- **Cookies de terceros:** ninguna por defecto. Si se aprueba una mejora que las requiera (analítica, etc.), se documentan y se bloquean hasta obtener consentimiento.

### Consentimiento
- Las cookies estrictamente necesarias (sesión) no requieren consentimiento previo para el funcionamiento, pero se informa al usuario.
- Las cookies opcionales (si las hubiera) no se cargan antes de obtener el consentimiento exigible.
- Banner de consentimiento cuando corresponda (no meramente decorativo): informa finalidad, permite aceptar/rechazar, guarda preferencia.
- No se afirma que existe consentimiento si el usuario no lo otorgó.

### Política de cookies
Se redacta una política acorde a las tecnologías realmente utilizadas (sin inventar servicios de seguimiento ni cláusulas ajenas al sistema). Incluye cómo modificar o retirar el consentimiento.

---

## 7. Secretos y variables de entorno

- No se incluyen contraseñas, claves JWT, credenciales de BD ni secretos en el código.
- Se usan variables de entorno y un archivo `.env` local ignorado por Git.
- Se incluye solo `.env.example` sin secretos reales (ejemplo de claves y estructura).
- `.gitignore` excluye `.env`, `vendor/`, `documentacion_desarrollo/`, `uploads/`, logs, etc.
- Nunca se muestran secretos en logs, mensajes de error ni respuestas HTTP.
- Manejo de credenciales: desarrollo (`.env` local), pruebas (`.env` de pruebas), producción (secretos del servidor/hosting o GitHub Secrets para CI/CD).

---

## 8. Privacidad y protección de datos

- Datos personales recogidos: nombre, email, contraseña (hasheada), historial de reservas y pedidos del usuario.
- Los datos se usan únicamente para el funcionamiento del sistema (autenticación, reservas, pedidos, administración).
- No se comparten con terceros (sin integraciones de terceros por defecto).
- El usuario puede solicitar acceso, modificación o eliminación de sus datos (derechos ARCO), según la normativa aplicable.
- No se incluyen datos personales reales en documentación ni repositorio.

---

## 9. Decisiones pendientes

| # | Decisión | Opciones | Recomendación actual |
|---|----------|----------|----------------------|
| SEG-01 | JWT / access / refresh / ID tokens | Usar / no usar por ahora | No usar por ahora (sesión de cookie). Reevaluar si se exige API stateless. |
| SEG-02 | CSRF token además de SameSite | Sí / no | Evaluar implementar token CSRF para endpoints que mutan estado. |
| SEG-03 | Almacenamiento del rate limiter | Archivo / BD / Redis | BD compartida (`rate_limit_state`) para que funcione en Docker y hosting. |
| SEG-04 | Servicio de correo para recuperación | Sí / no | Mejora opcional; si no hay servicio, se documenta el bloqueo y no se simula. |
