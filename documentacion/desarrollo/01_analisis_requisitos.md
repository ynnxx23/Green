# 01. Analisis de requisitos — Sistema de gestion de un restaurante

> **Estado:** version actualizada con requerimientos de referencia (RF-01 a RF-71) y requerimientos adicionales (RF-72 a RF-105).  
> **Proyecto:** Green en la Casa Blanca — Sistema de gestion de restaurante (reservas de mesas, pedidos, menú, pagos, reportes).  
> **Idioma:** espanol.  
> **Fecha de actualizacion:** octubre 2026.

---

## 1. Resumen del proyecto

| Dato | Valor |
|------|-------|
| Nombre del sistema | Green en la Casa Blanca |
| Dominio | Restaurante con servicio de reservas de mesas, menú semanal, pedidos anticipados, pagos con seña y reportes de ventas |
| Actores principales | Cliente, Encargado, Administrador |
| Total de requerimientos funcionales | 105 (RF-01 a RF-105) |
| Total de requerimientos no funcionales | 12 (RNF-01 a RNF-12) |
| Total de reglas de negocio | 20 (RB-01 a RB-20) |

---

## 2. Alcance del sistema

El sistema permite a los clientes registrarse, iniciar sesión, consultar el menú semanal, hacer reservas de mesas, realizar pedidos anticipados (para retiro o delivery), pagar señas y consultar su historial. Permite a los encargados gestionar mesas, disponibilidad, menú semanal, productos, pedidos, reservas, señas y stock. Permite al administrador gestionar usuarios, encargados, roles, permisos y generar reportes de ventas.

---

## 3. Requerimientos funcionales (RF)

Cada RF trae identificador `RF-XX`, nombre, descripcion y actor responsable.

### 3.1 Modulo: Gestion de clientes y acceso

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-01 | Registro de cliente | El sistema deberá permitir que los clientes se registren con nombre, email y contraseña. | Cliente |
| RF-02 | Inicio de sesion de cliente | El sistema deberá permitir que los clientes inicien sesión con email y contraseña. | Cliente |
| RF-03 | Cierre de sesion de cliente | El sistema deberá permitir que los clientes cierren sesión y finalicen su sesion activa. | Cliente |
| RF-04 | Modificacion de datos personales | El sistema deberá permitir que los clientes modifiquen sus datos personales (nombre, email, contraseña). | Cliente |
| RF-05 | Recuperacion de contrasena | El sistema deberá permitir que los clientes recuperen su contraseña mediante un proceso seguro (token temporal). | Cliente |
| RF-06 | Consulta de perfil y datos | El sistema deberá permitir que los clientes consulten su perfil y sus datos registrados. | Cliente |

### 3.2 Modulo: Consulta y gestion del menú

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-07 | Visualizacion del menú semanal | El sistema deberá permitir que los clientes visualicen el menú semanal publicado. | Cliente |
| RF-08 | Informacion de platos | El sistema deberá mostrar el nombre, la descripcion y el precio de cada plato. | Cliente |
| RF-09 | Consulta de combos disponibles | El sistema deberá permitir que los clientes consulten los combos disponibles. | Cliente |
| RF-10 | Disponibilidad de platos | El sistema deberá mostrar la disponibilidad de los platos publicados (disponible / no disponible). | Cliente |
| RF-11 | Publicar platos en el menú | El sistema deberá permitir que el encargado publique nuevos platos en el menú. | Encargado |
| RF-12 | Modificacion de platos | El sistema deberá permitir que el encargado modifique la informacion de los platos (nombre, descripcion, precio, categoria). | Encargado |
| RF-13 | Eliminacion de platos | El sistema deberá permitir que el encargado elimine platos del menú (desactivacion logica). | Encargado |
| RF-14 | Establecer precios de platos | El sistema deberá permitir que el encargado establezca los precios de los platos. | Encargado |
| RF-15 | Crear y publicar combos | El sistema deberá permitir que el encargado cree y publique nuevos combos (plato + bebida + postre con precio especial). | Encargado |
| RF-16 | Modificar o eliminar combos | El sistema deberá permitir que el encargado modifique o elimine los combos existentes. | Encargado |
| RF-17 | Organizar y actualizar menú semanal | El sistema deberá permitir que el encargado organice y actualice el menú semanal (semana inicio/fin, items, precios semanales). | Encargado |
| RF-18 | Activar o desactivar disponibilidad de platos | El sistema deberá permitir que el encargado active o desactive la disponibilidad de los platos. | Encargado |

### 3.3 Modulo: Gestion de reservas

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-19 | Consulta de mesas disponibles | El sistema deberá permitir que los clientes consulten las mesas disponibles. | Cliente |
| RF-20 | Consulta de horarios disponibles | El sistema deberá permitir que los clientes consulten los horarios disponibles para reservar. | Cliente |
| RF-21 | Registro de nueva reserva | El sistema deberá permitir que los clientes registren una nueva reserva. | Cliente |
| RF-22 | Datos requeridos para reserva | El sistema deberá solicitar la fecha, el horario y la cantidad de personas para realizar una reserva. | Sistema |
| RF-23 | Consultar reservas propias | El sistema deberá permitir que los clientes consulten sus reservas. | Cliente |
| RF-24 | Cancelar reservas propias | El sistema deberá permitir que los clientes cancelen sus propias reservas. | Cliente |
| RF-25 | Consultar todas las reservas | El sistema deberá permitir que el encargado consulte todas las reservas recibidas. | Encargado |
| RF-26 | Confirmar reservas | El sistema deberá permitir que el encargado confirme las reservas solicitadas. | Encargado |
| RF-27 | Rechazar reservas | El sistema deberá permitir que el encargado rechace las solicitudes de reserva. | Encargado |
| RF-28 | Modificar datos de reserva | El sistema deberá permitir que el encargado modifique los datos de una reserva (fecha, horario, personas, mesa). | Encargado |
| RF-29 | Cancelar reserva | El sistema deberá permitir que el encargado cancele una reserva. | Encargado |
| RF-30 | Asignar mesas a reservas | El sistema deberá permitir que el encargado asigne mesas a las reservas confirmadas. | Encargado |
| RF-31 | Actualizar disponibilidad de mesas | El sistema deberá actualizar la disponibilidad de las mesas segun las reservas registradas. | Sistema |
| RF-32 | Historial de reservas | El sistema deberá permitir que el encargado consulte el historial de reservas. | Encargado |

### 3.4 Modulo: Gestion de pedidos

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-33 | Realizar pedidos desde la web | El sistema deberá permitir que los clientes realicen pedidos de platos desde la pagina web. | Cliente |
| RF-34 | Seleccionar platos del menú | El sistema deberá permitir que los clientes seleccionen uno o varios platos del menu. | Cliente |
| RF-35 | Indicar cantidad de platos | El sistema deberá permitir que los clientes indiquen la cantidad de cada plato solicitado. | Cliente |
| RF-36 | Calcular importe total | El sistema deberá calcular el importe total de los pedidos segun los productos seleccionados y sus cantidades. | Sistema |
| RF-37 | Asociar pedido a reserva existente | El sistema deberá permitir que los clientes asocien un pedido anticipado a una reserva existente. | Cliente |
| RF-38 | Pedido sin reserva | El sistema deberá permitir que los clientes realicen pedidos sin tener una reserva. | Cliente |
| RF-39 | Tipo de entrega (retiro o delivery) | El sistema deberá permitir que los clientes seleccionen entre retirar el pedido en el restaurante o solicitar delivery. | Cliente |
| RF-40 | Datos para delivery | El sistema deberá solicitar los datos necesarios para realizar una entrega a domicilio (direccion, barrio, referencia). | Sistema |
| RF-41 | Fecha y horario de retiro o entrega | El sistema deberá permitir que los clientes seleccionen una fecha y un horario para recibir o retirar su pedido. | Cliente |
| RF-42 | Consultar pedidos realizados | El sistema deberá permitir que los clientes consulten sus pedidos realizados. | Cliente |
| RF-43 | Consultar estado de pedidos | El sistema deberá permitir que los clientes consulten el estado de sus pedidos (pendiente, en preparacion, listo, entregado, cancelado). | Cliente |
| RF-44 | Consultar todos los pedidos | El sistema deberá permitir que los encargados consulten todos los pedidos recibidos. | Encargado |
| RF-45 | Modificar estado de pedidos | El sistema deberá permitir que los encargados modifiquen el estado de los pedidos. | Encargado |
| RF-46 | Cancelar pedidos | El sistema deberá permitir que los encargados cancelen pedidos segun las condiciones establecidas. | Encargado |
| RF-47 | Consultar detalle de pedido | El sistema deberá permitir que los encargados consulten el detalle de cada pedido (productos, cantidades, precios, subtotal). | Encargado |
| RF-48 | Registrar entrega o retiro | El sistema deberá permitir que los encargados registren la entrega o el retiro de los pedidos. | Encargado |
| RF-49 | Historial de pedidos | El sistema deberá permitir que los encargados consulten el historial de pedidos. | Encargado |

### 3.5 Modulo: Gestion de pagos y señas

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-50 | Calculo automatico de seña | El sistema deberá calcular automaticamente una seña equivalente al 50% del importe total de los platos solicitados. | Sistema |
| RF-51 | Pago de seña | El sistema deberá permitir que los clientes realicen el pago de la seña mediante los medios de pago habilitados (efectivo, tarjeta, transferencia, billetera digital). | Cliente |
| RF-52 | Registro de pagos | El sistema deberá registrar los pagos realizados por los clientes (monto, medio, fecha, estado). | Sistema |
| RF-53 | Consultar señas abonadas | El sistema deberá permitir que los encargados consulten las señas abonadas por cada cliente. | Encargado |
| RF-54 | Calcular saldo pendiente | El sistema deberá calcular y mostrar el saldo pendiente de cada pedido (total menos seña abonada). | Sistema |
| RF-55 | Registrar pago restante | El sistema deberá permitir que los encargados registren el pago restante al momento de la entrega o retiro. | Encargado |
| RF-56 | Actualizar estado segun pago | El sistema deberá actualizar el estado del pedido segun la confirmacion del pago correspondiente (pendiente_seña, pagada, completado). | Sistema |

### 3.6 Modulo: Gestion de usuarios y permisos

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-57 | Registrar encargados | El sistema deberá permitir que el administrador registre nuevos encargados. | Administrador |
| RF-58 | Modificar datos de encargados | El sistema deberá permitir que el administrador modifique los datos de las cuentas de los encargados. | Administrador |
| RF-59 | Desactivar cuentas de encargados | El sistema deberá permitir que el administrador desactive las cuentas de los encargados. | Administrador |
| RF-60 | Asignar y modificar permisos | El sistema deberá permitir que el administrador asigne y modifique los permisos de acceso de los encargados. | Administrador |
| RF-61 | Consultar lista de encargados | El sistema deberá permitir que el administrador consulte la lista de encargados registrados. | Administrador |
| RF-62 | Supervisar actividades de encargados | El sistema deberá permitir que el administrador supervise las actividades realizadas por los encargados (historial de movimientos). | Administrador |

### 3.7 Modulo: Gestion de ventas y reportes

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-63 | Registrar ventas | El sistema deberá registrar las ventas realizadas en el restaurante. | Sistema |
| RF-64 | Consultar ventas registradas | El sistema deberá permitir que los encargados consulten las ventas registradas. | Encargado |
| RF-65 | Reportes de ventas por periodo | El sistema deberá generar reportes de ventas segun un periodo determinado (fecha desde / fecha hasta). | Administrador |
| RF-66 | Ingresos de pedidos anticipados | El sistema deberá permitir consultar los ingresos obtenidos mediante los pedidos anticipados. | Encargado / Administrador |
| RF-67 | Consultar pedidos entregados, retirados y cancelados | El sistema deberá permitir consultar los pedidos entregados, retirados y cancelados. | Encargado / Administrador |
| RF-68 | Limite maximo de pedidos sin reserva | El sistema deberá permitir que el encargado establezca un limite maximo de pedidos sin reserva de mesa, tanto para retiro en el restaurante como para delivery. | Encargado |
| RF-69 | Contabilizar pedidos sin reserva | El sistema deberá contabilizar los pedidos sin reserva recibidos durante un periodo determinado. | Sistema |
| RF-70 | Bloqueo automatico de pedidos sin reserva | El sistema deberá dejar de aceptar automaticamente nuevos pedidos sin reserva cuando se alcance el limite establecido. | Sistema |
| RF-71 | Reactivar recepcion de pedidos | El sistema deberá permitir que el encargado reactive la recepcion de pedidos cuando exista capacidad disponible. | Encargado |

### 3.8 Modulo: Gestion de areas, mesas y horarios (adicionales)

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-72 | Crear y gestionar areas | El sistema deberá permitir que el encargado cree, edite y desactive areas/salones del restaurante. | Encargado |
| RF-73 | Crear y gestionar mesas | El sistema deberá permitir que el encargado cree, edite y desactive mesas (numero, capacidad, area). | Encargado |
| RF-74 | Consultar areas y mesas | El sistema deberá permitir que cualquier usuario consulte las areas y mesas disponibles. | Cliente / Encargado |
| RF-75 | Gestionar horarios de funcionamiento | El sistema deberá permitir que el encargado defina horarios de apertura y cierre por area y dia de la semana. | Encargado |
| RF-76 | Bloquear mesas por motivo | El sistema deberá permitir que el encargado bloquee mesas en rangos de fecha/hora con motivo (mantenimiento, evento privado). | Encargado |
| RF-77 | Consultar bloques de mesa | El sistema deberá permitir que el encargado consulte los bloques existentes de cada mesa. | Encargado |

### 3.9 Modulo: Gestion de productos y stock (adicionales)

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-78 | Gestion de stock y limites de venta | El sistema deberá permitir que el encargado gestione el stock actual y el limite de venta de cada producto. | Encargado |
| RF-79 | Alerta de stock bajo | El sistema deberá alertar automaticamente cuando un producto tenga stock bajo (por debajo del limite de venta). | Sistema |
| RF-80 | Consultar stock de productos | El sistema deberá permitir que el encargado consulte el stock disponible de cada producto. | Encargado |

### 3.10 Modulo: Gestion de delivery y zonas (adicionales)

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-81 | Gestion de zonas de delivery | El sistema deberá permitir que el encargado configure zonas de delivery con costo de envio y tiempo estimado. | Encargado |
| RF-82 | Calcular costo de delivery | El sistema deberá calcular automaticamente el costo de envio segun la zona de delivery seleccionada. | Sistema |
| RF-83 | Asignar repartidor a pedido | El sistema deberá permitir que el encargado asigne un repartidor a un pedido de delivery. | Encargado |
| RF-84 | Estado de entrega de delivery | El sistema deberá permitir que el encargado registre el estado de entrega (en camino, entregado, fallido). | Encargado |

### 3.11 Modulo: Pagos y facturacion (adicionales)

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-85 | Generar factura electronica | El sistema deberá generar una factura electronica con numero, fecha, cliente, items, subtotal, impuestos y total. | Sistema |
| RF-86 | Registrar metodo de pago | El sistema deberá registrar el metodo de pago utilizado en cada pedido o reserva (efectivo, tarjeta, transferencia, billetera digital, QR). | Sistema |
| RF-87 | Cierre de caja diario | El sistema deberá permitir que el encargado realice el cierre de caja diario (apertura, corte, arqueo, diferencias). | Encargado |
| RF-88 | Gestion de cupones y descuentos | El sistema deberá permitir que el encargado cree y gestione cupones y codigos de descuento aplicables a pedidos y reservas. | Encargado |

### 3.12 Modulo: Clientes y fidelidad (adicionales)

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-89 | Historial de cliente | El sistema deberá mantener un historial de cliente con reservas, pedidos, gasto total y platos favoritos. | Sistema |
| RF-90 | Programa de puntos y fidelidad | El sistema deberá permitir acumular puntos por compra y canjearlos por descuentos o platos gratis. | Sistema / Cliente |
| RF-91 | Calificaciones y reseñas de platos | El sistema deberá permitir que los clientes califiquen y reseñen platos (estrellas y comentario). | Cliente |
| RF-92 | Preferencias y alergias del cliente | El sistema deberá permitir que el cliente registre preferencias alimentarias y alergias para el personal del restaurante. | Cliente |
| RF-93 | Reserva recurrente | El sistema deberá permitir que el cliente cree reservas recurrentes (ej. todos los jueves a las 20:00, misma mesa). | Cliente |

### 3.13 Modulo: Notificaciones y comunicacion (adicionales)

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-94 | Notificaciones por WhatsApp | El sistema deberá enviar notificaciones por WhatsApp (confirmacion de reserva, pedido listo, recordatorio). | Sistema |
| RF-95 | Notificaciones por email | El sistema deberá enviar notificaciones por email (factura, recordatorio de reserva, recuperacion de contrasena). | Sistema |
| RF-96 | Notificaciones push en el navegador | El sistema deberá enviar notificaciones push al navegador (nuevo pedido en cocina, reserva cancelada). | Sistema |

### 3.14 Modulo: Reportes y analitica avanzada (adicionales)

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-97 | Dashboard con graficos de ventas | El sistema deberá mostrar un dashboard con graficos visuales de ventas del mes, platos mas vendidos y ocupacion de mesas. | Administrador / Encargado |
| RF-98 | Analisis de rentabilidad por plato | El sistema deberá calcular la rentabilidad de cada plato (ingresos menos costo de insumos). | Administrador |
| RF-99 | Reporte de desempeño de meseros | El sistema deberá generar un reporte del mesero mas efectivo (ventas, propinas, mesas atendidas). | Administrador |
| RF-100 | Reporte de horarios pico | El sistema deberá generar un reporte de horarios pico (dias y horas con mas reservas y pedidos). | Administrador |

### 3.15 Modulo: Administracion y configuracion (adicionales)

| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-101 | Configuracion del restaurante | El sistema deberá permitir que el administrador configure los datos del restaurante (nombre, logo, colores, direccion, horario, impuestos, moneda). | Administrador |
| RF-102 | Gestion de sucursales multiples | El sistema deberá permitir gestionar varios locales/sucursales desde una misma cuenta administrativa. | Administrador |
| RF-103 | Respaldos automaticos de base de datos | El sistema deberá permitir respaldos automaticos y programados de la base de datos, descargables por el administrador. | Administrador |
| RF-104 | Historial de movimientos de auditoria | El sistema deberá registrar en un historial de auditoria todas las acciones realizadas (tabla, registro, accion, usuario, fecha, IP). | Sistema |
| RF-105 | Gestion de roles y permisos avanzada | El sistema deberá permitir que el administrador defina roles personalizados con permisos granulares por modulo. | Administrador |

---

## 4. Requerimientos no funcionales (RNF)

| ID | Nombre | Descripcion |
|----|--------|-------------|
| RNF-01 | Portabilidad de entorno | La misma aplicacion debe ejecutarse en XAMPP, Docker Compose y hosting remoto sin cambios de logica. |
| RNF-02 | Stack tecnologico | PHP para backend/API, JavaScript vanilla para cliente, MySQL/MariaDB para BD, HTML/CSS para presentacion. Sin frameworks innecesarios. |
| RNF-03 | POO | Logica de backend organizada con Programacion Orientada a Objetos (Controladores, DAO, Modelos, Servicios). |
| RNF-04 | Seguridad de contrasenas | Almacenamiento con bcrypt (`password_hash` / `password_verify`). Nunca texto plano ni SHA-256 para contrasenas. |
| RNF-05 | Sesiones seguras | Cookies de sesion `HttpOnly`, `Secure` (HTTPS), `SameSite=Lax`. |
| RNF-06 | Consultas seguras | Uso de consultas preparadas (PDO) para evitar inyeccion SQL. |
| RNF-07 | Versionamiento | Git y GitHub con ramas `main` y `develop`. |
| RNF-08 | Accesibilidad | Objetivo de referencia WCAG 2.2 nivel AA. |
| RNF-09 | Diseno adaptable | Funcionar en computadoras, tablets y celulares (responsive). |
| RNF-10 | Manejo de errores | Respuestas HTTP estandar, mensajes sin revelar rutas internas ni consultas SQL. |
| RNF-11 | Configuracion por entorno | Variables de entorno; secretos nunca en el repositorio. |
| RNF-12 | Migraciones | Esquema de BD versionado y reproducible en los tres entornos. |

---

## 5. Reglas de negocio (RB)

| ID | Regla | Descripcion |
|----|-------|-------------|
| RB-01 | Una mesa, una reserva por horario | Una mesa no puede tener dos reservas confirmadas que se superpongan en el mismo intervalo de tiempo. |
| RB-02 | Disponibilidad derivada | La disponibilidad se calcula a partir de horarios de funcionamiento, reservas existentes, bloqueos y reglas aplicables. |
| RB-03 | Validacion en servidor | Toda operacion de reserva y pedido se valida en servidor y en BD, independientemente de validaciones del cliente. |
| RB-04 | Concurrencia protegida | La creacion de reserva usa transaccion y bloqueo para evitar doble reserva ante peticiones simultaneas. |
| RB-05 | Estados de reserva | Estados validos: pendiente, confirmada, cancelada, vencida, completada. Transiciones definidas. |
| RB-06 | Cancelacion libera mesa | Al cancelar una reserva, la mesa queda disponible para otros usuarios. |
| RB-07 | Acceso a propios recursos | Un cliente solo puede ver/cancelar sus propias reservas y pedidos. Un encargado puede gestionar lo de su area. Un administrador puede ver/gestionar todo. |
| RB-08 | Horarios dentro de funcionamiento | Una reserva solo puede crearse dentro del horario de funcionamiento de la mesa/area y en turnos validos. |
| RB-09 | Contrasena segura | Las contrasenas se hashean con bcrypt. |
| RB-10 | Sesion expirada | Las sesiones tienen expiracion; tras ella el usuario debe autenticarse de nuevo. |
| RB-11 | Pedido asociado a mesa y/o usuario | Todo pedido debe pertenecer a un usuario autenticado y opcionalmente a una mesa existente. |
| RB-12 | Estados de pedido | Estados validos: pendiente, en_preparacion, listo, entregado, cancelado. |
| RB-13 | Seña del 50% | La seña se calcula automaticamente como el 50% del importe total de los platos solicitados. |
| RB-14 | Saldo pendiente | El saldo pendiente es el importe total menos la seña ya abonada. |
| RB-15 | Estado segun pago | El estado del pedido se actualiza segun la confirmacion del pago de la seña y del saldo restante. |
| RB-16 | Limite de pedidos sin reserva | Cuando se alcanza el limite maximo de pedidos sin reserva, el sistema deja de aceptarlos automaticamente. |
| RB-17 | Descuento de stock | Al confirmar un pedido, el sistema descuenta automaticamente el stock de los productos utilizados. |
| RB-18 | Stock bajo | Un producto esta en stock bajo cuando su stock actual es menor o igual al limite de venta definido. |
| RB-19 | Historial de auditoria | Toda accion de creacion, modificacion, eliminacion o cambio de estado se registra en el historial de movimientos. |
| RB-20 | Unico administrador activo | No se puede desactivar el unico administrador activo del sistema. |

---

## 6. Restricciones

| ID | Restriccion | Origen |
|----|-------------|--------|
| RES-01 | Sin frameworks innecesarios | Instruccion de planificacion. |
| RES-02 | Pagos y facturacion como mejora aprobada | Pagos y facturacion electronica requieren proveedor y cumplimiento normativo. |
| RES-03 | Notificaciones como mejora opcional | Notificaciones por email/WhatsApp/push requieren servicios externos. |
| RES-04 | SHA-256 solo con justificacion tecnica | No para contrasenas ni firma JWT. |
| RES-05 | JWT solo si se requiere API stateless | Actualmente se usa sesion de cookie nativa de PHP. |
| RES-06 | Tres entornos soportados | XAMPP, Docker y hosting remoto. |
| RES-07 | Sin secretos en el repositorio | Variables de entorno para claves sensibles. |
| RES-08 | Identificadores en espanol | Nomenclatura de BD, archivos y codigo en espanol. |

---

## 7. Matriz de trazabilidad — Referencia RF-01 a RF-71 vs Implementacion actual

| ID | Modulo | Estado en codigo | Archivos clave |
|----|--------|------------------|----------------|
| RF-01 | Clientes y acceso | Implementado | `src/Controller/AuthController.php`, `public/login.html`, `public/registro.html` |
| RF-02 | Clientes y acceso | Implementado | `src/Controller/AuthController.php` |
| RF-03 | Clientes y acceso | Implementado | `src/Controller/AuthController.php` |
| RF-04 | Clientes y acceso | Implementado | `src/Service/ServicioDeAutenticacion.php`, `public/panel-cliente.html` |
| RF-05 | Clientes y acceso | Implementado | `src/Controller/AuthController.php`, `src/Dao/RecuperarContrasenaDao.php` |
| RF-06 | Clientes y acceso | Implementado | `src/Service/ServicioDeAutenticacion.php` |
| RF-07 | Menu | Implementado | `src/Controller/ProductoController.php`, `public/carta.html`, `public/panel-cliente.html` |
| RF-08 | Menu | Implementado | `public/carta.html`, `public/panel-cliente.html` |
| RF-09 | Menu | Parcial (pendiente implementacion completa de combos) | `src/Controller/ProductoController.php` |
| RF-10 | Menu | Implementado | `src/Controller/ProductoController.php` |
| RF-11 | Menu | Implementado | `src/Controller/ProductoController.php` |
| RF-12 | Menu | Implementado | `src/Controller/ProductoController.php` |
| RF-13 | Menu | Implementado | `src/Controller/ProductoController.php` |
| RF-14 | Menu | Implementado | `src/Controller/ProductoController.php` |
| RF-15 | Menu | Parcial (estructura de combos por definir) | `src/Controller/ProductoController.php` |
| RF-16 | Menu | Parcial | `src/Controller/ProductoController.php` |
| RF-17 | Menu | Implementado | `public/panel-cliente.html` (menu semanal) |
| RF-18 | Menu | Implementado | `src/Controller/ProductoController.php` |
| RF-19 | Reservas | Implementado | `src/Controller/MesaController.php`, `public/panel-cliente.html` |
| RF-20 | Reservas | Implementado | `src/Controller/MesaController.php`, `src/Service/ServicioDeDisponibilidad.php` |
| RF-21 | Reservas | Implementado | `src/Controller/ReservaController.php` |
| RF-22 | Reservas | Implementado | `src/Validator/ValidadorDeReserva.php` |
| RF-23 | Reservas | Implementado | `public/panel-cliente.html` |
| RF-24 | Reservas | Implementado | `src/Controller/ReservaController.php` |
| RF-25 | Reservas | Implementado | `src/Controller/ReservaController.php` |
| RF-26 | Reservas | Implementado | `src/Controller/ReservaController.php` |
| RF-27 | Reservas | Implementado | `src/Controller/ReservaController.php` |
| RF-28 | Reservas | Implementado | `src/Controller/ReservaController.php` |
| RF-29 | Reservas | Implementado | `src/Controller/ReservaController.php` |
| RF-30 | Reservas | Implementado | `src/Controller/ReservaController.php`, `src/Dao/MesaDao.php` |
| RF-31 | Reservas | Implementado | `src/Service/ServicioDeDisponibilidad.php` |
| RF-32 | Reservas | Implementado | `src/Controller/ReservaController.php` |
| RF-33 | Pedidos | Implementado | `src/Controller/PedidoController.php`, `public/panel-cliente.html` |
| RF-34 | Pedidos | Implementado | `public/panel-cliente.html` |
| RF-35 | Pedidos | Implementado | `public/panel-cliente.html` |
| RF-36 | Pedidos | Implementado | `src/Service/ServicioDePedidos.php` |
| RF-37 | Pedidos | Implementado | `src/Controller/PedidoController.php` |
| RF-38 | Pedidos | Implementado | `src/Controller/PedidoController.php` |
| RF-39 | Pedidos | Implementado | `public/panel-cliente.html` |
| RF-40 | Pedidos | Implementado | `public/panel-cliente.html` |
| RF-41 | Pedidos | Implementado | `public/panel-cliente.html` |
| RF-42 | Pedidos | Implementado | `public/panel-cliente.html` |
| RF-43 | Pedidos | Implementado | `public/panel-cliente.html` |
| RF-44 | Pedidos | Implementado | `src/Controller/PedidoController.php`, `public/panel-encargado.html` |
| RF-45 | Pedidos | Implementado | `src/Controller/PedidoController.php` |
| RF-46 | Pedidos | Implementado | `src/Controller/PedidoController.php` |
| RF-47 | Pedidos | Implementado | `src/Controller/PedidoController.php` |
| RF-48 | Pedidos | Implementado | `src/Controller/PedidoController.php` |
| RF-49 | Pedidos | Implementado | `src/Controller/PedidoController.php` |
| RF-50 | Pagos y señas | Implementado | `src/Service/ServicioDePedidos.php` |
| RF-51 | Pagos y señas | Parcial (registro de pago, no pasarela externa) | `src/Controller/PedidoController.php` |
| RF-52 | Pagos y señas | Implementado | `src/Controller/PedidoController.php` |
| RF-53 | Pagos y señas | Implementado | `src/Controller/PedidoController.php` |
| RF-54 | Pagos y señas | Implementado | `src/Service/ServicioDePedidos.php` |
| RF-55 | Pagos y señas | Implementado | `src/Controller/PedidoController.php` |
| RF-56 | Pagos y señas | Implementado | `src/Controller/PedidoController.php` |
| RF-57 | Usuarios y permisos | Implementado | `src/Controller/UsuarioAdminController.php` |
| RF-58 | Usuarios y permisos | Implementado | `src/Controller/UsuarioAdminController.php` |
| RF-59 | Usuarios y permisos | Implementado | `src/Controller/UsuarioAdminController.php` |
| RF-60 | Usuarios y permisos | Parcial (roles basicos, no granulares) | `src/Middleware/AuthMiddleware.php` |
| RF-61 | Usuarios y permisos | Implementado | `src/Controller/UsuarioAdminController.php` |
| RF-62 | Usuarios y permisos | Implementado | `src/Dao/...`, `public/panel-reportes.html` |
| RF-63 | Ventas y reportes | Implementado | `src/Controller/PedidoController.php` |
| RF-64 | Ventas y reportes | Implementado | `public/panel-reportes.html` |
| RF-65 | Ventas y reportes | Implementado | `src/Controller/...` (endpoints de reportes) |
| RF-66 | Ventas y reportes | Implementado | `public/panel-reportes.html` |
| RF-67 | Ventas y reportes | Implementado | `public/panel-reportes.html` |
| RF-68 | Ventas y reportes | Implementado | `public/index.php` (manejarLimitePedidosSinReserva), `public/panel-encargado.html` (vista límite pedidos) |
| RF-69 | Ventas y reportes | Implementado | `src/Controller/PedidoController.php` (contador al crear pedido) |
| RF-70 | Ventas y reportes | Implementado | `src/Controller/PedidoController.php` (bloqueo con 403 al alcanzar límite) |
| RF-71 | Ventas y reportes | Implementado | `public/index.php` (endpoint POST para reactivar/establecer límite) |
| RF-72 | Áreas, mesas, horarios | Implementado (parcial) | `src/Controller/AreaController.php`, `src/Controller/MesaAdminController.php`, `src/Controller/HorarioAdminController.php` |
| RF-73 | Áreas, mesas, horarios | Implementado (parcial) | `src/Controller/MesaAdminController.php` |
| RF-74 | Áreas, mesas, horarios | Implementado | `src/Controller/AreaController.php`, `src/Controller/MesaController.php` |
| RF-75 | Áreas, mesas, horarios | Implementado | `src/Controller/HorarioAdminController.php` |
| RF-76 | Áreas, mesas, horarios | Implementado | `src/Controller/BloqueAdminController.php`, `public/panel-encargado.html` |
| RF-77 | Áreas, mesas, horarios | Implementado | `public/panel-encargado.html` (vista bloques) |
| RF-78 | Productos y stock | Implementado (parcial) | `src/Controller/MesaAdminController.php`, `src/Service/ServicioDeMesas.php` |
| RF-79 | Productos y stock | Implementado | `public/panel-encargado.html` (vista stock con alerta visual) |
| RF-80 | Productos y stock | Implementado | `public/index.php` (endpoint stock/bajo) |
| RF-81 | Delivery y zonas | Implementado | `public/index.php` (manejarZonasDelivery), `public/panel-encargado.html`, `public/panel-cliente.html` |
| RF-82 | Delivery y zonas | Implementado (parcial) | `public/index.php` (zonas con costo_envio), `public/panel-cliente.html` (costo en total) |
| RF-83 | Delivery y repartidores | Implementado | `public/index.php` (manejarRepartidores), `public/panel-encargado.html`, `public/panel-cliente.html` |
| RF-84 | Delivery y repartidores | Pendiente | Endpoint para cambiar estado de entrega de pedido (en camino, entregado, fallido) |
| RF-85 | Pagos y facturación | Pendiente | Requiere proveedor de pagos y cumplimiento normativo |
| RF-86 | Pagos y facturación | Implementado (parcial) | `src/Controller/PedidoController.php` (campo estado_seña, método de pago por implementar) |
| RF-87 | Pagos y facturación | Pendiente | Cierre de caja diario |
| RF-88 | Pagos y descuentos | Implementado | `public/index.php` (manejarCupones), `public/panel-encargado.html` |
| RF-89 | Clientes y fidelidad | Pendiente | Historial de cliente con platos favoritos |
| RF-90 | Clientes y fidelidad | Implementado | `public/index.php` (manejarPuntosFidelidad), `public/panel-cliente.html` (vista puntos) |
| RF-91 | Clientes y fidelidad | Implementado | `public/index.php` (manejarCalificaciones), `public/panel-cliente.html` (vista calificar platos) |
| RF-92 | Clientes y fidelidad | Implementado | `public/index.php` (manejarPreferencias), `public/panel-cliente.html` (vista preferencias) |
| RF-93 | Clientes y fidelidad | Pendiente (tabla creada) | `migrations/011` (tabla reservas_recurrentes), faltan endpoints y frontend |
| RF-94 | Notificaciones | Pendiente | Requiere servicio WhatsApp externo |
| RF-95 | Notificaciones | Pendiente | Requiere servicio de email externo |
| RF-96 | Notificaciones | Pendiente | Requiere servicio de push notifications |
| RF-97 | Reportes avanzados | Pendiente | Dashboard con gráficos visuales |
| RF-98 | Reportes avanzados | Pendiente | Análisis de rentabilidad por plato |
| RF-99 | Reportes avanzados | Pendiente | Reporte de desempeño de meseros |
| RF-100 | Reportes avanzados | Pendiente | Reporte de horarios pico |
| RF-101 | Administración y configuración | Implementado | `public/index.php` (manejarConfiguracionSistema) |
| RF-102 | Administración y configuración | Pendiente | Gestión de sucursales múltiples |
| RF-103 | Administración y configuración | Pendiente | Respaldos automáticos de base de datos |
| RF-104 | Administración y configuración | Implementado (parcial) | `src/Dao/...`, `public/index.php` (historial_movimientos) |
| RF-105 | Administración y configuración | Pendiente | Roles y permisos avanzados granulares |

---

## 8. Pendientes de implementación (RF aún sin completar)

Los siguientes requerimientos aún requieren desarrollo adicional:

- **RF-84:** Estado de entrega de delivery (en camino, entregado, fallido) — endpoint para cambiar estado de envío.
- **RF-85:** Facturación electrónica — requiere proveedor de pagos y cumplimiento normativo.
- **RF-87:** Cierre de caja diario — arqueo de caja, apertura/corte.
- **RF-89:** Historial de cliente con platos favoritos.
- **RF-93:** Reservas recurrentes — tabla creada en migración 011, faltan endpoints y frontend.
- **RF-94 a RF-96:** Notificaciones WhatsApp/email/push — requieren servicios externos.
- **RF-97 a RF-100:** Reportes avanzados con gráficos, rentabilidad, desempeño de meseros, horarios pico.
- **RF-102:** Gestión de sucursales múltiples.
- **RF-103:** Respaldos automáticos de base de datos.
- **RF-105:** Roles y permisos avanzados granulares.

Todos los demás RF (RF-01 a RF-83, RF-86, RF-88, RF-90 a RF-92, RF-101, RF-104) están **implementados** en el código.

---

## 9. Hoja de ruta sugerida para completamiento

| Fase | Requerimientos | Entregable |
|------|---------------|------------|
| Fase 1 | RF-84, RF-87 | Estado de entrega delivery, cierre de caja |
| Fase 2 | RF-85, RF-89 | Facturación electrónica, historial de cliente |
| Fase 3 | RF-93, RF-94 a RF-96 | Reservas recurrentes, notificaciones |
| Fase 4 | RF-97 a RF-100 | Reportes avanzados con gráficos |
| Fase 5 | RF-102, RF-103, RF-105 | Sucursales múltiples, respaldos, roles avanzados |
