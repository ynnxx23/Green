# 07. Git, CI/CD y Docker — Sistema de gestion de un restaurante

> **Estado:** borrador de planificacion (etapa 1 - sin programar).  
> **Idioma:** espanol.

---

## 1. Flujo de ramas (Git y GitHub)

| Rama | Propósito | Protección | ¿Quién puede subir directamente? |
|------|-----------|------------|----------------------------------|
| `main` | Versión estable y validada. Solo código que ha pasado pruebas y revisión. | Sí (requiere PR y aprobación) | Nadie directamente; solo mediante pull request aprobado y merge desde `develop` o ramas de release/hotfix. |
| `develop` | Integración de funcionalidades terminadas y probadas. Rama principal de desarrollo. | Sí (requiere PR) | Mediante pull request desde ramas de funcionalidad. |
| `feature/nombre-funcionalidad` | Nueva funcionalidad o módulo. Se crea desde `develop` y se fusiona a `develop`. | No | El autor, mediante PR a `develop`. |
| `fix/nombre-correccion` | Corrección de error. Se crea desde `develop` (o `main` si es hotfix) y se fusiona al destino correspondiente. | No | El autor, mediante PR. |
| `mantenimiento/nombre-tarea` | Tareas de mantenimiento (dependencias, configuración, documentación versionada, Docker, CI). | No | El autor, mediante PR a `develop`. |

**Reglas de fusión:**
- Ninguna rama se fusiona a `develop` o `main` sin pull request y revisión por al menos otro integrante (o auto-revisión documentada si es un solo desarrollador, dejando constancia).
- Antes de fusionar a `develop`, la rama debe pasar las verificaciones de GitHub Actions (CI).
- `develop` se fusiona a `main` solo cuando se considera una versión estable lista para entregar/defender.
- Los mensajes de commit deben seguir la convención definida (sección 2).
- No se suben secretos, archivos `.env` reales, `documentacion_desarrollo/` ni dependencias instaladas.

---

## 2. Convenciones de commits

Formato: `tipo(ámbito): descripción`

**Tipos permitidos:**
- `feat`: nueva funcionalidad.
- `fix`: corrección de error.
- `docs`: cambios en documentación (solo archivos versionados en `documentacion/`, no la interna ignorada).
- `style`: formato, espacios, punto y coma (sin cambios de lógica).
- `refactor`: reestructuración sin cambiar comportamiento.
- `test`: agregar o corregir pruebas.
- `chore`: tareas de mantenimiento, dependencias, configuración, Docker, CI.
- `seguridad`: parche o mejora de seguridad (autenticación, rate limiting, validaciones).

**Ámbito (ámbito):** módulo afectado (ej. `auth`, `reservas`, `mesas`, `pedidos`, `bd`, `docker`, `ci`, `seguridad`).

**Ejemplos:**
- `feat(auth): agregar registro de usuario con hash bcrypt`
- `fix(reservas): bloquear solapamiento de horarios en misma mesa`
- `docs(bd): agregar diccionario de datos inicial`
- `chore(docker): agregar docker-compose.yml y Dockerfile`
- `seguridad(auth): agregar rate limiting en endpoint de login`

**Reglas:**
- Descripción en español, iniciando con verbo en infinitivo, sin mayúscula inicial ni punto final.
- Máximo 50 caracteres en el asunto (asunto claro y conciso).
- Cuerpo opcional para explicar el "por qué" cuando no sea obvio.
- No se mezclan responsabilidades ajenas en un solo commit.

---

## 3. Plan de commits por etapas (hoja de ruta de commits)

Cada commit indica: mensaje propuesto, objetivo, archivos/módulos afectados, dependencias previas y criterio de aceptación.

### Etapa 1 — Estructura base y configuración
| # | Commit propuesto | Objetivo | Archivos/módulos afectados | Dependencias previas | Criterio de aceptación |
|---|------------------|----------|----------------------------|----------------------|------------------------|
| C1 | `chore(estructura): crear estructura de carpetas inicial del proyecto` | Definir carpetas `public/`, `src/`, `migrations/`, `documentacion/`, `documentacion_desarrollo/` | Estructura de directorios, `.gitignore` | Ninguna | Estructura creada y `.gitignore` excluye secretos y documentación interna. |
| C2 | `chore(config): agregar archivo de ejemplo de variables de entorno` | Definir variables de entorno sin secretos reales | `.env.example`, `documentacion/variables_configuracion.md` | C1 | `.env.example` versionado sin credenciales; documento de variables creado. |
| C3 | `chore(docker): agregar Dockerfile y docker-compose.yml` | Definir servicios PHP+Apache y MariaDB con red interna y volumen persistente | `Dockerfile`, `docker-compose.yml` | C1 | `docker-compose up` levanta servicios y se saludan entre sí (health check). |
| C4 | `feat(config): implementar carga de configuración desde variables de entorno` | Clase `Config` que lee entorno y evita valores hardcodeados | `src/Config.php` | C2 | Config se lee de `.env`; no hay credenciales hardcodeadas. |
| C5 | `feat(bd): implementar conexión PDO a la base de datos` | Conexión segura y reutilizable mediante PDO singleton | `src/Database/Conexion.php` | C4 | Conexión establece con MariaDB en XAMPP y Docker. |

### Etapa 2 — Modelo de datos y migraciones
| # | Commit propuesto | Objetivo | Archivos/módulos afectados | Dependencias previas | Criterio de aceptación |
|---|------------------|----------|----------------------------|----------------------|------------------------|
| C6 | `feat(bd): crear migración 001 con esquema inicial` | Crear tablas roles, usuarios, areas, mesas, horarios, bloques, reservas, pedidos, detalle_pedidos, productos, intentos_login | `migrations/001_create_schema.sql` | C5 | Migración ejecuta sin errores en MariaDB y MySQL; esquema coincide con 03_base_de_datos.md. |
| C7 | `feat(bd): agregar tabla de control de migraciones aplicadas` | Registrar qué migraciones se han aplicado para no reejecutar | `migrations/002_migraciones_control.sql` (o dentro de 001) | C6 | Tabla `migraciones_aplicadas` existe y se consulta antes de aplicar. |
| C8 | `feat(bd): crear datos iniciales (roles y usuario admin)` | Semilla mínima de arranque | `migrations/003_datos_iniciales.sql` | C6, C7 | Roles `cliente`, `mesero`, `cocinero`, `administrador` creados; usuario admin con contraseña bcrypt. |

### Etapa 3 — Autenticación y sesiones
| # | Commit propuesto | Objetivo | Archivos/módulos afectados | Dependencias previas | Criterio de aceptación |
|---|------------------|----------|----------------------------|----------------------|------------------------|
| C9 | `feat(auth): crear modelo Usuario y Rol` | Entidades de usuario y rol | `src/Model/Usuario.php`, `src/Model/Rol.php` | C8 | Clases POO con atributos en español y getters/setters. |
| C10 | `feat(auth): crear DAO de usuarios` | Acceso a datos de usuarios con consultas preparadas | `src/Dao/UsuarioDao.php` | C9 | `buscarPorEmail`, `crear`, `existeEmail` funcionan con PDO preparado. |
| C11 | `feat(auth): implementar registro con contraseña bcrypt` | Registro seguro de usuarios | `src/Service/ServicioDeAutenticacion.php`, `src/Controller/AuthController.php` | C10 | Contraseña se hashea con `password_hash`; email único validado. |
| C12 | `feat(auth): implementar inicio y cierre de sesión` | Sesión de PHP con cookie segura | `src/Service/ServicioDeAutenticacion.php`, `src/Middleware/Auth.php` | C11 | Login valida con `password_verify`; cookie `HttpOnly`, `Secure` (HTTPS), `SameSite=Lax`. |
| C13 | `seguridad(auth): agregar rate limiting en login` | Token Bucket en endpoint de login | `src/Middleware/RateLimiter.php`, `src/Dao/IntentosLoginDao.php` | C12 | Endpoint de login devuelve 429 al superar límite; tabla `intentos_login` registra intentos. |

### Etapa 4 — Areas, mesas y disponibilidad
| # | Commit propuesto | Objetivo | Archivos/módulos afectados | Dependencias previas | Criterio de aceptación |
|---|------------------|----------|----------------------------|----------------------|------------------------|
| C14 | `feat(mesas): crear modelos Area y Mesa` | Entidades de area y mesa | `src/Model/Area.php`, `src/Model/Mesa.php` | C8 | Clases POO con atributos en español. |
| C15 | `feat(mesas): crear DAO y servicio de mesas` | Acceso a datos y lógica de mesas | `src/Dao/MesaDao.php`, `src/Service/ServicioDeMesas.php` | C14 | Listar mesas por area; filtrar activas. |
| C16 | `feat(reservas): crear modelo Reserva y DAO` | Entidad y acceso a datos de reservas | `src/Model/Reserva.php`, `src/Dao/ReservaDao.php` | C8, C15 | Consultar reservas por mesa, fecha y usuario. |
| C17 | `feat(reservas): implementar consulta de disponibilidad` | Calcular horarios libres considerando reservas, bloques y horario de funcionamiento | `src/Service/ServicioDeDisponibilidad.php` | C15, C16 | Devuelve solo horarios disponibles; no muestra ocupados ni bloqueados. |

### Etapa 5 — Reservas (crear, consultar, cancelar, estados)
| # | Commit propuesto | Objetivo | Archivos/módulos afectados | Dependencias previas | Criterio de aceptación |
|---|------------------|----------|----------------------------|----------------------|------------------------|
| C18 | `feat(reservas): agregar validaciones de reglas de negocio` | Validar solapamiento, horario de funcionamiento, hora fin > inicio | `src/Validator/ValidadorDeReserva.php` | C17 | Rechaza reservas inválidas con mensajes claros. |
| C19 | `feat(reservas): implementar creación de reserva con transacción y bloqueo` | Evitar reservas duplicadas bajo concurrencia | `src/Service/ServicioDeReservas.php`, `src/Dao/ReservaDao.php` | C17, C18 | Transacción + `FOR UPDATE`; solo una reserva confirmada por mesa/intervalo (caso C-01). |
| C20 | `feat(reservas): crear endpoints de reservas` | Endpoints crear, listar propias, cancelar | `src/Controller/ReservaController.php`, rutas | C19 | Endpoints devuelven JSON estándar y códigos HTTP correctos. |
| C21 | `feat(reservas): implementar cancelación y cambio de estado` | Cancelar reserva propia; liberar mesa; estados válidos | `src/Service/ServicioDeReservas.php` | C20 | Reserva pasa a `cancelada`; mesa vuelve a estar disponible. |

### Etapa 6 — Pedidos y productos
| # | Commit propuesto | Objetivo | Archivos/módulos afectados | Dependencias previas | Criterio de aceptación |
|---|------------------|----------|----------------------------|----------------------|------------------------|
| C22 | `feat(pedidos): crear modelos Pedido, Producto y DetallePedido` | Entidades de pedido, producto y detalle | `src/Model/Pedido.php`, `src/Model/Producto.php`, `src/Model/DetallePedido.php` | C8 | Clases POO con atributos en español. |
| C23 | `feat(pedidos): crear DAO y servicio de pedidos` | Acceso a datos y lógica de pedidos | `src/Dao/PedidoDao.php`, `src/Dao/DetallePedidoDao.php`, `src/Service/ServicioDePedidos.php` | C22, C15 | Crear pedido asociado a mesa y mesero; listar pedidos por estado. |
| C24 | `feat(pedidos): crear DAO y servicio de productos` | Acceso a datos y lógica de productos del menu | `src/Dao/ProductoDao.php`, `src/Service/ServicioDeProductos.php` | C22 | Listar productos activos; crear/editar/desactivar productos. |
| C25 | `feat(pedidos): crear endpoints de pedidos y productos` | Endpoints registrar pedido, listar pedidos, cambiar estado, gestionar productos | `src/Controller/PedidoController.php`, `src/Controller/ProductoController.php`, rutas | C23, C24 | Endpoints devuelven JSON estándar; autorización por rol (mesero, cocinero, administrador). |

### Etapa 7 — Administración y autorización
| # | Commit propuesto | Objetivo | Archivos/módulos afectados | Dependencias previas | Criterio de aceptación |
|---|------------------|----------|----------------------------|----------------------|------------------------|
| C26 | `seguridad(auth): implementar middleware de autorización por rol` | Verificar rol y permisos en servidor (cliente, mesero, cocinero, administrador) | `src/Middleware/Auth.php` (extender) | C12 | Endpoints admin requieren rol `administrador`; mesero/cocinero ven sus operaciones (403 si no). |
| C27 | `feat(usuarios): endpoints de gestión de usuarios y roles` | CRUD de usuarios para administrador | `src/Controller/UsuarioController.php` | C26 | Solo admin puede crear/editar/desactivar usuarios. |
| C28 | `feat(areas): endpoints de gestión de areas y mesas` | CRUD de areas y mesas para administrador | `src/Controller/AreaController.php`, `src/Controller/MesaController.php` | C26 | Solo admin puede gestionar areas/mesas. |
| C29 | `feat(mesas): endpoints administrativos de reservas` | Listar todas las reservas, cambiar estado de reservas ajenas | `src/Controller/ReservaController.php` | C20, C26 | Admin puede ver y cambiar estado de cualquier reserva. |

### Etapa 8 — Interfaz y frontend dinámico
| # | Commit propuesto | Objetivo | Archivos/módulos afectados | Dependencias previas | Criterio de aceptación |
|---|------------------|----------|----------------------------|----------------------|------------------------|
| C30 | `feat(ui): crear design tokens y CSS base` | Paleta, tipografía, espaciado, radios, sombras | `public/assets/css/design-tokens.css`, `base.css` | Ninguna (puede ir temprano) | Tokens CSS definidos y reutilizados. |
| C31 | `feat(ui): crear componentes compartidos` | Botones, inputs, alertas, tarjeta de mesa, tarjeta de pedido, modal, spinner | `public/assets/css/components.css` | C30 | Componentes respetan design tokens y estados (carga, error, éxito). |
| C32 | `feat(ui): implementar pantallas de autenticación` | Login, registro, recuperación | Vistas HTML/JS, `public/assets/js/auth.js` | C12, C31 | Pantallas funcionales con validación inmediata y servidor. |
| C33 | `feat(ui): implementar consulta de areas, mesas y disponibilidad` | Listar areas/mesas; seleccionar fecha y ver horarios libres | Vistas, `public/assets/js/mesas.js`, `disponibilidad.js` | C17, C31 | Muestra disponibilidad sin ambigüedades; responsive. |
| C34 | `feat(ui): implementar creación y gestión de reservas` | Formulario de reserva, historial, cancelar | `public/assets/js/reservas.js`, vistas | C20, C21, C33 | Crear, ver y cancelar reservas; previene envíos duplicados; conserva datos ante error. |
| C35 | `feat(ui): implementar registro y gestión de pedidos` | Formulario de pedido, lista de productos, cambio de estado | `public/assets/js/pedidos.js`, vistas | C23, C24, C31 | Registrar pedido, ver pedidos por estado, cambiar estado (mesero/cocinero/admin). |
| C36 | `feat(ui): implementar panel de administración` | Gestión usuarios, areas, mesas, productos | Vistas admin, `public/assets/js/admin.js` | C27, C28, C29, C31 | Acceso solo para administrador; operaciones protegidas en servidor. |
| C37 | `chore(ui): revisar accesibilidad y responsive` | Teclado, foco, etiquetas, contraste, puntos de quiebre | Todas las vistas y CSS | C30-C36 | Pasa auditoría de accesibilidad (WCAG 2.2 AA referencial) y pruebas en móvil/tablet/escritorio. |

### Etapa 9 — Pruebas, CI/CD y ajuste fino
| # | Commit propuesto | Objetivo | Archivos/módulos afectados | Dependencias previas | Criterio de aceptación |
|---|------------------|----------|----------------------------|----------------------|------------------------|
| C38 | `test(unidades): agregar pruebas unitarias de lógica de negocio` | Pruebas de disponibilidad, solapamiento, validadores | `tests/` | C17-C25 | Pruebas unitarias pasan. |
| C39 | `test(integracion): agregar pruebas de integración con BD` | Pruebas de transacciones, bloqueo de filas, FK | `tests/` | C6-C25 | Pruebas de integración pasan en BD de pruebas. |
| C40 | `test(endpoints): agregar pruebas de endpoints API` | Códigos HTTP, autenticación, autorización | `tests/` | C12-C29 | Pruebas de endpoints pasan. |
| C41 | `chore(ci): agregar GitHub Actions para integración continua` | Lint PHP/JS, pruebas, build Docker en PR y merge | `.github/workflows/ci.yml` | C1-C40 | CI pasa en PR a `develop` y `main`. |
| C42 | `docs(versionada): agregar guías de instalación y despliegue` | Guías XAMPP, Docker, hosting; tabla comparativa; variables | `documentacion/` | C1-C41 | Guías completas y sin secretos reales. |
| C43 | `chore(release): fusionar develop a main y etiquetar versión 1.0` | Versión estable lista para entregar | `main`, etiqueta `v1.0.0` | Todas las previas | `main` estable; sistema funcional listo para defensa. |

**Nota:** El orden de commits respeta dependencias reales (no se puede crear pedidos sin mesas, ni autenticar sin usuarios, etc.).

---

## 4. GitHub Actions (integración continua)

**Disparadores:**
- Pull request hacia `develop` o `main`.
- Push a `develop` y `main`.

**Trabajos y pasos propuestos:**

1. **Validación de sintaxis PHP** (`php -l` en archivos cambiados).
2. **Verificación de sintaxis y calidad de JavaScript** (node + linter si está disponible, o al menos `node --check`).
3. **Ejecución de pruebas automatizadas** (unitarias, integración con BD de pruebas en servicio MariaDB del contenedor, endpoints).
4. **Comprobación de migraciones y estructura de la base de datos** (aplicar migraciones en BD limpia y verificar que no fallen).
5. **Verificación de dependencias** (revisar que no se agreguen dependencias no autorizadas; validar `composer.json` si existe).
6. **Comprobaciones de seguridad** (buscar secretos hardcodeados con `grep`/`truffleHog` simple; verificar que `.env` real no esté versionado).
7. **Construcción o validación de imágenes Docker** (`docker compose config` para validar YAML; `docker build` para validar Dockerfile).

**Criterios para permitir o bloquear la integración:**
- Bloquear si falla cualquier paso de sintaxis, pruebas, migraciones o build de Docker.
- Bloquear si se detectan secretos hardcodeados.
- Permitir solo cuando todos los pasos pasen y el PR tenga revisión aprobada.

**Diferenciación CI vs CD:**
- **CI (integrada):** se ejecuta en cada PR y push; valida calidad, pruebas y seguridad. No despliega.
- **CD (despliegue continuo):** NO configurado automáticamente a producción. Cualquier despliegue a producción requiere entorno definido, credenciales seguras, aprobación explícita y condiciones de seguridad documentadas. El despliegue es manual y controlado.

**Secretos de GitHub Actions:**
- Gestionados mediante los secretos cifrados de GitHub Settings > Secrets and variables > Actions.
- Nunca incluidos en los archivos del repositorio ni en los workflows en texto plano.

---

## 5. Docker y Docker Compose

### Servicios necesarios
| Servicio | Responsabilidad | Imagen base | Puerto expuesto | Volumen persistente |
|----------|-----------------|-------------|-----------------|---------------------|
| `web` (PHP + Apache) | Ejecutar PHP, servir la aplicación, exponer endpoints y vistas | `php:8.2-apache` (o versión compatible con XAMPP/producción) | 8000 (configurable) | Código montado (bind mount en dev) o copiado (prod) |
| `mariadb` | Base de datos | `mariadb:10.11` (o MySQL 8.0 compatible) | 3306 solo en red interna (no expuesto al host salvo que se configure) | `mariadb_data` en `/var/lib/mysql` |

### Redes y dependencias
- Red interna `restaurante_net` entre `web` y `mariadb`.
- `web` depende de `mariadb` (`depends_on` con `condition: service_healthy`).
- `mariadb` tiene health check (`mysqladmin ping` o `HEALTHCHECK`).

### Variables de entorno
Definidas en `.env` (local, ignorado) y referenciadas en `docker-compose.yml`:
- `DB_HOST=mariadb` (nombre del servicio en Docker; en XAMPP/hosting es la dirección configurada).
- `DB_PORT=3306`
- `DB_NAME=restaurante`
- `DB_USER=restaurante`
- `DB_PASSWORD` (secreto, solo en `.env`)
- `APP_ENV=development`
- `APP_URL=http://localhost:8000`
- `SESSION_COOKIE_SECURE=0` en desarrollo (HTTP local), `1` en producción (HTTPS)

### Persistencia y recuperación
- Volumen `mariadb_data` para datos de MariaDB (no se pierden al eliminar el contenedor).
- Código de la app se monta o se copia según entorno (bind mount en desarrollo para editar en caliente; copia en producción).
- Migraciones se aplican al iniciar o mediante script explícito; nunca se borran datos existentes al iniciar.

### Puertos
- `web`: 8000 -> 80 (configurable via variable).
- `mariadb`: NO expuesto al host por defecto (solo red interna). Si se necesita acceso desde herramientas de administración, se expone 3306 solo en desarrollo y se documenta.

### Logs
- Logs de PHP/Apache a stdout/stderr (capturados por Docker).
- Logs de aplicación a archivo dentro de volumen o stdout, sin secretos.

### Procedimientos
- Inicio: `docker compose up -d` (desde raíz del proyecto).
- Detención: `docker compose down`.
- Recuperación: `docker compose down` + `docker compose up -d`; los datos persisten en el volumen.
- Verificación: acceder a `APP_URL` y comprobar que responde; comprobar que `web` se conecta a `mariadb`.

### Diferencias entre entornos
| Aspecto | Desarrollo (Docker) | Desarrollo (XAMPP) | Pruebas / Producción |
|---------|---------------------|--------------------|----------------------|
| Servidor web | Contenedor PHP+Apache | Apache de XAMPP | Apache del hosting/servidor |
| BD host | `mariadb` (servicio Docker) | `127.0.0.1` o `localhost` | Dirección configurada del hosting |
| `SESSION_COOKIE_SECURE` | 0 (HTTP local) | 0 (HTTP local) | 1 (HTTPS) |
| Volúmenes | Sí (datos de BD) | N/A (BD local de XAMPP) | Sí (datos de BD del servidor) |
| Código | Bind mount o copia | Carpeta en `htdocs` | Desplegado en servidor |

---

## 6. Configuración de entornos

| Variable | Desarrollo XAMPP | Desarrollo Docker | Pruebas | Producción |
|----------|------------------|-------------------|---------|------------|
| `APP_ENV` | `development` | `development` | `testing` | `production` |
| `APP_URL` | `http://localhost/restaurante/public` | `http://localhost:8000` | `http://tests.local` | `https://dominio.real` |
| `DB_HOST` | `127.0.0.1` | `mariadb` | `db-tests` | `direccion-hosting` |
| `DB_PORT` | `3306` | `3306` | `3306` | `3306` |
| `DB_NAME` | `restaurante_dev` | `restaurante` | `restaurante_test` | `restaurante_prod` |
| `DB_USER` | `root` (o usuario XAMPP) | `restaurante` | `restaurante_test` | `usuario_prod` |
| `DB_PASSWORD` | (vacío o XAMPP) | secreto | secreto | secreto |
| `SESSION_COOKIE_SECURE` | `0` | `0` | `0` | `1` |
| `SESSION_COOKIE_SAMESITE` | `Lax` | `Lax` | `Lax` | `Strict` o `Lax` |

Los valores reales y secretos viven en `.env` (ignorado por Git). Solo `.env.example` va al repositorio.

---

## 7. Procedimientos de ejecución y validación

### Desde cero (resumen; detalles en `documentacion/`)
1. Clonar repositorio.
2. Copiar `.env.example` a `.env` y completar valores reales (sin subir `.env`).
3. Elegir entorno:
   - **XAMPP:** colocar proyecto en `htdocs`, crear BD en phpMyAdmin/MySQL, ejecutar migraciones, acceder vía navegador.
   - **Docker:** `docker compose up -d`, aplicar migraciones, acceder a `APP_URL`.
4. Validar: login, registro, consulta de mesas, crear reserva, registrar pedido, verificar que no hay superposiciones, panel de admin.

### Validación de portabilidad
- Misma lógica en XAMPP, Docker y hosting.
- Migraciones producen esquema compatible en MariaDB y MySQL.
- Autenticación, permisos y reservas simultáneas funcionan en cada modalidad.
- Ningún entorno depende de secretos o archivos exclusivos de otro.
