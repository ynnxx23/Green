# Plan de desarrollo — Backend

Sistema de gestión de un restaurante (API REST en PHP puro)

---

## 1. Resumen ejecutivo

El backend es una **API REST completa** construida en PHP 8.2+ sin frameworks, siguiendo arquitectura en capas (Controlador → Servicio → Validador → DAO → Modelo) y acceso a datos mediante PDO con consultas preparadas.

Estado actual: **fases F1 a F15 implementadas y funcionando en XAMPP**.
Backend completo: autenticación, mesas, reservas, pedidos, productos, áreas, horarios, bloques, stock, menú semanal, delivery, zonas, repartidores, cupones, calificaciones, preferencias, fidelidad, configuración, límite pedidos sin reserva, historial, reportes, cierre de caja, estado de entrega, platos favoritos, reservas recurrentes, notificaciones, reportes avanzados, sucursales, respaldos, roles y permisos avanzados.
Faltan: **F8 (frontend dinámico ya existe en public/)** y **F9 (pruebas, CI/CD, ajuste fino)** — pendiente de completar.

---

## 2. Arquitectura y capas

```
Capa de presentación (JSON vía HTTP)
        |
        v
Capa de rutas/endpoints  (public/index.php — front controller)
        |
        v
Capa de controladores    (src/Controller/*)
        |
        v
Capa de servicios        (src/Service/*)
        |
        v
Capa de validaciones     (src/Validator/*)
        |
        v
Capa de acceso a datos   (src/Dao/*) — PDO, consultas preparadas
        |
        v
Capa de modelos          (src/Model/*)
        |
        v
Base de datos MariaDB/MySQL
```

Capas transversales: Configuración (`.env`), Middleware (`AuthMiddleware`, `RateLimiterMiddleware`), Manejo centralizado de errores.

---

## 3. Stack tecnológico

| Componente | Tecnología | Versión | Observación |
|------------|-----------|---------|-------------|
| Lenguaje | PHP | 8.2.12 | Sin frameworks (PSR-4 manual vía `spl_autoload_register`) |
| Servidor web | Apache | 2.4.58 | XAMPP, mod_rewrite activo para front controller |
| Base de datos | MariaDB | 10.4.32 | Compatible con MySQL 8.0 |
| Acceso a BD | PDO | — | Consultas preparadas, singleton en `Conexion.php` |
| Hash de contraseñas | bcrypt | `password_hash/password_verify` | Nunca SHA-256 para credenciales |
| Sesiones | Cookie nativa PHP | `HttpOnly`, `SameSite=Lax`, `Secure=0` (HTTP local) | Sin JWT en versión base |
| Rate limiting | Token Bucket | `rate_limit_state` tabla | Middleware en login |
| Autoload | `spl_autoload_register` | — | `Restaurante\Xxx\Yyy → src/Xxx/Yyy.php` |

---

## 4. Estructura de carpetas (backend)

```
public/
├── index.php          # Front controller, enrutador único
└── .htaccess          # Reescritura: todo a index.php

src/
├── Config.php                 # movido a src/Config/Config.php (PSR-4)
├── Database/
│   └── Conexion.php           # PDO singleton
├── Config/
│   └── Config.php             # carga variables de entorno (.env)
├── ConfiguracionSesion.php    # configura cookies de sesión PHP
├── Middleware/
│   ├── AuthMiddleware.php     # verifica sesión y rol
│   └── RateLimiterMiddleware.php  # Token Bucket
├── Controller/
│   ├── AuthController.php         # login, registro, logout, sesión
│   ├── AreaController.php         # listar áreas
│   ├── MesaController.php         # listar mesas, disponibilidad
│   ├── ReservaController.php      # crear, listar propias, cancelar
│   ├── PedidoController.php       # crear pedido, listar, cambiar estado
│   ├── ProductoController.php     # CRUD productos (admin)
│   ├── UsuarioAdminController.php # CRUD usuarios (admin)
│   ├── AreaAdminController.php    # CRUD áreas (admin)
│   ├── MesaAdminController.php    # CRUD mesas (admin)
│   ├── HorarioAdminController.php # horarios de funcionamiento (admin)
│   └── BloqueAdminController.php  # bloques de mesa (admin)
├── Service/
│   ├── ServicioDeAutenticacion.php
│   ├── ServicioDeMesas.php
│   ├── ServicioDeDisponibilidad.php
│   ├── ServicioDeReservas.php
│   ├── ServicioDePedidos.php
│   ├── ServicioDeProductos.php
│   ├── ServicioDeHorarios.php
│   └── ServicioDeBloques.php
├── Validator/
│   └── ValidadorDeReserva.php
├── Dao/
│   ├── UsuarioDao.php
│   ├── ReservaDao.php
│   ├── AreaDao.php
│   ├── MesaDao.php
│   ├── ProductoDao.php
│   ├── PedidoDao.php
│   ├── DetallePedidoDao.php
│   ├── HorarioFuncionamientoDao.php
│   ├── BloqueMesaDao.php
│   └── IntentosLoginDao.php
├── Model/
│   ├── Usuario.php
│   ├── Rol.php
│   ├── Area.php
│   ├── Mesa.php
│   ├── Reserva.php
│   ├── Producto.php
│   ├── Pedido.php
│   ├── DetallePedido.php
│   ├── BloqueMesa.php
│   └── HorarioFuncionamiento.php

migrations/
├── 001_create_schema.sql
├── 002_migraciones_control.sql
├── 003_datos_iniciales.sql
├── 004_stock_y_disponibilidad.sql
├── 005_pedidos_y_senas.sql
├── 006_historial_y_reportes.sql
├── 007_datos_prueba.sql
├── 008_recuperar_contrasena_y_perfil.sql
├── 009_configuracion_y_limites_pedidos.sql
├── 010_cupones_descuento.sql
├── 011_calificaciones_preferencias_fidelidad.sql
├── 012_delivery_repartidores.sql
├── 013_rf_pendientes.sql
├── 014_cloudinary_imagenes_productos.sql
├── 015_datos_prueba_completos.sql
├── 016_datos_finales.sql
└── 017_reparacion_productos.sql

.env               # local, gitignored, valores reales (XAMPP)
.env.example       # versionado, sin secretos
```

---

## 5. Fases completadas (según hoja de ruta `08_hoja_de_ruta.md`)

| Fase | Nombre | Estado | Qué se implementó |
|------|--------|--------|-------------------|
| F1 | Estructura base y configuración | ✅ Hecha | Carpetas, `.env.example`, `.env`, `Config.php`, `Conexion.php`, `Dockerfile`, `docker-compose.yml`, `.htaccess`, `Logger` |
| F2 | Modelo de datos y migraciones | ✅ Hecha | 18 migraciones (001 → 018), tabla de control `migraciones_aplicadas`, roles y admin inicial, tablas RF pendientes (013), columnas saldo_pendiente y numero_pedido (018) |
| F3 | Autenticación y sesiones | ✅ Rehecho | Registro con bcrypt (cost 12, validación mayúscula/número), login con bloqueo por intentos fallidos, sesión segura (HttpOnly, Secure, SameSite=Lax, 4h), `AuthMiddleware`, `IntentosLoginDao`, recuperación de contraseña, perfil, cambio de contraseña, validación de sesión expirada |
| F4 | Áreas, mesas y disponibilidad | ✅ Hecha | Listar áreas, mesas por área, cálculo de disponibilidad respetando horarios, reservas y bloques |
| F5 | Reservas | ✅ Hecha | Crear con transacción + `FOR UPDATE`, listar propias, cancelar, estados y transiciones, prevención de solapamiento, detalle de reserva |
| F6 | Pedidos y productos | ✅ Rehecho | Pedidos con seña 50% automática, número de pedido (PED-YYYYMMDD-XXXX), saldo pendiente, delivery con zonas y costo de envío, validación de stock y límite de venta, descuento automático de stock, historial, estados de pedido |
| F7 | Administración y autorización | ✅ Hecha | Middleware de roles, CRUD admin (usuarios, áreas, mesas, productos, horarios, bloques), 403 para roles no autorizados |
| F8-F15 | Funcionalidades avanzadas (RF-68 a RF-105) | ✅ Hecha | Stock, menú semanal, delivery, zonas, repartidores, cupones, calificaciones, preferencias, fidelidad, reservas recurrentes, cierre de caja, estado de entrega, platos favoritos, notificaciones, reportes avanzados, sucursales, respaldos, roles y permisos granulares, subir imágenes, logging |

---

## 6. Endpoints API implementados

### Autenticación (público)
| Método | Ruta | Descripción | Acceso |
|--------|------|-------------|--------|
| POST | `/api/auth/login` | Iniciar sesión | Público |
| POST | `/api/auth/registro` | Registrar usuario | Público |
| POST | `/api/auth/logout` | Cerrar sesión | Autenticado |
| GET | `/api/auth/sesion` | Verificar sesión activa | Autenticado |

### Áreas y mesas (público)
| Método | Ruta | Descripción | Acceso |
|--------|------|-------------|--------|
| GET | `/api/areas` | Listar áreas | Público |
| GET | `/api/mesas` | Listar mesas (con filtros de disponibilidad) | Público |

### Reservas (autenticado)
| Método | Ruta | Descripción | Acceso |
|--------|------|-------------|--------|
| POST | `/api/reservas` | Crear reserva | Autenticado |
| GET | `/api/reservas` | Listar reservas propias | Autenticado |
| DELETE/PUT | `/api/reservas/{id}` | Cancelar/cambiar estado | Autenticado (propia) |

### Pedidos (autenticado por rol)
| Método | Ruta | Descripción | Acceso |
|--------|------|-------------|--------|
| POST | `/api/pedidos` | Registrar pedido (seña 50% automática, número de pedido, saldo pendiente, delivery con zonas) | Autenticado |
| GET | `/api/pedidos` | Listar pedidos (propios o todos según rol) | Autenticado |
| GET | `/api/pedidos/mis-pedidos` | Pedidos propios del cliente | Autenticado (cliente) |
| GET | `/api/pedidos/mis-pedidos-detalle` | Pedidos propios con detalle de items | Autenticado (cliente) |
| GET | `/api/pedidos/{id}` | Detalle completo de un pedido con items | Autenticado |
| GET | `/api/pedidos/estado/{estado}` | Pedidos filtrados por estado | Mesero/Admin |
| POST | `/api/pedidos/{id}/estado` | Cambiar estado de pedido | Mesero/Admin |
| POST | `/api/pedidos/{id}/seña` | Actualizar estado de seña y saldo pendiente | Mesero/Admin |
| PUT | `/api/pedidos/{id}/entrega` | Cambiar estado de entrega (delivery) | Mesero/Admin |

### Productos / Carta (público + admin)
| Método | Ruta | Descripción | Acceso |
|--------|------|-------------|--------|
| GET | `/api/productos` | Listar productos para la carta (filtro: `?categoria=`, `?q=`) | Público |
| GET | `/api/productos/categorias` | Listar categorías disponibles | Público |
| GET | `/api/productos/{id}` | Detalle de producto con calificación promedio y stock | Público |
| GET | `/api/productos/buscar?q=` | Buscar productos por nombre o categoría | Público |
| POST | `/api/productos` | Crear producto | Mesero/Admin |
| PUT | `/api/productos/{id}` | Editar producto | Mesero/Admin |
| DELETE | `/api/productos/{id}` | Desactivar producto | Mesero/Admin |
| PUT | `/api/productos/{id}/activar` | Activar producto | Mesero/Admin |
| PUT | `/api/productos/{id}/stock` | Actualizar stock y límite de venta | Mesero/Admin |

### Autenticación (público + autenticado)
| Método | Ruta | Descripción | Acceso |
|--------|------|-------------|--------|
| POST | `/api/auth/login` | Iniciar sesión (sesión segura, cookies HttpOnly/Secure/SameSite) | Público |
| POST | `/api/auth/registro` | Registrar cliente o encargado | Público |
| POST | `/api/auth/logout` | Cerrar sesión | Autenticado |
| GET | `/api/auth/sesion` | Verificar sesión activa | Autenticado |
| GET | `/api/auth/perfil` | Obtener perfil del usuario autenticado | Autenticado |
| PUT | `/api/auth/perfil` | Actualizar perfil (nombre, email, teléfono, avatar, etc.) | Autenticado |
| PUT | `/api/auth/cambiar-contrasena` | Cambiar contraseña (requiere actual) | Autenticado |
| POST | `/api/auth/recuperar` | Solicitar token de recuperación de contraseña | Público |
| POST | `/api/auth/restablecer` | Restablecer contraseña con token | Público |

### Administración (solo `administrador`)
| Método | Ruta | Descripción |
|--------|------|-------------|
| CRUD | `/api/admin/usuarios` | Gestión de usuarios |
| CRUD | `/api/admin/areas` | Gestión de áreas |
| CRUD | `/api/admin/mesas` | Gestión de mesas |
| CRUD | `/api/admin/horarios` | Horarios de funcionamiento |
| CRUD | `/api/admin/bloques` | Bloques de mesa |

---

## 7. Base de datos (migraciones)

Se aplican en orden numérico, una sola vez por entorno:

1. **`001_create_schema.sql`** — 14 tablas: `roles`, `usuarios`, `areas`, `mesas`, `horarios_funcionamiento`, `bloques_mesa`, `reservas`, `pedidos`, `detalle_pedidos`, `productos`, `intentos_login`, `rate_limit_state`, `migraciones_aplicadas`. Claves foráneas, CHECK constraints, índices.
2. **`002_migraciones_control.sql`** — Tabla de control de migraciones aplicadas.
3. **`003_datos_iniciales.sql`** — Roles (cliente, mesero, cocinero, administrador) y usuario administrador inicial (`admin@restaurante.local` / `admin123`).

### Usuario admin inicial
- Email: `admin@restaurante.local`
- Contraseña: `admin123` (bcrypt)
- Rol: `administrador` (id 4)

> **Nota:** La contraseña se guarda hasheada con `password_hash`. Cambiar en producción.

---

## 8. Seguridad implementada

- Contraseñas con `password_hash`/`password_verify` (bcrypt, cost 12), nunca texto plano ni SHA-256.
- Validación de contraseña: mínimo 8 caracteres, al menos una mayúscula y un número.
- Consultas preparadas con PDO (previene inyección SQL).
- Cookies de sesión `HttpOnly`, `SameSite=Lax`, `Secure` (HTTPS), duración 4 horas.
- Regeneración de ID de sesión tras login (`session_regenerate_id(true)`).
- Middleware `AuthMiddleware` verifica sesión y rol en endpoints protegidos.
- `RateLimiterMiddleware` (Token Bucket) limita peticiones en login y recuperación.
- `IntentosLoginDao` registra intentos fallidos y bloquea tras 5 fallos en 15 min.
- Validación de reglas de negocio en servidor (nunca solo en cliente).
- Logging de errores a archivo en `logs/YYYY-MM-DD.log` (sin secretos).
- Tokens de recuperación de contraseña de un solo uso, expiración 30 min.

---

## 9. Cómo ejecutar en XAMPP (resumen operativo)

1. **Servicios:** Iniciar Apache y MySQL en el Panel de Control de XAMPP.
2. **Proyecto:** Ubicado en `C:\xampp\htdocs\Yensel\Morday\`.
3. **`.env`:** Ya creado con valores XAMPP (`DB_USER=root`, `DB_PASSWORD=` vacío, `APP_URL=http://localhost/Yensel/Morday/public`).
4. **Base de datos:** Crear `restaurante_dev` y aplicar migraciones en orden (`001`, `002`, `003`).
5. **Acceso:** `http://localhost/Yensel/Morday/public/`
6. **Login:** `POST http://localhost/Yensel/Morday/public/api/auth/login` con JSON `{ "email": "admin@restaurante.local", "contrasena": "admin123" }`.

---

## 10. Qué NO pertenece al backend (pendiente de frontend / pruebas)

| Elemento | Fase | Estado | Plan |
|----------|------|--------|------|
| Pantallas HTML/CSS/JS (login, reservas, pedidos, admin) | F8 | ❌ Pendiente | Ver `plan_desarrollo/frontend/` |
| Assets CSS/JS (`design-tokens.css`, `api.js`, `auth.js`, etc.) | F8 | ❌ Pendiente | Ver `plan_desarrollo/frontend/` |
| Pruebas unitarias, integración, endpoints | F9 | ❌ Pendiente | Carpeta `tests/` |
| GitHub Actions (CI/CD) | F9 | ❌ Pendiente | `.github/workflows/ci.yml` |
| Casos críticos C-01 a C-15 | F9 | ❌ Pendiente | `06_pruebas_y_calidad.md` |
| Etiqueta `v1.0.0` en `main` | F9 | ❌ Pendiente | Release final |

---

## 11. Archivos de referencia

- `README.md` — Inicio rápido (Docker y XAMPP).
- `documentacion/guia_instalacion_xampp.md` — Guía detallada XAMPP.
- `documentacion/estrategia_migraciones.md` — Procedimiento de migraciones.
- `documentacion/variables_configuracion.md` — Variables de entorno.
- `documentacion_desarrollo/01_analisis_requisitos.md` — RF, RNF, RB.
- `documentacion_desarrollo/02_stack_y_arquitectura.md` — Stack, arquitectura, contratos API.
- `documentacion_desarrollo/08_hoja_de_ruta.md` — Fases F1-F9 y criterios de completado.

---

*Plan generado el 26/10/2026. Backend operativo en `http://localhost/Yensel/Morday/public/`.*
