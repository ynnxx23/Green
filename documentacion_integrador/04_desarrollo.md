# 04 - Desarrollo

Sistema de gestión para el restaurante "Green en la Casa Blanca". Se detalla el diseño de interfaz, la base de datos, la programación y la integración frontend-base de datos.

## 1. Diseño de interfaz

### Pantallas públicas

Se implementaron las siguientes pantallas de acceso público:

- **Inicio (home):** sección hero con fondo `plato.png` y opacidad suave, título principal, breve descripción del restaurante y llamada a la acción.
- **Carta:** listado de productos del menú con nombre, descripción, precio y disponibilidad según stock.
- **Reservas:** formulario para crear una reserva con selección de fecha, hora, mesa y cantidad de comensales.
- **Nosotros:** información del restaurante, historia y equipo.
- **Contacto:** formulario de contacto y datos de ubicación del local.
- **Login:** pantalla de autenticación con campos de correo y contraseña, enlace a registro y botón de ingreso.
- **Registro:** acceso desde el login, permite crear cuenta seleccionando rol entre cliente, encargado o administrador.

### Paneles por rol

Según el rol del usuario autenticado, se habilita el panel correspondiente:

- **panel-cliente:** ver mis reservas, hacer reservas, ver mis pedidos, hacer pedidos y cancelar reservas propias; ver menú semanal, carrito de platos, cálculo de seña (50%) y selección de tipo de pedido (con reserva, para llevar o domicilio).
- **panel-encargado:** disponibilidad multifecha (días verde pastel seleccionados, amarillos ya creados, rango de hora), cambio de estados de pedidos, gestión de productos y stock; gestión de menú semanal, tabla de pedidos con columna de seña y estado, cambio de estado de seña.
- **panel-admin:** gestión de usuarios, creación de usuarios, cambio de estados de reservas, bloqueo de mesas y administración general.
- **panel despachador:** redirige automáticamente al panel correspondiente según el rol del usuario que inicia sesión.

### Navbar

La barra de navegación mantiene la siguiente estructura:

- Logo "Green" al lado izquierdo.
- Enlaces centrados: Inicio, Carta, Reservar mesa, Contacto.
- Solo el botón "Iniciar sesión" al lado derecho.
- No hay botón de registro en el navbar; el registro se encuentra dentro del flujo de login.

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

### Usuario de prueba (admin)

- Correo: `admin@restaurante.local`
- Contraseña: `admin123`
- Rol: administrador

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

### Middleware

`AuthMiddleware` protege las rutas y valida los roles de usuario (cliente, encargado, administrador) antes de permitir el acceso a cada panel.

### JavaScript

- `public/assets/js/api.js`: API centralizada con `fetch` y cookies de sesión (`credentials: include`).
- `public/assets/js/main.js`: lógica de sesión, manejo de autenticación en frontend y redirecciones por rol.

---

## 4. Integración app - Base de datos

El frontend consume la API REST mediante `fetch` y envía cookies de sesión con la opción `credentials: include` en cada petición, permitiendo mantener la sesión del usuario autenticado entre el navegador y el backend PHP.

---

## 5. Funcionalidades definidas e implementadas

- Registro y login con 3 roles: cliente, encargado y administrador.
- Ver carta de productos.
- Hacer reserva: calendario de 1 fecha, días en verde disponibles y gris no disponibles.
- Hacer pedido: selección de mesa, productos y cantidades.
- Ver mis reservas y mis pedidos.
- Cancelar reservas propias.
- Control de stock y límite de ventas por plato.
- Disponibilidad multifecha para encargado: días verde pastel seleccionados, amarillos ya creados y rango de hora.
- Cambiar estados de pedidos y reservas.
- Gestionar usuarios (administrador).
- Bloquear mesas.
- Panel despachador que redirige según el rol del usuario.
- Menú semanal: endpoints GET/POST/DELETE `/api/menu-semanal` con tablas `menu_semanal` y `menu_semanal_items`.
- Pedidos con seña: `POST /api/pedidos` acepta `tipo_pedido`, `seña` y `direccion_domicilio`; `POST /api/pedidos/{id}/seña` marca la seña como pagada.
- Tipos de entrega: `con_reserva`, `para_llevar`, `domicilio`.
- Panel cliente: vista menú semanal, carrito de platos, cálculo de seña (50%) y selección de tipo de pedido.
- Panel encargado: gestión de menú semanal, tabla de pedidos con columna de seña y estado, cambio de estado de seña.

---

**Archivo creado:** `documentacion_integrador/04_desarrollo.md`
