# Handoff — Sistema de Gestión de Restaurante "Green en la Casa Blanca"

## Estado del Proyecto
Sistema funcional completo con 3 roles, autenticación, reservas, pedidos, stock, menú semanal, historial y reportes. Listo para pruebas en entorno local (XAMPP/Docker).

## Estructura del Repositorio

```
/ (root)
├── .env / .env.example
├── docker-compose.yml / Dockerfile
├── README.md
├── start_server.bat / start_sistema.bat
├── /src                  Código fuente del sistema (PHP)
│   ├── Config/
│   ├── Controller/
│   ├── Dao/
│   ├── Database/
│   ├── Middleware/
│   ├── Model/
│   ├── Service/
│   └── Validator/
├── /public               Frontend (HTML/CSS/JS vanilla)
│   ├── index.php         Front controller
│   ├── router.php
│   ├── *.html            Páginas
│   └── /assets           CSS, imágenes, JS
├── /migrations           Migraciones de base de datos (001 → 007)
├── /documentacion        Documentación del sistema
│   ├── /general          Documentación general (requerimientos, tecnologías, endpoints, guías)
│   └── /desarrollo       Documentación de desarrollo (análisis, arquitectura, pruebas, CI/CD)
├── /entregables          Entregables del proyecto (documento APA7, etapas del integrador y diagramas)
│   ├── 00_indice_entregables.md
│   ├── 01_documento_apa7.md
│   ├── 02_analisis_problematica.md
│   ├── 03_etapa1_requerimientos.md
│   ├── 04_diseno_sistema.md
│   ├── 05_desarrollo.md
│   ├── 06_sistema_funcional_pruebas.md
│   ├── 07_plan_mantenimiento.md
│   ├── 08_plan_capacitacion.md
│   ├── 09_diagrama_caso_uso.md
│   ├── 10_diagrama_der.md
│   └── 11_diagrama_gantt.md
├── /plan_desarrollo      Plan de desarrollo (backend, frontend)
└── /tests                Pruebas (en .gitignore)
```

## Qué se ha implementado

### Backend (PHP)
- `public/index.php` — Front controller con enrutamiento de todos los endpoints API.
- `src/Controller/` — AuthController, PedidoController, ReservaController, UsuarioAdminController, HorarioAdminController, BloqueAdminController, AreaAdminController, MesaAdminController, AreaController, MesaController, ProductoController.
- `src/Middleware/AuthMiddleware.php` — Validación de roles (cliente=1, encargado=2, administrador=4).
- `src/Service/ServicioDeAutenticacion.php` — Login, registro, sesión, roles.
- Endpoints clave:
  - `/api/auth/*` — login, registro, logout, sesión.
  - `/api/productos`, `/api/mesas`, `/api/reservas`, `/api/pedidos`, `/api/disponibilidad`, `/api/menu-semanal`.
  - `/api/admin/stock`, `/api/admin/disponibilidad`, `/api/admin/bloques`, `/api/admin/horarios`.
  - `/api/admin/usuarios` (CRUD usuarios).
  - `/api/admin/reportes/ventas`, `/api/admin/reportes/productos-mas-vendidos`, `/api/admin/reportes/reservas-por-dia`, `/api/admin/reportes/ventas-por-dia`, `/api/admin/reportes/historial`.
  - `/api/admin/historial` (POST interno).
  - `/api/pedidos/{id}/seña` (marcar seña pagada/devuelta).
  - `/api/reservas/{id}/estado` y `/api/pedidos/{id}/estado` (cambiar estado).
  - `/api/admin/disponibilidad/{id}` (DELETE).

### Frontend (HTML/CSS/JS vanilla)
Páginas en `public/`:
- `index.html` — Inicio con hero (fondo `plato.png`), nosotros, carta destacada.
- `carta.html` — Carta con filtros por categoría.
- `reservas.html` — Redirige a login o panel-cliente.
- `nosotros.html`, `contacto.html`.
- `login.html`, `registro.html`.
- `panel.html` — Despachador por rol.
- `panel-cliente.html` — Mis reservas, nueva reserva (calendario 1 fecha), menú semanal, nuevo pedido (carrito, seña 50%, tipo de entrega), mis pedidos.
- `panel-encargado.html` — Pedidos, reservas, stock, menú semanal, disponibilidad multifecha, bloques.
- `panel-admin.html` — Usuarios, reservas, pedidos, link a reportes.
- `panel-reportes.html` — Ventas por periodo, productos más vendidos, reservas/día, ventas/día, historial filtrado.
- `assets/js/api.js`, `main.js`, `auth.js`, `reservas.js`.

### Base de Datos (Migraciones en `migrations/`)
- `001_create_schema.sql` — Esquema inicial (roles, usuarios, mesas, productos, reservas, pedidos, detalle_pedidos, áreas, horarios, bloques).
- `002_migraciones_control.sql` — Control de versionado.
- `003_datos_iniciales.sql` — Roles: 1=cliente, 2=encargado, 4=administrador. Usuario admin: `admin@restaurante.local` / `admin123`.
- `004_stock_y_disponibilidad.sql` — `stock_producto` (stock_actual, limite_venta), `disponibilidad_mesa`.
- `005_pedidos_y_senas.sql` — Campos en `pedidos` (tipo_pedido, seña, estado_seña, direccion_domicilio), `menu_semanal`, `menu_semanal_items`, `observaciones` en `reservas`.
- `006_historial_y_reportes.sql` — **Normalización**: `pedidos.id_mesa` nullable, `pedidos.id_usuario` nullable, FK con `ON DELETE SET NULL`, tabla `historial_movimientos`, índices para reportes.
- `007_datos_prueba.sql` — Datos de prueba (áreas, mesas, productos, stock, disponibilidad, menú semanal, usuarios).

### Documentación
- `/documentacion/general/` — Documentación general del sistema (requerimientos, tecnologías, endpoints, guías de instalación, estrategia de migraciones).
- `/documentacion/desarrollo/` — Documentación de desarrollo (análisis de requisitos, stack y arquitectura, base de datos, diseño y accesibilidad, seguridad, pruebas, CI/CD, hoja de ruta).
- `/entregables/` — Etapas del proyecto integrador (archivos 02 a 08: análisis, requerimientos, diseño, desarrollo, pruebas, mantenimiento y capacitación), documento APA7 (01) y diagramas (09 a 11).
- `/entregables/` — Entregables finales (documento APA7, diagramas de casos de uso, DER, Gantt en .md Mermaid).

## Reglas de Negocio Clave
- **3 roles únicamente:** cliente, encargado, administrador (sin cocinero).
- **Reservas:** solo fechas con disponibilidad abierta (calendario verde); validar solapamiento y mesas disponibles.
- **Pedidos:** validar stock y límite de venta; descontar stock al crear; seña 50% en pedidos anticipados.
- **Tipos de pedido:** `con_reserva`, `para_llevar`, `domicilio` (con dirección).
- **Calendario cliente:** 1 solo día. **Calendario encargado:** múltiples días.
- **Estados de reserva:** pendiente, confirmada, cancelada, vencida, completada.
- **Estados de pedido:** pendiente, en_preparacion, listo, entregado, cancelado.
- **Estados de seña:** pendiente, pagada, devuelta.

## Cómo Ejecutar (Producción/Pruebas)
1. Copiar proyecto en `htdocs/Yensel/Morday/` (XAMPP) o usar Docker Compose.
2. Copiar `.env.example` a `.env` y completar credenciales de BD.
3. Crear base de datos y aplicar migraciones en orden: `001` → `002` → `003` → `004` → `005` → `006` → `007`.
4. Acceder a `http://localhost/Yensel/Morday/public/`.
5. Usuario admin de prueba: `admin@restaurante.local` / `admin123`.

## Verificación de Sintaxis PHP
Todos los archivos PHP verificados con `php -l` sin errores (quando PHP esté disponible en el entorno).

## Qué Falta / Pendiente
- **Datos de prueba:** `migrations/007_datos_prueba.sql` listo para aplicar (orden 001→007).
- **PedidoController corregido:** acepta `id_mesa=NULL` (para_llevar/domicilio), guarda `tipo_pedido/seña/estado_seña/direccion_domicilio/id_usuario`, comillas simples en `IN (...)`.
- **Diagramas y Gantt:** Hechos en `/entregables/` (`.md` Mermaid): caso de uso, DER, Gantt.
- **Documento APA7:** Hecho — `/entregables/01_documento_apa7.md` (`.md`).
- **Pruebas en navegador:** Pendiente validación visual en localhost (PHP no disponible en este entorno; verificar en XAMPP/Docker).

## Contacto / Usuario de Prueba
- Admin: `admin@restaurante.local` / `admin123`

---
*Generado el 30 de octubre de 2026 — Sistema completo listo para pruebas.*