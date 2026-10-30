# 08. Hoja de ruta — Sistema de gestion de un restaurante

> **Estado:** borrador de planificacion (etapa 1 - sin programar).  
> **Idioma:** espanol.  
> **Orden recomendado:** las fases se ejecutan en este orden porque cada una depende de la anterior.

---

## 1. Fases de desarrollo ordenadas por dependencias

| Fase | Nombre | Objetivo | Depende de |
|------|--------|----------|------------|
| F1 | Estructura base y configuración | Crear carpetas, `.gitignore`, `.env.example`, configuración por entorno, Docker base | Ninguna |
| F2 | Modelo de datos y migraciones | Esquema de BD, tabla de control de migraciones, datos iniciales (roles, admin) | F1 |
| F3 | Autenticación y sesiones | Modelo/DAO de usuarios, registro con bcrypt, login/logout, sesión segura, rate limiting en login | F2 |
| F4 | Areas, mesas y disponibilidad | Modelos/DAO de area y mesa, consulta de disponibilidad de mesas | F2 |
| F5 | Reservas (crear, consultar, cancelar, estados) | Validadores, creación con transacción y bloqueo, cancelación, historial, estados | F3, F4 |
| F6 | Pedidos y productos | Modelos/DAO de pedido, producto, detalle; registro de pedido; cambio de estado; gestión de productos | F3, F4 |
| F7 | Administración y autorización | Middleware de roles, CRUD de usuarios/areas/mesas/productos, gestión admin de reservas y pedidos | F3, F5, F6 |
| F8 | Interfaz y frontend dinámico | CSS design tokens, componentes, pantallas de auth, reservas, pedidos, admin, accesibilidad y responsive | F3, F5, F6, F7 |
| F9 | Pruebas, CI/CD y ajuste fino | Pruebas unitarias/integración/endpoints, GitHub Actions, guías de instalación, validación final | Todas las previas |

---

## 2. Objetivos y tareas de cada fase

### Fase F1 — Estructura base y configuración
**Objetivo:** Tener el esqueleto del proyecto listo y configuración separada del código.

Tareas:
1. Crear estructura de carpetas (`public/`, `src/`, `migrations/`, `documentacion/`, `documentacion_desarrollo/`).
2. Crear `.gitignore` (excluir `.env`, `vendor/`, `documentacion_desarrollo/`, `uploads/`, logs).
3. Crear `.env.example` sin secretos reales.
4. Crear `documentacion/variables_configuracion.md`.
5. Crear `Dockerfile` y `docker-compose.yml`.
6. Implementar `src/Config.php` (carga de variables de entorno).
7. Implementar `src/Database/Conexion.php` (conexión PDO singleton).

**Archivos y módulos:** `src/Config.php`, `src/Database/Conexion.php`, `.env.example`, `.gitignore`, `Dockerfile`, `docker-compose.yml`, carpetas base.

**Prerrequisitos:** XAMPP instalado o Docker Desktop instalado (según entorno elegido).

**Criterio para completar la fase:**
- `docker compose up -d` levanta `web` y `mariadb` y se saludan entre sí.
- O en XAMPP: Apache y MariaDB funcionan y `src/Database/Conexion.php` se conecta.
- No hay credenciales hardcodeadas ni secretos versionados.

---

### Fase F2 — Modelo de datos y migraciones
**Objetivo:** Definir y versionar el esquema de base de datos.

Tareas:
1. Escribir migración `001_create_schema.sql` con todas las tablas (roles, usuarios, areas, mesas, horarios_funcionamiento, bloques_mesa, reservas, pedidos, detalle_pedidos, productos, intentos_login, rate_limit_state).
2. Crear tabla de control `migraciones_aplicadas`.
3. Escribir migración de datos iniciales (roles `cliente`, `mesero`, `cocinero`, `administrador`; usuario administrador inicial con contraseña bcrypt).
4. Verificar compatibilidad MariaDB/MySQL.

**Archivos y módulos:** `migrations/001_create_schema.sql`, `migrations/002_migraciones_control.sql`, `migrations/003_datos_iniciales.sql`.

**Prerrequisitos:** F1 completada (conexión PDO funciona).

**Criterio para completar la fase:**
- La migración se ejecuta sin errores en MariaDB y MySQL.
- El esquema coincide con `03_base_de_datos.md`.
- Las tablas de roles y usuario admin existen.

---

### Fase F3 — Autenticación y sesiones
**Objetivo:** Usuarios puedan registrarse, iniciar sesión y cerrar sesión de forma segura.

Tareas:
1. Crear `src/Model/Usuario.php` y `src/Model/Rol.php`.
2. Crear `src/Dao/UsuarioDao.php` (consultas preparadas: `buscarPorEmail`, `crear`, `existeEmail`).
3. Implementar `src/Service/ServicioDeAutenticacion.php` (registro con `password_hash`, login con `password_verify`).
4. Implementar `src/Controller/AuthController.php` y endpoints de registro/login/logout.
5. Configurar sesión de PHP con cookie `HttpOnly`, `Secure` (HTTPS), `SameSite=Lax`.
6. Implementar `src/Middleware/AuthMiddleware.php` (verificar sesión y rol).
7. Implementar `src/Middleware/RateLimiterMiddleware.php` (Token Bucket en login) y `src/Dao/IntentosLoginDao.php`.
8. Crear pruebas unitarias de hash/verificación de contraseña y validador de email.

**Archivos y módulos:** `src/Model/Usuario.php`, `src/Model/Rol.php`, `src/Dao/UsuarioDao.php`, `src/Service/ServicioDeAutenticacion.php`, `src/Controller/AuthController.php`, `src/Middleware/AuthMiddleware.php`, `src/Middleware/RateLimiterMiddleware.php`, `src/Dao/IntentosLoginDao.php`.

**Prerrequisitos:** F2 (tablas `usuarios`, `roles`, `intentos_login` existen).

**Criterio para completar la fase:**
- Registro almacena contraseña con bcrypt (no texto plano).
- Login con credenciales correctas crea sesión; con incorrectas falla genéricamente.
- Logout invalida sesión y cookie.
- Endpoint de login devuelve 429 al superar el límite de rate limiting.
- Cookie de sesión tiene `HttpOnly`, `SameSite=Lax` (y `Secure` si HTTPS).

---

### Fase F4 — Areas, mesas y disponibilidad
**Objetivo:** Consultar areas, mesas y horarios disponibles.

Tareas:
1. Crear `src/Model/Area.php` y `src/Model/Mesa.php`.
2. Crear `src/Dao/AreaDao.php` y `src/Dao/MesaDao.php`.
3. Implementar `src/Service/ServicioDeMesas.php` y `src/Service/ServicioDeDisponibilidad.php` (cálculo de disponibilidad: horario de funcionamiento, reservas existentes, bloques).
4. Crear endpoints de listado de areas, mesas por area y disponibilidad por fecha.

**Archivos y módulos:** `src/Model/Area.php`, `src/Model/Mesa.php`, `src/Dao/AreaDao.php`, `src/Dao/MesaDao.php`, `src/Service/ServicioDeMesas.php`, `src/Service/ServicioDeDisponibilidad.php`.

**Prerrequisitos:** F2 (tablas `areas`, `mesas`, `horarios_funcionamiento`, `bloques_mesa`, `reservas` existen).

**Criterio para completar la fase:**
- Se listan areas y mesas activas.
- Consulta de disponibilidad devuelve solo horarios libres (no ocupados ni bloqueados).
- Disponibilidad respeta horario de apertura/cierre de el area/mesa.

---

### Fase F5 — Reservas (crear, consultar, cancelar, estados)
**Objetivo:** Crear, ver y cancelar reservas respetando reglas de negocio y concurrencia.

Tareas:
1. Crear `src/Model/Reserva.php` y `src/Dao/ReservaDao.php`.
2. Crear `src/Validator/ValidadorDeReserva.php` (solapamiento, horario de funcionamiento, hora fin > inicio, estado válido).
3. Implementar creación de reserva dentro de transacción con bloqueo de filas (`FOR UPDATE`) para evitar reservas duplicadas bajo concurrencia.
4. Implementar endpoints: crear reserva, listar reservas propias, cancelar reserva propia.
5. Implementar gestión de estados (pendiente, confirmada, cancelada, vencida, completada) con transiciones válidas.

**Archivos y módulos:** `src/Model/Reserva.php`, `src/Dao/ReservaDao.php`, `src/Validator/ValidadorDeReserva.php`, `src/Service/ServicioDeReservas.php`, `src/Controller/ReservaController.php`.

**Prerrequisitos:** F3 (autenticación funciona) y F4 (disponibilidad calculable).

**Criterio para completar la fase:**
- No se crean dos reservas confirmadas superpuestas en la misma mesa (caso C-01).
- Crear reserva valida horario de funcionamiento y hora fin > hora inicio.
- Cancelar reserva libera la mesa.
- Historial de reservas muestra solo las propias del usuario.

---

### Fase F6 — Pedidos y productos
**Objetivo:** Registrar pedidos, controlar su estado y gestionar el menu.

Tareas:
1. Crear `src/Model/Pedido.php`, `src/Model/Producto.php`, `src/Model/DetallePedido.php`.
2. Crear `src/Dao/PedidoDao.php`, `src/Dao/DetallePedidoDao.php`, `src/Dao/ProductoDao.php`.
3. Implementar `src/Service/ServicioDePedidos.php` (crear pedido asociado a mesa y mesero, cambiar estado, validar transiciones).
4. Implementar `src/Service/ServicioDeProductos.php` (listar productos activos, crear/editar/desactivar).
5. Crear endpoints: registrar pedido, listar pedidos (mesero ve los suyos, cocinero/admin ven todos), cambiar estado de pedido, gestionar productos (solo admin).
6. Validar que un pedido pertenece a una mesa y mesero existentes (RB-11).
7. Validar transiciones de estado de pedido (RB-12).

**Archivos y módulos:** `src/Model/Pedido.php`, `src/Model/Producto.php`, `src/Model/DetallePedido.php`, `src/Dao/PedidoDao.php`, `src/Dao/DetallePedidoDao.php`, `src/Dao/ProductoDao.php`, `src/Service/ServicioDePedidos.php`, `src/Service/ServicioDeProductos.php`, `src/Controller/PedidoController.php`, `src/Controller/ProductoController.php`.

**Prerrequisitos:** F3 (autenticación funciona) y F4 (mesas existen).

**Criterio para completar la fase:**
- Se puede crear un pedido asociado a mesa y mesero válidos.
- Un mesero solo puede ver/gestionar sus propios pedidos.
- Un cocinero/administrador puede ver todos los pedidos y cambiar estados.
- Transiciones de estado de pedido respetan `pendiente → en_preparación → listo → entregado`.
- Solo administrador puede gestionar productos.

---

### Fase F7 — Administración y autorización
**Objetivo:** El administrador gestiona usuarios, areas, mesas, productos; autorización real en servidor.

Tareas:
1. Extender `src/Middleware/AuthMiddleware.php` para verificar roles (`cliente`, `mesero`, `cocinero`, `administrador`) en endpoints protegidos.
2. Crear `src/Controller/UsuarioController.php` (CRUD de usuarios, solo admin).
3. Crear `src/Controller/AreaController.php` y `src/Controller/MesaController.php` (CRUD, solo admin).
4. Crear `src/Controller/ProductoController.php` (CRUD de productos, solo admin).
5. Extender `src/Controller/ReservaController.php` con endpoints admin (listar todas, cambiar estado de reserva ajena).
6. Extender `src/Controller/PedidoController.php` con endpoints admin (listar todos, cambiar estado de cualquier pedido).
7. Verificar que un cliente/mesero/cocinero no puede acceder a endpoints de administración (HTTP 403).
8. Verificar que un mesero no puede ver pedidos de otro mesero (403).
9. Verificar que un cliente no puede acceder a reservas ajenas mediante ID manipulado.

**Archivos y módulos:** `src/Middleware/AuthMiddleware.php`, `src/Controller/UsuarioController.php`, `src/Controller/AreaController.php`, `src/Controller/MesaController.php`, `src/Controller/ProductoController.php`, `src/Controller/ReservaController.php` (extensión admin), `src/Controller/PedidoController.php` (extensión admin).

**Prerrequisitos:** F3, F5, F6.

**Criterio para completar la fase:**
- Solo administrador puede gestionar usuarios/areas/mesas/productos (403 para otros roles).
- Mesero no puede ver pedidos de otro mesero (403).
- Cliente no puede ver/cancelar reservas ajenas (403/404).
- Administrador puede cambiar estado de cualquier reserva y cualquier pedido.

---

### Fase F8 — Interfaz y frontend dinámico
**Objetivo:** Interfaz web dinámica, consistente, accesible y responsive.

Tareas:
1. Crear `public/assets/css/design-tokens.css` (paleta, tipografía, espaciado, radios, sombras).
2. Crear `public/assets/css/components.css` (botones, inputs, alertas, tarjeta mesa, tarjeta pedido, modal, spinner).
3. Implementar pantallas de autenticación (login, registro, recuperación) con `public/assets/js/auth.js`.
4. Implementar consulta de areas/mesas y disponibilidad (`public/assets/js/mesas.js`, `disponibilidad.js`).
5. Implementar creación, historial y cancelación de reservas (`public/assets/js/reservas.js`).
6. Implementar registro y gestión de pedidos (`public/assets/js/pedidos.js`).
7. Implementar panel de administración (`public/assets/js/admin.js`).
8. Implementar validación inmediata en cliente + definitiva en servidor; conservación de datos ante error; prevención de envíos duplicados.
9. Revisar accesibilidad (teclado, foco, etiquetas, contraste, semántica, ARIA) y responsive (móvil, tablet, escritorio).

**Archivos y módulos:** `public/assets/css/design-tokens.css`, `public/assets/css/components.css`, `public/assets/js/auth.js`, `mesas.js`, `reservas.js`, `pedidos.js`, `admin.js`, `api.js`, vistas HTML.

**Prerrequisitos:** F3, F5, F6, F7 (endpoints backend disponibles).

**Criterio para completar la fase:**
- Todas las pantallas respetan los design tokens (`04_diseno_y_accesibilidad.md`).
- Navegación completa por teclado; foco visible; etiquetas en todos los campos.
- Funciona en móvil, tablet y escritorio.
- Formularios conservan datos ante error recuperable y previenen envíos duplicados.

---

### Fase F9 — Pruebas, CI/CD y ajuste fino
**Objetivo:** Verificar calidad, seguridad y portabilidad antes de considerar el sistema terminado.

Tareas:
1. Escribir pruebas unitarias (lógica de negocio, validadores).
2. Escribir pruebas de integración con BD (transacciones, bloqueo de filas, claves foráneas).
3. Escribir pruebas de endpoints (códigos HTTP, autenticación, autorización).
4. Ejecutar casos críticos (C-01 a C-15 definidos en `06_pruebas_y_calidad.md`).
5. Configurar GitHub Actions (lint PHP/JS, pruebas, migraciones, seguridad, build Docker).
6. Escribir guías de instalación (XAMPP, Docker, hosting), tabla comparativa, lista de variables, estrategia de migraciones y pruebas de portabilidad en `documentacion/`.
7. Validar portabilidad real en los tres entornos (XAMPP, Docker, hosting).
8. Fusionar `develop` a `main` y etiquetar `v1.0.0`.

**Archivos y módulos:** `tests/`, `.github/workflows/ci.yml`, `documentacion/`.

**Prerrequisitos:** F1 a F8 completadas.

**Criterio para completar la fase:**
- Todos los casos críticos pasan con resultado esperado verificable.
- CI pasa en PR a `develop` y `main`.
- Portabilidad verificada en los tres entornos.
- `main` estable y lista para entrega/defensa.

---

## 3. Matriz de dependencias entre fases

```
F1 --> F2 --> F3 --> F4 --> F5 --> F6 --> F7 --> F8 --> F9
  \                                                            /
   ----------------------------------------------------------
            (F9 depende indirectamente de todas las anteriores)
```

- F2 depende de F1 (conexión BD).
- F3 depende de F2 (tablas de usuarios/roles).
- F4 depende de F2 (tablas de mesas/reservas).
- F5 depende de F3 (autenticación) y F4 (disponibilidad).
- F6 depende de F3 (autenticación/mesero) y F4 (mesas).
- F7 depende de F3 (middleware auth), F5 (reservas) y F6 (pedidos).
- F8 depende de F3, F5, F6, F7 (endpoints backend).
- F9 depende de todo lo anterior.

---

## 4. Riesgos y decisiones pendientes

| Riesgo / decisión | Descripción | Impacto | Mitigación / propuesta |
|-------------------|-------------|---------|------------------------|
| A-04 | JWT vs sesión de cookie | Arquitectura de autenticación | Usar sesión de cookie por ahora; JWT solo si se exige API stateless (mejora aprobada). |
| SEG-01 | Service de correo para recuperación | Si no hay correo, no se puede enviar token de recuperación real | Documentar bloqueo; no simular envío; opción: mostrar token en pantalla solo en desarrollo de pruebas. |
| DDB-01 | Zona horaria del servidor | Consistencia de fechas/horas | Configurar misma zona horaria en PHP y MariaDB. |
| DDB-02 | Tiempo máximo de anticipación para reservar | Reglas de negocio de reserva | Definir con docente antes de F5. |
| DDB-03 | Política de vencimiento de reservas pendientes | Estados de reserva | Definir transiciones antes de F5. |
| DDB-04 | Nivel de aislamiento de transacción | Concurrencia en reservas | Probar con `REPEATABLE READ` + `FOR UPDATE`; subir a `SERIALIZABLE` solo si falla bajo carga. |
| DDB-05 | Cálculo automático del total del pedido | Detalle de pedidos | Calcular en servicio al crear/editar detalle o usar trigger de BD. |
| Portabilidad | Misma lógica en XAMPP, Docker, hosting | Requerido por instrucción | Configuración por variables de entorno; pruebas reales en los tres entornos (F9). |

---

## 5. Lista de verificación para aprobar cada etapa

### Checklist general (aplica a todas las fases)
- [ ] El código ejecuta sin errores críticos.
- [ ] No hay secretos ni credenciales en el repositorio.
- [ ] Las convenciones de nomenclatura (español, POO, capas) se respetan.
- [ ] La documentación afectada está actualizada.
- [ ] Se registró un commit por cambio coherente (convención de 07_git_ci_cd_y_docker.md).
- [ ] CI pasa (cuando se implemente GitHub Actions).

### Checklist F1
- [ ] Estructura de carpetas creada.
- [ ] `.gitignore` excluye secretos y documentación interna.
- [ ] `.env.example` sin secretos reales.
- [ ] `src/Config.php` y `src/Database/Conexion.php` funcionan.
- [ ] Docker levanta servicios y se saludan (o XAMPP funciona).

### Checklist F2
- [ ] Migración `001_create_schema.sql` ejecuta en MariaDB y MySQL.
- [ ] Tabla `migraciones_aplicadas` existe.
- [ ] Roles y usuario admin creados.
- [ ] Esquema coincide con `03_base_de_datos.md`.

### Checklist F3
- [ ] Contraseñas con `password_hash`/`password_verify` (bcrypt).
- [ ] Sesión con cookie `HttpOnly`, `SameSite=Lax` (y `Secure` en HTTPS).
- [ ] Login falla genéricamente sin revelar si el usuario existe.
- [ ] Rate limiting en login devuelve 429.

### Checklist F4
- [ ] Se listan areas y mesas.
- [ ] Disponibilidad excluye horarios ocupados y bloqueados.
- [ ] Respeta horario de apertura/cierre.

### Checklist F5
- [ ] No hay reservas superpuestas (caso C-01 verificado).
- [ ] Transacción + bloqueo de filas en creación.
- [ ] Cancelar libera la mesa.
- [ ] Estados y transiciones válidas.

### Checklist F6
- [ ] Pedido asociado a mesa y mesero válidos.
- [ ] Mesero solo ve sus propios pedidos; cocinero/admin ven todos.
- [ ] Transiciones de estado de pedido válidas.
- [ ] Solo admin gestiona productos.

### Checklist F7
- [ ] Solo admin accede a endpoints de administración (403 si no).
- [ ] Mesero no ve pedidos de otro mesero (403).
- [ ] Usuario no accede a reservas ajenas (403/404).
- [ ] Admin puede cambiar estado de reserva y pedido ajenos.

### Checklist F8
- [ ] Design tokens reutilizados en todos los componentes.
- [ ] Accesibilidad: teclado, foco, etiquetas, contraste, semántica.
- [ ] Responsive: móvil, tablet, escritorio.
- [ ] Formularios conservan datos y previenen envíos duplicados.

### Checklist F9
- [ ] Casos críticos C-01 a C-15 pasan.
- [ ] Pruebas unitarias, integración y endpoints pasan.
- [ ] GitHub Actions configurado y pasando.
- [ ] Guías de instalación (XAMPP, Docker, hosting) escritas.
- [ ] Portabilidad verificada en los tres entornos.
- [ ] `main` estable etiquetada `v1.0.0`.

**Una etapa no se considera finalizada si no se cumplen todos los ítems de su checklist.**

---

## 6. Fases adicionales implementadas (post-F9)

Tras completar las fases F1–F9 del plan original, se implementaron funcionalidades adicionales cubriendo RF-68 a RF-105 (requerimientos extendidos):

| Fase | Nombre | RFs cubiertos | Estado |
|------|--------|---------------|--------|
| F10 | **Stock, límites y menú semanal** | RF-72 a RF-80 | ✅ Completada |
| F11 | **Delivery, zonas y repartidores** | RF-81 a RF-83 | ✅ Completada |
| F12 | **Pedidos sin reserva y configuración** | RF-68 a RF-71, RF-101 | ✅ Completada |
| F13 | **Cupones y descuentos** | RF-88 | ✅ Completada |
| F14 | **Calificaciones, preferencias, fidelidad** | RF-90 a RF-93 | ✅ Completada (parcial: RF-93 tabla creada) |
| F15 | **RF pendientes: cierre caja, notificaciones, sucursales, permisos** | RF-84, RF-87, RF-89, RF-94-100, RF-102, RF-103, RF-105 | ✅ Migraciones listas (013) |

### Migraciones añadidas post-F9

| Archivo | Descripción |
|---------|-------------|
| `004_stock_y_disponibilidad.sql` | Stock y límite por producto, disponibilidad mesa (RF-72–77) |
| `005_pedidos_y_senas.sql` | Pedidos con seña, tipos, menú semanal (RF-78–80, RF-86) |
| `006_historial_y_reportes.sql` | Historial movimientos, normalización, reportes (RF-97–100) |
| `007_datos_prueba.sql` | Datos de prueba |
| `008_recuperar_contrasena_y_perfil.sql` | Recuperación contraseña, perfil extendido |
| `009_configuracion_y_limites_pedidos.sql` | Config sistema, límite pedidos sin reserva (RF-68–71, RF-101) |
| `010_cupones_descuento.sql` | Cupones y uso por cliente (RF-88) |
| `011_calificaciones_preferencias_fidelidad.sql` | Calificaciones, preferencias, fidelidad, reservas recurrentes (RF-90–93) |
| `012_delivery_repartidores.sql` | Zonas delivery, repartidores, campos delivery en pedidos (RF-81, RF-83) |
| `013_rf_pendientes.sql` | Estado entrega, cierre caja, favoritos, notificaciones, sucursales, respaldos, permisos (RF-84, 87, 89, 94–100, 102, 103, 105) |

### Endpoints principales añadidos

| Área | Endpoints |
|------|-----------|
| Configuración | `GET/PUT /api/configuracion`, `GET/PUT /api/configuracion/limite-pedidos-sin-reserva`, `GET/POST /api/configuracion/contador-pedidos-sin-reserva` |
| Zonas delivery | `GET/POST/PUT/DELETE /api/zonas-delivery` |
| Repartidores | `GET/POST/PUT/DELETE /api/repartidores`, `POST /api/repartidores/{id}/estado` |
| Cupones | `GET/POST/PUT/DELETE /api/cupones`, `GET /api/cupones/{id}/usos`, `GET /api/cupones/validar` |
| Calificaciones | `POST /api/calificaciones`, `GET /api/calificaciones/plato/{id}` |
| Preferencias | `GET/POST /api/preferencias` |
| Fidelidad | `GET /api/fidelidad/puntos`, `GET /api/fidelidad/historial`, `POST /api/fidelidad/canjear` |
| Autenticación | `POST /api/auth/recuperar`, `POST /api/auth/restablecer` |

### Cobertura de requerimientos (actualizada)

| Categoría | Total RF | Implementados | Pendientes |
|-----------|----------|---------------|------------|
| Core original (RF-01 a RF-30) | 30 | 30 | 0 |
| Extendidos (RF-31 a RF-71) | 41 | 41 | 0 |
| Avanzados (RF-72 a RF-105) | 34 | 23 | 11* |

*Los 11 RF pendientes requieren servicios externos (facturación electrónica, envío real de notificaciones WhatsApp/Email/Push, reportes avanzados) o son mejoras de fase 2 (reservas recurrentes con endpoints, cierre de caja UI, multisede UI).
