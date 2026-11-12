# 04 - Desarrollo

Sistema de gestión para el restaurante "Green en la Casa Blanca". Se detalla el diseño de interfaz, la base de datos, la programación y la integración frontend-base de datos.

## 1. Diseño de interfaz

### Pantallas públicas

Se implementaron las siguientes pantallas de acceso público:

- **Inicio (home):** sección hero con fondo `plato.png` y opacidad suave, título principal, breve descripción del restaurante y llamada a la acción. Incluye la **carta destacada** con cuatro categorías (entradas, principales, postres y coctelería) y botón «Pedir» por plato con stock visible.
- **Carta:** listado de productos del menú con nombre, descripción, precio, **stock disponible** y filtro por categoría. Cada tarjeta abre una **modal de cantidad** antes de agregar al carrito.
- **Reservas:** formulario de reserva con selector de **fecha habilitada únicamente en los días con cupo abierto** y selector de **hora con las franjas disponibles de esa fecha**. La reserva se confirma directamente (no es una solicitud pendiente).
- **Pedir a domicilio:** página del carrito en **dos columnas** — izquierda el pedido y el resumen, derecha la lista de platos adicionales con **scroll propio** y **filtro por categoría** para no desplazar la página completa.
- **Nosotros:** información del restaurante, historia y equipo.
- **Contacto:** formulario de contacto y datos de ubicación del local.
- **Login:** pantalla de autenticación con campos de correo y contraseña, enlace a registro y botón de ingreso.
- **Registro:** acceso desde el login, permite crear cuenta seleccionando rol entre cliente, encargado o administrador.

### Paneles por rol

Según el rol del usuario autenticado, se habilita el panel correspondiente:

- **panel-cliente:** ver mis reservas, hacer reservas, ver mis pedidos, hacer pedidos y cancelar reservas propias; ver menú semanal, carrito de platos, cálculo de seña (50%) y selección de tipo de pedido (con reserva, para llevar o domicilio).
- **panel-encargado:** disponibilidad multifecha con **generador de cupos en lote** (rango de fechas + días de la semana + franja horaria + mesas por cupo), calendario mensual de cupos, cambio de estados de pedidos y reservas, gestión de productos (alta y edición de platos) y stock con límite de venta; gestión de menú semanal, tabla de pedidos con columna de seña y estado, cambio de estado de seña; zonas de delivery, repartidores, cupones de descuento y límite de pedidos sin reserva.
- **panel-admin:** gestión de usuarios, creación de usuarios, cambio de estados de reservas, bloqueo de mesas y administración general. **Además incluye las mismas secciones operativas que el panel de encargado**: cupos de reservas, platos de la carta y stock y límites. Ambos paneles comparten el módulo `gestion-panel.js`.
- **panel despachador:** redirige automáticamente al panel correspondiente según el rol del usuario que inicia sesión.

### Navbar

La barra de navegación mantiene la siguiente estructura:

- Logo "Green" al lado izquierdo.
- Enlaces centrados: Inicio, Carta, Reservar mesa, Pedir a domicilio, Contacto.
- Botón **carrito con contador** de artículos a la derecha, que enlaza a la página de pedido.
- Botón "Iniciar sesión" o el chip de sesión con el nombre del usuario y su rol.

### Colores de marca

Se usaron verdes de marca aclarados para mejorar la legibilidad:

- Verde primario: `#2E5F4E`
- Verde secundario: `#4A8C6F`

El fondo del hero utiliza `plato.png` con opacidad suave para que el texto resalte sobre la imagen.

---

## 2. Base de datos

Se aplicaron las siguientes migraciones en orden:

1. **001 esquema:** tablas base del sistema (usuarios, mesas, productos, reservas, pedidos, áreas, etc.).
2. **002 control:** tablas y columnas de control de estados y disponibilidad.
3. **003 datos iniciales:** inserción del usuario administrador y datos semilla básicos.
4. **004 stock y disponibilidad:** control de stock por producto y disponibilidad multifecha de mesas.
5. **005 menú semanal:** tablas `menu_semanal` y `menu_semanal_items` (semana, platos del menú semanal y sus cantidades/disponibilidad).

## 2. Base de datos

Se aplicaron las siguientes migraciones en orden:

1. **001 esquema:** tablas base del sistema (usuarios, mesas, productos, reservas, pedidos, áreas, etc.).
2. **002 control:** tablas y columnas de control de estados y disponibilidad.
3. **003 datos iniciales:** inserción del usuario administrador y datos semilla básicos.
4. **004 stock y disponibilidad:** control de stock por producto y disponibilidad multifecha de mesas.
5. **005 menú semanal:** tablas `menu_semanal` y `menu_semanal_items` (semana, platos del menú semanal y sus cantidades/disponibilidad).
6. **006 a 017:** historial de movimientos, configuración del sistema, cupones, calificaciones, preferencias, fidelización, delivery, notificaciones, cierre de caja, sucursales, respaldos y permisos.
7. **018 a 023:** saldo pendiente y número de pedido, renombrado de las columnas de seña, usuario del pedido, fecha de creación, mesa opcional en pedidos y corrección de codificación UTF-8.
8. **024 datos del cliente en la reserva:** columnas `comensales`, `telefono` y `ocasion` en `reservas`; `telefono` en `usuarios`; y `observaciones` en `pedidos`.

El esquema final comprende **36 tablas**. La descripción campo por campo está en
`13_diccionario_datos.md` y el diagrama de relaciones en `10_diagrama_der.md`.

### Cuentas de prueba

| Rol | Correo | Contraseña |
| --- | --- | --- |
| Administrador | `admin@restaurante.local` | `admin123` |
| Administrador | `laura.admin@test.local` | `admin123` |
| Encargado | `carlos.encargado@test.local` | `admin123` |
| Cliente | `cliente@demo.com` | `cliente123` |
| Cliente | `maria.cliente@test.local` | `admin123` |

---

## 3. Programación

### Controladores (`src/Controller`)

Se implementaron los siguientes controladores PHP:

- `AuthController`: login, registro y cierre de sesión.
- `PedidoController`: creación, listado y cambio de estado de pedidos.
- `ReservaController`: creación, listado, cancelación y cambio de estado de reservas.
- `UsuarioAdminController`: gestión de usuarios (crear, listar, editar, eliminar).
- `HorarioAdminController`: gestión de horarios de atención.
- `BloqueAdminController`: bloqueo y desbloqueo de mesas.
- `AreaAdminController`: gestión de áreas del restaurante.
- `MesaAdminController`: administración de mesas.
- `AreaController`: consulta de áreas (público/cliente).
- `MesaController`: consulta de mesas (público/cliente).
- `ProductoController`: listado y gestión de productos.
- `MenuSemanalController`: GET/POST/DELETE `/api/menu-semanal` para gestionar el menú semanal y sus items.
- `PedidoController`: creación de pedidos acepta `tipo_pedido` (`con_reserva`, `para_llevar`, `domicilio`), `seña` y `direccion_domicilio`; endpoint `POST /api/pedidos/{id}/seña` para marcar la seña como pagada.

### Servicios (`src/Service`)

Capa de servicios que encapsula la lógica de negocio: autenticación, reservas, pedidos, stock, disponibilidad y gestión de usuarios.

| Servicio | Responsabilidad |
| --- | --- |
| `ServicioDeAutenticacion` | Verificación de credenciales, emisión de sesión, control de intentos fallidos y recuperación de contraseña. |
| `ServicioDeReservas` | Creación de la reserva (confirmada directamente), asignación de mesa libre, cancelación y cambio de estado. |
| `ServicioDeProductos` | Alta, edición y activación de platos, con integración de imágenes en Cloudinary. |
| `ServicioDeMesas` | Consulta de áreas y mesas; la lista global de mesas ya no exige `id_area`. |
| `ServicioDeDisponibilidad` | Generación masiva de cupos por rango de fechas y días, y eliminación por rango. |
| `ServicioDePedidos` | Registro del pedido, cálculo de seña y saldo, y control de stock y límite de venta. |
| `ServicioDeCloudinary` | Subida y gestión de imágenes de producto. |

### Middleware

`AuthMiddleware` protege las rutas y valida los roles de usuario (cliente, encargado, administrador) antes de permitir el acceso a cada panel.

### JavaScript

| Archivo | Responsabilidad |
| --- | --- |
| `public/assets/js/api.js` | API centralizada con `fetch` y cookies de sesión (`credentials: include`). Expone los métodos de disponibilidad, stock, productos y pedidos. |
| `public/assets/js/main.js` | Lógica de sesión, manejo de autenticación en frontend, redirecciones por rol y utilidades de interfaz (toast, formato de moneda, botones con estado de carga). |
| `public/assets/js/carrito.js` | **Carrito de compras compartido**: persistencia en `localStorage`, contador en el navbar, modal de cantidad con tope de stock y helpers de formato. |
| `public/assets/js/gestion-panel.js` | **Módulo de gestión compartido** por el panel de encargado y el de administrador: stock y límites, alta y edición de platos, generación masiva de cupos y calendario de disponibilidad. |

### Hojas de estilo

| Archivo | Responsabilidad |
| --- | --- |
| `public/assets/css/main.css` | Variables de diseño, tipografía y utilidades de layout. |
| `public/assets/css/componentes.css` | Componentes compartidos (tarjetas de plato, filtros, formularios, calendario). |
| `public/assets/css/ecommerce.css` | Estilos del e-commerce: badge del carrito, tarjetas de producto con stock, modal de cantidad y layout de dos columnas del carrito con scroll propio. |
| `public/assets/css/panel.css` | Estilos de los paneles (sidebar, tarjetas, tablas, calendario), antes duplicados en el `<style>` de cada HTML. |

---

## 4. Integración app - Base de datos

El frontend consume la API REST mediante `fetch` y envía cookies de sesión con la opción `credentials: include` en cada petición, permitiendo mantener la sesión del usuario autenticado entre el navegador y el backend PHP.

La capa PHP se organiza en **autoload** por espacio de nombres (`Restaurante\Xxx\Yyy` → `src/Xxx/Yyy.php`), con tres capas: controladores (HTTP), servicios (reglas de negocio) y DAOs (acceso a datos). La conexión a la base de datos es un **singleton** (`Conexion::obtener()`) que construye el DSN a partir de la configuración (`Config::db()`), la cual lee el archivo `.env` en desarrollo y las variables de entorno del contenedor en producción.

---

## 5. Funcionalidades definidas e implementadas

### Reserva de mesa

- **Reserva directa, no solicitud:** al confirmar, la reserva queda en estado `confirmada` sin aprobación intermedia.
- **El cliente puede reservar sin iniciar sesión:** el sistema crea o reutiliza el usuario a partir del correo y el teléfono indicados en el formulario.
- **Mesa asignada automáticamente** por capacidad y ausencia de solapamiento con otra reserva.
- **Selector de fecha restringido:** solo se habilitan los días que tienen cupo abierto.
- **Selector de hora filtrado:** solo ofrece las franjas disponibles del día seleccionado.
- **Cupos generados en lote** por el encargado o el administrador: rango de fechas + días de la semana + franja horaria + mesas por cupo.
- **Cupos reutilizables** por el administrador y el encargado desde un único módulo compartido.

### E-commerce y pedidos a domicilio

- **Carrito persistente** en `localStorage`, visible desde el navbar con contador de artículos.
- **Modal de cantidad** al pulsar «Pedir», con tope por stock disponible.
- **Stock visible** en cada tarjeta de producto (`disponible`, `¡Últimas N!`, `Agotado`).
- **Carrito en dos columnas:** el pedido a la izquierda y la lista de platos adicionales a la derecha, con **scroll propio** y **filtro por categoría** para no desplazar la página completa.
- **Tarjetas de tamaño uniforme** con el botón «Pedir» siempre en la misma posición.
- Pedidos para llevar o a domicilio, con **seña del 50 %** automática, saldo pendiente y dirección de entrega obligatoria en domicilio.
- Validación de stock y límite de venta por producto en la creación del pedido.

### Gestión (encargado y administrador)

- Control de **stock y límite de venta** por plato.
- **Alta y edición de platos** de la carta (nombre, categoría, precio e imagen).
- **Generación masiva de cupos** de reserva y eliminación por rango de fechas.
- Cambio de estados de pedidos y de seña, y de reservas.
- Gestión de menú semanal, zonas de delivery, repartidores, cupones de descuento y límite de pedidos sin reserva.
- Gestión de usuarios, reportes, historial de movimientos, cierre de caja y respaldos (administrador).

### Autenticación y sesión

- Registro y login con 3 roles: cliente, encargado y administrador.
- Sesión por cookie `HttpOnly`, verificación de contraseña con bcrypt y bloqueo por intentos fallidos.
- Recuperación de contraseña con token de un solo uso y expiración de una hora.
- Panel despachador que redirige según el rol del usuario.

---

**Archivo creado:** `entregables/05_desarrollo.md`
