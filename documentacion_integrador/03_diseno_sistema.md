# 03. Diseño del Sistema

Documento de diseño técnico y funcional del sistema de gestión para el restaurante "Green en la Casa Blanca". Describe actores, casos de uso, modelo de datos, reglas de negocio y arquitectura técnica.

---

## 1. Actores y Roles

El sistema define exactamente tres roles de usuario, identificados por `id_rol`:

| id_rol | Rol | Responsabilidades principales |
|--------|-----|-------------------------------|
| 1 | Cliente | Ver la carta de productos. Hacer una reserva para una fecha determinada. Hacer pedidos. Ver sus propias reservas y pedidos. Cancelar sus propias reservas. |
| 2 | Encargado del restaurante | Gestionar el stock y el límite de venta por plato. Publicar el menú semanal con los platos disponibles cada semana. Abrir disponibilidad de mesas por múltiples fechas con rango de hora. Ver todos los pedidos, cambiar su estado y gestionar el estado de la seña (pendiente/pagada/devuelta). Bloquear mesas. |
| 4 | Administrador | Gestionar usuarios (crear, activar, desactivar, eliminar). Ver todas las reservas y cambiar su estado. Ver todos los pedidos y cambiar su estado. Acceso a reportes de ventas, productos más vendidos, reservas por día y ventas por día, y al historial completo de movimientos. Acceso total al sistema. |

---

## 2. Casos de Uso

A continuación se enumeran los casos de uso del sistema, con una descripción textual de cada uno.

### 2.1 Iniciar sesión
El usuario proporciona su correo electrónico y contraseña en el formulario de acceso. El sistema valida las credenciales contra la base de datos (hash bcrypt), aplica rate limiting para evitar ataques de fuerza bruta y, si son correctas, crea una sesión autenticada con cookie HttpOnly.

### 2.2 Registrarse
Un nuevo usuario se registra proporcionando nombre, correo electrónico, contraseña y rol (por defecto cliente). El sistema valida la unicidad del correo, almacena la contraseña con hash bcrypt y aplica rate limiting en el endpoint de registro.

### 2.3 Ver carta
El cliente consulta el catálogo de productos disponibles (platos y bebidas) con su nombre, categoría, precio y estado de disponibilidad. Los productos inactivos no se muestran.

### 2.4 Hacer reserva
Un usuario autenticado selecciona una fecha con disponibilidad abierta (día verde en el calendario), elige una mesa disponible dentro del rango horario indicado y confirma la reserva. El sistema valida que exista disponibilidad para la fecha, que no haya solapamiento de horarios en la mesa y que queden mesas disponibles, y crea el registro con estado `pendiente`.

### 2.5 Ver mis reservas
El cliente autenticado consulta el listado de sus propias reservas, con fecha, hora, mesa, estado y detalles asociados.

### 2.6 Cancelar reserva
El cliente autenticado puede cancelar una reserva propia que se encuentre en estado `pendiente` o `confirmada`. El sistema actualiza el estado a `cancelada`.

### 2.7 Hacer pedido
Un usuario autenticado selecciona productos de la carta (o del menú semanal publicado), indica cantidades y el tipo de pedido (`con_reserva`, `para_llevar` o `domicilio`), y confirma el pedido. Para pedidos anticipados (con o sin reserva), se aplica una seña del 50% del total, cuyo estado (`pendiente`, `pagada`, `devuelta`) lo gestiona el encargado. El sistema verifica que cada producto tenga stock suficiente y que no se supere el límite de venta configurado. Al confirmar, se descuenta el stock automáticamente. Se crea el pedido con sus detalles y estado inicial `pendiente`. Para `domicilio` se requiere una dirección de entrega.

### 2.8 Ver mis pedidos
El cliente autenticado consulta el listado de sus propios pedidos, con fecha, estado, total y detalle de productos.

### 2.9 Gestionar stock
El encargado consulta y edita el stock actual y el límite de venta de cada producto. El límite de venta define la cantidad máxima de unidades que pueden pedirse en un periodo determinado, evitando tomar más pedidos de los gestionables.

### 2.10 Gestionar disponibilidad
El encargado abre disponibilidad de mesas para múltiples fechas, definiendo para cada fecha el rango de hora (hora de inicio y hora de fin) y la cantidad de mesas disponibles. Estas fechas aparecen como "días verdes" en el calendario del cliente. Las fechas sin disponibilidad aparecen como "días grises" (no disponibles).

### 2.11 Gestionar pedidos (cambiar estado)
El encargado y el administrador ven todos los pedidos del sistema y pueden cambiar el estado de cualquiera de ellos. Los estados posibles son: `pendiente`, `en_preparacion`, `listo`, `entregado`, `cancelado`.

### 2.12 Gestionar usuarios
El administrador crea nuevos usuarios, consulta el listado completo, y puede activar, desactivar o eliminar cualquier usuario del sistema.

### 2.13 Gestionar reservas (cambiar estado)
El encargado y el administrador ven todas las reservas del sistema y pueden cambiar el estado de cualquiera de ellas. Los estados posibles son: `pendiente`, `confirmada`, `cancelada`, `vencida`, `completada`.

### 2.14 Bloquear mesa
El encargado bloquea una mesa para un rango de fechas (fecha de inicio y fecha de fin) indicando un motivo. Mientras una mesa esté bloqueada, no puede asignarse a nuevas reservas en ese periodo.

### 2.15 Gestionar menú semanal
El encargado publica el menú semanal, indicando qué platos están disponibles cada semana con su precio de la semana. Los clientes pueden consultar los platos disponibles del menú vigente al hacer un pedido.

### 2.16 Gestionar estado de seña de pedidos
El encargado gestiona el estado de la seña de cada pedido anticipado (`pendiente`, `pagada` o `devuelta`). La seña representa el 50% del total del pedido y se aplica a pedidos con o sin reserva.

---

## 3. Modelo de Datos

A continuación se describen las tablas principales del modelo de datos y sus relaciones. El prefijo de fechas (`fechas`) en varias tablas se utiliza para auditoría de creación y modificación.

### 3.1 roles
Almacena los roles del sistema.
- `id_rol` (entero, PK): identificador del rol.
- `nombre` (texto): nombre del rol (cliente, encargado, administrador).
- `descripcion` (texto): descripción de las responsabilidades del rol.
Valores iniciales: 1 = cliente, 2 = encargado, 4 = administrador.

### 3.2 usuarios
Almacena los datos de acceso de los usuarios.
- `id_usuario` (entero, PK): identificador del usuario.
- `nombre` (texto): nombre completo del usuario.
- `email` (texto, único): correo electrónico de acceso.
- `contrasena_hash` (texto): hash bcrypt de la contraseña.
- `id_rol` (entero, FK): rol asignado al usuario, referencia a `roles(id_rol)`.
- `activo` (booleano): indica si el usuario puede acceder al sistema.
- `fechas` (texto/json): fechas de creación y modificación para auditoría.

### 3.3 productos
Catálogo de productos vendibles.
- `id_producto` (entero, PK): identificador del producto.
- `nombre` (texto): nombre del producto.
- `categoria` (texto): categoría a la que pertenece.
- `precio` (decimal): precio unitario.
- `activo` (booleano): indica si el producto se muestra en la carta.

### 3.4 stock_producto
Control de inventario y límite de venta por producto.
- `id_stock` (entero, PK): identificador del registro de stock.
- `id_producto` (entero, FK): referencia a `productos(id_producto)`.
- `stock_actual` (entero): unidades disponibles actualmente.
- `limite_venta` (entero): cantidad máxima gestionable de pedidos para el producto.
- `activo` (booleano): indica si el registro de stock está vigente.

### 3.5 mesas
Mesas físicas del restaurante.
- `id_mesa` (entero, PK): identificador de la mesa.
- `id_area` (entero, FK): área a la que pertenece la mesa, referencia a `areas(id_area)`.
- `numero_mesa` (entero): número identificador de la mesa.
- `capacidad` (entero): número de comensales que soporta.
- `activo` (booleano): indica si la mesa está operativa.

### 3.6 areas
Áreas o salones del restaurante.
- `id_area` (entero, PK): identificador del área.
- `nombre` (texto): nombre del área.
- `descripcion` (texto): descripción del área.
- `activo` (booleano): indica si el área está operativa.

### 3.7 disponibilidad_mesa
Disponibilidad de mesas abierta por el encargado para reservas.
- `id_disponibilidad` (entero, PK): identificador de la disponibilidad.
- `fecha` (fecha): fecha para la cual se abre disponibilidad.
- `hora_inicio` (hora): hora de inicio del rango disponible.
- `hora_fin` (hora): hora de fin del rango disponible.
- `mesas_disponibles` (entero): cantidad de mesas disponibles en ese rango.
- `activo` (booleano): indica si la disponibilidad está vigente.
- `creado_por` (entero, FK): usuario que creó la disponibilidad, referencia a `usuarios(id_usuario)`.
- `fecha_creacion` (datetime): fecha y hora de creación.

### 3.8 reservas
Reservas de mesas realizadas por los clientes.
- `id_reserva` (entero, PK): identificador de la reserva.
- `id_mesa` (entero, FK): mesa reservada, referencia a `mesas(id_mesa)`.
- `id_usuario` (entero, FK): usuario que hizo la reserva, referencia a `usuarios(id_usuario)`.
- `fecha_reserva` (fecha): fecha de la reserva.
- `hora_inicio` (hora): hora de inicio de la reserva.
- `hora_fin` (hora): hora de fin de la reserva.
- `estado` (texto): estado de la reserva (pendiente, confirmada, cancelada, vencida, completada).
- `fechas` (texto/json): fechas de creación y modificación para auditoría.

### 3.9 pedidos
Pedidos de productos realizados por los usuarios.
- `id_pedido` (entero, PK): identificador del pedido.
- `id_usuario` (entero, FK, nullable): usuario que realizó el pedido (cliente o encargado), referencia a `usuarios(id_usuario)`. `ON DELETE SET NULL`.
- `id_mesero` (entero, FK, nullable): mesero o encargado que atendió el pedido (renombrado semánticamente), referencia a `usuarios(id_usuario)`. `ON DELETE SET NULL`.
- `id_mesa` (entero, FK, nullable): mesa asociada al pedido (solo aplica para `con_reserva`; pedidos `para_llevar` y `domicilio` no tienen mesa). **Campo normalizado: ahora nullable**, referencia a `mesas(id_mesa)`. `ON DELETE SET NULL`.
- `tipo_pedido` (texto): tipo de pedido (`con_reserva`, `para_llevar`, `domicilio`).
- `direccion_domicilio` (texto, nullable): dirección de entrega para pedidos de tipo `domicilio`.
- `seña` (decimal): monto de la seña (50% del total en pedidos anticipados).
- `estado_seña` (texto): estado de la seña (`pendiente`, `pagada`, `devuelta`). Gestionado por el encargado.
- `fecha_pedido` (datetime): fecha y hora del pedido.
- `estado` (texto): estado del pedido (pendiente, en_preparacion, listo, entregado, cancelado).
- `total` (decimal): monto total del pedido.
- `fechas` (texto/json): fechas de creación y modificación para auditoría.

### 3.10 detalle_pedidos
Líneas de detalle de cada pedido.
- `id_detalle` (entero, PK): identificador del detalle.
- `id_pedido` (entero, FK): pedido al que pertenece, referencia a `pedidos(id_pedido)`.
- `id_producto` (entero, FK): producto del detalle, referencia a `productos(id_producto)`.
- `cantidad` (entero): cantidad solicitada del producto.
- `precio_unitario` (decimal): precio unitario al momento del pedido.

### 3.11 menu_semanal
Menú semanal publicado por el encargado con los platos disponibles cada semana.
- `id_menu_semanal` (entero, PK): identificador del menú semanal.
- `semana_inicio` (fecha): fecha de inicio de la semana (lunes).
- `semana_fin` (fecha): fecha de fin de la semana (domingo).
- `activo` (booleano): indica si el menú semanal está vigente/publicado.
- `creado_por` (entero, FK): encargado que publicó el menú, referencia a `usuarios(id_usuario)`.
- `fecha_creacion` (datetime): fecha y hora de creación.

### 3.12 menu_semanal_items
Ítems del menú semanal: platos disponibles y su precio de la semana.
- `id_item` (entero, PK): identificador del ítem.
- `id_menu_semanal` (entero, FK): referencia a `menu_semanal(id_menu_semanal)`.
- `id_producto` (entero, FK): producto del menú, referencia a `productos(id_producto)`.
- `precio_semana` (decimal): precio del producto para esa semana.
- `disponible` (booleano): indica si el plato está disponible esa semana.

### 3.13 horarios_funcionamiento
Horarios de apertura y cierre por área y día de la semana.
- `id_horario` (entero, PK): identificador del horario.
- `id_area` (entero, FK): área a la que aplica, referencia a `areas(id_area)`.
- `dia_semana` (entero): día de la semana (1=lunes a 7=domingo).
- `hora_apertura` (hora): hora de apertura del área.
- `hora_cierre` (hora): hora de cierre del área.
- `activo` (booleano): indica si el horario está vigente.

### 3.14 bloques_mesa
Bloqueos de mesas para periodos determinados.
- `id_bloque` (entero, PK): identificador del bloqueo.
- `id_mesa` (entero, FK): mesa bloqueada, referencia a `mesas(id_mesa)`.
- `fecha_inicio` (fecha): fecha de inicio del bloqueo.
- `fecha_fin` (fecha): fecha de fin del bloqueo.
- `motivo` (texto): motivo del bloqueo.
- `creado_por` (entero, FK): usuario que creó el bloqueo, referencia a `usuarios(id_usuario)`.

### 3.15 Tablas de soporte y seguridad
- `intentos_login`: registra intentos fallidos de inicio de sesión por correo o IP para aplicar rate limiting.
- `rate_limit_state`: almacena el estado actual de límites de velocidad (rate limiting) en endpoints sensibles como login y registro.
- `migraciones_aplicadas`: lleva el registro de las migraciones de base de datos ya ejecutadas para control de versiones del esquema.

### 3.16 historial_movimientos
Auditoría completa de todo el sistema. Registra cada movimiento relevante (creaciones, modificaciones, cambios de estado, eliminaciones lógicas) con la tabla y el registro afectados.
- `id_historial` (entero, PK): identificador del movimiento.
- `tabla_afectada` (texto): nombre de la tabla afectada (ej. `pedidos`, `reservas`, `usuarios`).
- `id_registro` (entero): identificador del registro afectado en la tabla indicada.
- `accion` (texto): acción realizada (`INSERT`, `UPDATE`, `DELETE`, `CAMBIO_ESTADO`, etc.).
- `detalles` (JSON): detalles del movimiento en formato JSON (valores antes y después, campos modificados).
- `id_usuario` (entero, FK, nullable): usuario que ejecutó el movimiento, referencia a `usuarios(id_usuario)`. `ON DELETE SET NULL`.
- `fecha_movimiento` (datetime): fecha y hora del movimiento.
- `direccion_ip` (texto): dirección IP de origen del movimiento.

### 3.17 Relaciones entre tablas
- `usuarios` tiene un `rol` mediante `id_rol` -> `roles(id_rol)`.
- `reservas` pertenece a un `usuario` mediante `id_usuario` -> `usuarios(id_usuario)`.
- `reservas` pertenece a una `mesa` mediante `id_mesa` -> `mesas(id_mesa)`.
- `pedidos` pertenece a un `usuario` (cliente) mediante `id_usuario` -> `usuarios(id_usuario)`.
- `pedidos` pertenece a una `mesa` mediante `id_mesa` -> `mesas(id_mesa)` (nullable, solo aplica para `con_reserva`).
- `detalle_pedidos` pertenece a un `pedido` mediante `id_pedido` -> `pedidos(id_pedido)`.
- `detalle_pedidos` pertenece a un `producto` mediante `id_producto` -> `productos(id_producto)`.
- `stock_producto` pertenece a un `producto` mediante `id_producto` -> `productos(id_producto)`.
- `mesas` pertenece a un `área` mediante `id_area` -> `areas(id_area)`.
- `disponibilidad_mesa` es creado por un `usuario` mediante `creado_por` -> `usuarios(id_usuario)`.
- `menu_semanal` es creado por un `usuario` (encargado) mediante `creado_por` -> `usuarios(id_usuario)`.
- `menu_semanal_items` pertenece a un `menu_semanal` mediante `id_menu_semanal` -> `menu_semanal(id_menu_semanal)`.
- `menu_semanal_items` pertenece a un `producto` mediante `id_producto` -> `productos(id_producto)`.
- `bloques_mesa` pertenece a una `mesa` mediante `id_mesa` -> `mesas(id_mesa)`.
- `bloques_mesa` es creado por un `usuario` mediante `creado_por` -> `usuarios(id_usuario)`.
- `horarios_funcionamiento` pertenece a un `área` mediante `id_area` -> `areas(id_area)`.
- `historial_movimientos` registra el usuario que ejecutó el movimiento mediante `id_usuario` -> `usuarios(id_usuario)` (nullable).

---

## 4. Reglas de Negocio Clave

1. **Autenticación requerida:** Solo usuarios autenticados pueden realizar reservas y hacer pedidos.
2. **Tipos de pedido:** Un pedido puede ser `con_reserva` (mesa reservada), `para_llevar` (retiro en el local) o `domicilio` (delivery con dirección de entrega).
3. **Límite de venta y stock:** El límite de venta de un producto evita tomar más pedidos de los gestionables. Antes de aceptar un pedido, el sistema verifica que la cantidad solicitada no supere el stock disponible ni el límite de venta configurado. Al confirmar el pedido, se descuenta el stock automáticamente.
4. **Seña del 50%:** Todo pedido anticipado (con o sin reserva) aplica una seña del 50% del total. El estado de la seña (`pendiente`, `pagada`, `devuelta`) lo gestiona el encargado.
5. **Disponibilidad para reservas:** Las reservas solo se pueden hacer en fechas con disponibilidad abierta (días verdes en el calendario). El sistema valida que no haya solapamiento de horarios en la mesa y que queden mesas disponibles.
6. **Calendario del cliente:** El calendario que ve el cliente permite seleccionar 1 solo día para hacer una reserva.
7. **Calendario del encargado:** El calendario del encargado permite seleccionar múltiples días y definir para cada uno la hora de inicio y la hora de fin de disponibilidad.
8. **Estados de reserva:** Los estados válidos son `pendiente`, `confirmada`, `cancelada`, `vencida` y `completada`.
9. **Estados de pedido:** Los estados válidos son `pendiente`, `en_preparacion`, `listo`, `entregado` y `cancelado`.
10. **Cancelación de reserva propia:** Un cliente solo puede cancelar sus propias reservas y solo cuando el estado sea `pendiente` o `confirmada`.
11. **Bloqueo de mesas:** Una mesa bloqueada no puede asignarse a nuevas reservas durante el periodo de bloqueo definido.
12. **Menú semanal:** El encargado publica qué platos están disponibles cada semana mediante el menú semanal.
13. **Reportes e historial:** Los endpoints de reportes (ventas, productos más vendidos, reservas por día, ventas por día) y el historial de movimientos son exclusivos del rol administrador.

---

## 5. Arquitectura Técnica Resumida

### 5.1 Backend
- **Lenguaje:** PHP.
- **Punto de entrada:** `index.php` actúa como front controller.
- **Enrutamiento:** `router.php` se encarga de dirigir las peticiones a los controladores correspondientes.
- **Organización de código:** El directorio `src/` contiene controllers, services, DAOs, modelos y el middleware de autenticación.
- **Capas:** Controllers reciben las peticiones HTTP, services contienen la lógica de negocio, DAOs gestionan el acceso a datos y modelos representan las entidades.

### 5.2 Base de Datos
- **Motor:** MariaDB / MySQL.
- **Control de versiones del esquema:** Migraciones almacenadas en el directorio `migrations/`. La tabla `migraciones_aplicadas` registra cuáles se han ejecutado.

### 5.3 Frontend
- **Tecnologías:** HTML puro, CSS y JavaScript vanilla.
- **Estilos:** `main.css` para estilos globales y `componentes.css` para componentes reutilizables.
- **JavaScript:** Módulos vanilla en archivos `api.js` (comunicación con la API), `main.js` (lógica principal de páginas) y `auth.js` (gestión de autenticación en el cliente).
- **Páginas:** Archivos HTML ubicados en el directorio `public/`. Nueva página de administración: `panel-reportes.html` con gráficos y tablas de ventas, productos más vendidos, reservas y ventas por día, e historial de movimientos.

### 5.4 Autenticación y Seguridad
- **Sesiones:** Sesiones de PHP con cookie `HttpOnly` para proteger el identificador de sesión.
- **Contraseñas:** Hash con algoritmo bcrypt.
- **Rate limiting:** Control de intentos en endpoints de login y registro para prevenir ataques de fuerza bruta, apoyado por las tablas `intentos_login` y `rate_limit_state`.

### 5.5 Endpoints API
Los endpoints disponibles son los siguientes:
- `/api/auth/*`: Autenticación (login, registro, logout, verificación de sesión).
- `/api/productos`: Consulta y gestión del catálogo de productos.
- `/api/mesas`: Consulta y gestión de mesas y áreas.
- `/api/reservas`: Gestión de reservas (crear, listar, cambiar estado, cancelar).
- `/api/pedidos`: Gestión de pedidos (crear, listar, cambiar estado, detalle y estado de seña).
- `/api/menu-semanal`: Gestión del menú semanal (publicar, listar platos disponibles por semana).
- `/api/disponibilidad`: Gestión de disponibilidad de mesas por fecha y rango horario.
- `/api/admin/*`: Administración de usuarios y operaciones de acceso total.

### 5.6 Normalización de la base de datos

El esquema sigue los niveles de normalización 1FN, 2FN y 3FN:

- **1FN (primera forma normal):** todas las columnas contienen valores atómicos; no hay grupos repetidos ni columnas multivalor. Cada tabla tiene una clave primaria definida.
- **2FN (segunda forma normal):** cada atributo no clave depende completamente de la clave primaria de su tabla. No existen dependencias parciales (todas las tablas usan clave primaria simple, excepto `detalle_pedidos` y `menu_semanal_items` cuya clave compuesta identifica unívocamente cada línea).
- **3FN (tercera forma normal):** no hay dependencias transitivas entre atributos no clave; cada dato se almacena en una sola tabla.

Cambios clave de normalización aplicados en la migración `006_historial_y_reportes.sql`:
- `pedidos.id_mesa`, `pedidos.id_mesero` y `pedidos.id_usuario` se volvieron **nullable** con `ON DELETE SET NULL`, de modo que un pedido `para_llevar` o `domicilio` no requiere mesa, y un pedido no se pierde si se elimina lógicamente la mesa o el usuario asociado.
- Creación de la tabla `historial_movimientos` para centralizar la auditoría (quién, qué, cuándo y con qué IP) en lugar de dispersar campos de auditoría en cada tabla.
- Índices específicos para acelerar las consultas de reportes (`idx_pedidos_fecha_total`, `idx_reservas_fecha_estado`, `idx_detalle_producto_fecha`).

### 5.7 Reportes e Historial (rol administrador)

Los reportes y el historial son exclusivos del rol **administrador** (`id_rol = 4`):

**Reportes disponibles:**
- Ventas por período de tiempo (`/api/admin/reportes/ventas`).
- Productos más vendidos (`/api/admin/reportes/productos-mas-vendidos`).
- Cantidad de reservas por día (`/api/admin/reportes/reservas-por-dia`).
- Ventas por día (`/api/admin/reportes/ventas-por-dia`).

**Historial de movimientos:**
La tabla `historial_movimientos` registra cada movimiento relevante del sistema (creaciones, modificaciones, cambios de estado, señas de pedidos) con los campos:
- `tabla_afectada`: nombre de la tabla (ej. `pedidos`, `reservas`, `usuarios`).
- `id_registro`: identificador del registro afectado.
- `accion`: acción realizada (`INSERT`, `UPDATE`, `DELETE`, `CAMBIO_ESTADO`, etc.).
- `detalles`: valores antes y después en formato JSON.
- `id_usuario`: usuario que ejecutó la acción (nullable, `ON DELETE SET NULL`).
- `fecha_movimiento` y `direccion_ip`: cuándo y desde dónde ocurrió.

Endpoint de consulta filtrada: `GET /api/admin/reportes/historial?tabla=&accion=&fecha_desde=&fecha_hasta=`. Endpoint interno de inserción: `POST /api/admin/historial`.

---

Fin del documento de diseño del sistema.