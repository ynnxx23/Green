# Sistema de gestión de un restaurante — Green en la Casa Blanca

Sistema web completo para gestionar reservas de mesas, registrar pedidos y controlar su estado en un restaurante de estilo neoclásico. Resuelve la problemática de organizar reservas, registrar pedidos y controlar su estado, evitando demoras y errores en la atención.

---

## 1. Actores y roles (3 exactamente)

El sistema tiene **3 roles de usuario** (eliminado el rol cocinero):

| Rol | ¿Quién es? | Qué puede hacer |
|-----|------------|-----------------|
| **Cliente** | Persona que consume en el restaurante | Ver la carta, ver menú semanal, hacer reservas (1 fecha, calendario verde=disponible/gris=no disponible), hacer pedidos con o sin reserva, ver sus propias reservas y pedidos, cancelar reservas pendientes, iniciar sesión / registrarse. |
| **Encargado del restaurante** | Encargado de sala / mesero | Gestionar stock y límite de venta por plato, abrir disponibilidad de mesas por múltiples fechas con rango de hora, ver todos los pedidos y cambiar su estado (pendiente, en_preparación, listo, entregado, cancelado), ver todas las reservas y cambiar su estado, gestionar menú semanal, consultar señas de pedidos, administrar delivery y retiro, bloquear mesas. |
| **Administrador** | Jefe de sistema | Gestionar usuarios (crear, activar, desactivar, eliminar — roles cliente, encargado, administrador), ver todas las reservas y cambiar estado, ver todos los pedidos y cambiar estado, acceso total al sistema. |

**Registro público:** un visitante puede registrarse como **cliente** o **encargado** desde `registro.html`. El rol de **administrador** solo puede crearlo otro administrador desde el panel de administración.

---

## 2. Funcionalidades por rol

### Cliente
- Ver la carta de productos con filtros por categoría.
- Ver el **menú semanal** publicado por el encargado (platos disponibles cada semana).
- Hacer una reserva: selecciona **una fecha** en un calendario.
  - Días **verdes** = hay disponibilidad de mesas, seleccionables.
  - Días **gris claro** = sin disponibilidad, inseleccionables.
  - Luego elige una mesa y una hora disponible dentro del rango de apertura.
- Ver sus propias reservas y cancelar las que estén pendientes.
- Hacer un **pedido anticipado**:
  - **Con reserva de mesa:** se le cobra un **50% de seña** por los platos y la mesa.
  - **Sin reserva (para llevar):** pedido para llevar, se le pide el plato y cantidad, se dice el precio, se aplica seña del 50%, puede retirar personalmente.
  - **Sin reserva (domicilio):** pedido a domicilio, se aplica seña del 50%, se indica dirección de entrega.
  - También puede hacer una reserva **sin pedir ningún plato**.
- Ver sus propios pedidos y sus estados, incluido el estado de la seña (pendiente/pagada).

### Encargado del restaurante
- **Stock y límite de ventas por plato:** ingresa el stock actual y un límite de venta para cada producto. El límite evita tomar más pedidos de los que el restaurante puede gestionar. El stock se descuenta automáticamente al crear pedidos.
- **Disponibilidad de mesas por múltiples fechas:** abre disponibilidad para varias fechas a la vez desde un calendario.
  - Días **verdes pastel** = seleccionados para abrir disponibilidad.
  - Días **amarillos** = ya tienen disponibilidad creada.
  - Días **sin disponibilidad** = no se pueden reservar ese día.
  - Define rango de hora inicial y final, y cantidad de mesas disponibles.
- **Menú semanal:** publica y actualiza el menú semanal con los platos disponibles cada semana.
- **Pedidos:** ver todos los pedidos, cambiar su estado, consultar señas (pendiente/pagada), marcar seña como pagada, administrar delivery y retiro de pedidos.
- **Reservas:** ver todas las reservas, confirmar, rechazar, modificar o cancelar reservas.
- **Bloques de mesas:** bloquear mesas en fechas/horas donde no haya disponibilidad.

### Administrador
- Gestionar usuarios: crear, activar, desactivar y eliminar clientes, encargados y otros administradores.
- Ver **todas** las reservas del sistema y cambiar su estado (pendiente, confirmada, cancelada, vencida, completada).
- Ver **todos** los pedidos y cambiar su estado.
- **Reportes y estadísticas:** ver ventas por periodo de tiempo, productos más vendidos, días con más reservas, días con más ventas, historial completo de movimientos del sistema.
- Acceso total a la gestión del sistema.

---

## 3. Reglas de negocio clave

- Solo usuarios autenticados pueden reservar y pedir.
- **Reservas:** solo se pueden hacer en fechas con disponibilidad abierta (días verdes). El sistema valida que no haya solapamiento de horario para la misma mesa y que queden mesas disponibles.
- **Pedidos:** el sistema valida stock disponible por producto y límite de venta. Al crear un pedido, el stock se descuenta automáticamente.
- **Seña del 50%:** se aplica a todo pedido anticipado (con o sin reserva). El estado de la seña (pendiente/pagada) lo gestiona el encargado.
- **Tipos de pedido:** `con_reserva` (mesa reservada), `para_llevar` (retiro en local), `domicilio` (delivery con dirección).
- El calendario del cliente permite seleccionar **1 solo día**.
- El calendario del encargado permite seleccionar **múltiples días** y definir hora de inicio y fin.
- Estados de reserva: pendiente, confirmada, cancelada, vencida, completada.
- Estados de pedido: pendiente, en_preparacion, listo, entregado, cancelado.
- **3 roles únicamente:** cliente (1), encargado (2), administrador (4). No existe rol cocinero.

---

## 4. Estructura de páginas públicas

| Página | Descripción |
|--------|-------------|
| `index.html` | Inicio con hero (fondo `plato.png`), nosotros, carta destacada, reservas rápidas. |
| `carta.html` | Carta completa con filtros por categoría y productos desde la API. |
| `reservas.html` | Redirige a login si no hay sesión, o al panel-cliente para reservar con calendario. |
| `nosotros.html` | Historia y concepto del restaurante. |
| `contacto.html` | Dirección, teléfonos, WhatsApp, horarios. |
| `login.html` | Inicio de sesión. Incluye enlace a registro si no tiene cuenta. |
| `registro.html` | Registro de nuevo usuario (cliente o encargado). |
| `panel.html` | Despachador automático al panel según el rol. |
| `panel-cliente.html` | Panel del cliente (reservas, menú semanal, pedidos, mis reservas, mis pedidos). |
| `panel-encargado.html` | Panel del encargado (pedidos, reservas, stock, menú semanal, disponibilidad, bloques). |
| `panel-admin.html` | Panel de administración (usuarios, reservas, pedidos). |
| `panel-reportes.html` | Panel de reportes y estadísticas (solo administrador). |

---

## 5. Principales endpoints de la API (`/api/`)

### Autenticación
- `POST /api/auth/login` — iniciar sesión.
- `POST /api/auth/registro` — registrar usuario (cliente o encargado).
- `POST /api/auth/logout` — cerrar sesión.
- `GET /api/auth/sesion` — verificar sesión activa (devuelve rol).

### Públicos / clientes
- `GET /api/productos` — listar productos de la carta.
- `GET /api/mesas` — listar mesas.
- `GET /api/disponibilidad?fecha=YYYY-MM-DD` — disponibilidad de mesas para una fecha.
- `GET /api/menu-semanal` — listar menú semanal activo.
- `GET /api/menu-semanal?semana=YYYY-MM-DD` — menú semanal de una semana específica.
- `POST /api/reservas` — crear reserva (autenticado; valida disponibilidad y solapamiento).
- `GET /api/reservas/mis-reservas` — reservas propias.
- `POST /api/reservas/{id}/cancelar` — cancelar reserva propia.
- `POST /api/pedidos` — crear pedido (autenticado; valida stock y límite de venta; acepta tipo_pedido, seña, direccion_domicilio).
- `GET /api/pedidos/mis-pedidos` — pedidos propios.

### Encargado / administrador
- `GET /api/admin/stock` — listar stock y límite por producto.
- `POST /api/admin/stock` — guardar/actualizar stock y límite.
- `GET /api/admin/disponibilidad` — listar disponibilidad.
- `POST /api/admin/disponibilidad` — crear disponibilidad (una o varias fechas).
- `DELETE /api/admin/disponibilidad/{id}` — desactivar disponibilidad.
- `GET /api/admin/horarios?area_id=N` — horarios de funcionamiento por área.
- `POST /api/admin/horarios` — crear/actualizar horario.
- `GET /api/admin/bloques?mesa_id=N` — bloques de una mesa.
- `POST /api/admin/bloques` — crear bloque.
- `DELETE /api/admin/bloques/{id}` — eliminar bloque.
- `POST /api/menu-semanal` — crear/actualizar menú semanal (encargado/admin).
- `DELETE /api/menu-semanal/{id}` — eliminar menú semanal (encargado/admin).
- `POST /api/pedidos/{id}/seña` — marcar seña como pagada/devuelta (encargado/admin).
- `GET /api/pedidos` — listar todos los pedidos (encargado/admin).
- `POST /api/pedidos/{id}/estado` — cambiar estado de pedido.
- `GET /api/reservas` — listar todas las reservas (solo admin).
- `POST /api/reservas/{id}/estado` — cambiar estado de reserva (solo admin).

### Administrador / Reportes e historial
- `GET /api/admin/reportes/ventas?fecha_desde=&fecha_hasta=` — Ventas totales por periodo.
- `GET /api/admin/reportes/productos-mas-vendidos?fecha_desde=&fecha_hasta=` — Top productos más vendidos.
- `GET /api/admin/reportes/reservas-por-dia?fecha_desde=&fecha_hasta=` — Cantidad de reservas por día.
- `GET /api/admin/reportes/ventas-por-dia?fecha_desde=&fecha_hasta=` — Ventas por día.
- `GET /api/admin/reportes/historial?tabla=&accion=&fecha_desde=&fecha_hasta=` — Historial de movimientos con filtros.
- `POST /api/admin/historial` — Insertar movimiento al historial (uso interno).

### Administrador / Usuarios
- `POST /api/admin/usuarios` — crear usuario (solo admin).
- `PUT /api/admin/usuarios/{id}` — actualizar usuario (solo admin).
- `DELETE /api/admin/usuarios/{id}` — eliminar usuario (solo admin).

---

## 6. Base de datos y migraciones

Aplicar las migraciones en orden dentro de `migrations/`:

1. `001_create_schema.sql` — esquema inicial.
2. `002_migraciones_control.sql` — control de versionado.
3. `003_datos_iniciales.sql` — roles base (cliente=1, encargado=2, administrador=4) y usuario administrador inicial. **Sin rol cocinero.**
4. `004_stock_y_disponibilidad.sql` — tablas `stock_producto` (stock y límite de venta) y `disponibilidad_mesa` (disponibilidad por fecha y hora).
5. `005_pedidos_y_senas.sql` — campos en `pedidos` (tipo_pedido, seña, estado_seña, direccion_domicilio), tablas `menu_semanal` y `menu_semanal_items`, campo `observaciones` en `reservas`.
6. `006_historial_y_reportes.sql` — normalización (id_mesa nullable, id_usuario en pedidos), tabla `historial_movimientos` y índices para reportes.

**Usuario administrador de prueba:**
- Email: `admin@restaurante.local`
- Contraseña: `admin123` (cambiar en producción)

---

## 7. Entornos soportados

- XAMPP local (Apache + PHP + MariaDB)
- Docker Compose local
- Hosting o servidor remoto con PHP y MariaDB/MySQL

### Inicio rápido (XAMPP)
1. Copiar el proyecto en `htdocs/Yensel/Morday/` (o la ruta de tu servidor).
2. Copiar `.env.example` a `.env` y completar valores (base de datos, URL, cookies).
3. Crear base de datos y aplicar las migraciones de `migrations/` en orden.
4. Acceder a `http://localhost/Yensel/Morday/public/`.

### Inicio rápido (Docker)
```bash
cp .env.example .env
# editar .env con valores reales
docker compose up -d
```

---

## 8. Correcciones aplicadas (sistema completo y coherente)

- **Nav fijo:** logo "Green" izquierda, enlaces centrados (Inicio, Carta, Reservar mesa, Contacto), botón "Iniciar sesión" a la derecha. Sin botón de registro en el navbar (va dentro de login).
- **Fondo hero:** `plato.png` con opacidad suave, como el ejemplo 2.
- **Colores:** verdes aclarados (#2E5F4E primario, #4A8C6F secundario) en footer, botones y texto.
- **3 roles:** eliminado el rol cocinero de BD, middlewares, servicios y formularios.
- **Reservas validadas:** el backend verifica disponibilidad de mesas, rango horario y solapamiento antes de aceptar una reserva.
- **Stock real:** el backend valida stock disponible y límite de venta al crear pedidos, y descuenta stock automáticamente.
- **Calendario cliente:** solo 1 fecha, días verdes disponibles y grises no disponibles.
- **Calendario encargado:** múltiples fechas, días verde pastel seleccionados, amarillos ya creados.
- **Menú semanal:** publicación y consulta de platos por semana.
- **Pedidos con seña:** seña del 50% para pedidos anticipados, tipos con_reserva/para_llevar/domicilio, estado de seña gestionable por encargado.
- **Delivery y retiro:** dirección de domicilio para pedidos a domicilio, opción para llevar sin dirección.
- **Historial de movimientos:** tabla `historial_movimientos` registra todo (reservas, pedidos, cambios de estado, señas) con usuario, fecha, IP y detalles en JSON.
- **Reportes de administrador:** ventas por periodo, productos más vendidos, reservas por día, ventas por día, historial filtrado.

---

## 9. Estructura del Repositorio

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

## 10. Documentación del proyecto integrador

Todos los archivos de las etapas del proyecto integrador se encuentran en `entregables/` (archivos numerados del 02 al 08), junto al documento APA7 (01) y los diagramas (09 a 11). Ver `entregables/00_indice_entregables.md` para el listado completo y ordenado.

Documentación general del sistema en `documentacion/general/` y documentación de desarrollo en `documentacion/desarrollo/`.

Entregables finales en `entregables/` (documento APA7, diagramas de casos de uso, DER y Gantt en formato .md Mermaid).

---

## 11. Resumen del problema resuelto

**Problemática:** El restaurante tenía dificultades para organizar reservas de mesas, registrar pedidos y controlar su estado, generando demoras y errores en la atención. Además, necesitaba publicar el menú semanal para que los clientes consultaran los platos disponibles.

**Solución implementada:** Sistema funcional completo con 3 roles, autenticación por sesión, calendarios de disponibilidad, control de stock y límite de venta, gestión de estados de pedidos y reservas, administración de usuarios, menú semanal, pedidos con seña del 50%, tipos de entrega (para llevar/domicilio/con reserva), y administración de señas y delivery. Todo conectado a una API REST con backend PHP y base de datos MariaDB/MySQL.
