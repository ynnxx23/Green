# Diccionario de Datos — Sistema de Gestión de Restaurante "Green en la Casa Blanca"

*Entregable 13 — Diccionario de datos del modelo físico. Esquema simplificado tras la migración 025: **23 tablas**.*

## Resumen

| Entidad | Filas | Descripción |
|---|---|---|
| `roles` | 3 | Roles del sistema (cliente, encargado, administrador). |
| `usuarios` | 5 | Personas registradas con credenciales y rol. |
| `areas` | 3 | Salones o zonas físicas del restaurante. |
| `mesas` | 10 | Mesas físicas con capacidad, dentro de un área. |
| `horarios_funcionamiento` | 21 | Horario de apertura y cierre por área y día. |
| `disponibilidad_mesa` | 64 | Cupos de reserva por fecha y franja horaria. |
| `bloques_mesa` | 0 | Bloqueos de una mesa en un rango de fechas. |
| `reservas` | 0 | Reservas de mesa confirmadas con datos del cliente. |
| `categorias` | 5 | Categorías de la carta (Entradas, Platos principales, Postres, Bebidas, Coctelería). |
| `productos` | 31 | Catálogo de platos y bebidas con precio e imagen. |
| `stock_producto` | 30 | Inventario y límite de venta por producto (1:1). |
| `pedidos` | 6 | Pedidos con tipo, seña, saldo y estado de entrega. |
| `detalle_pedidos` | 18 | Líneas de cada pedido: producto, cantidad y precio unitario. |
| `menu_semanal` | 1 | Menú publicado por semana. |
| `menu_semanal_items` | 0 | Platos del menú semanal con precio especial. |
| `zonas_delivery` | 0 | Zonas de entrega con costo y tiempo estimado. |
| `repartidores` | 0 | Personal de reparto con su usuario asociado. |
| `configuracion_sistema` | 12 | Parámetros del restaurante (límites de pedidos, seña, moneda). |
| `contador_pedidos_sin_reserva` | 1 | Contador diario de pedidos sin reserva por modalidad. |
| `historial_movimientos` | 0 | Auditoría de todos los movimientos del sistema. |
| `intentos_login` | 30 | Auditoría de intentos de ingreso (control de fuerza bruta). |
| `rate_limit_state` | 2 | Cubo de tokens del límite de peticiones. |
| `migraciones_aplicadas` | 18 | Registro de migraciones aplicadas a la base de datos. |

## ROLES (`roles`)

Roles del sistema (cliente, encargado, administrador).

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_rol` | `int(10) unsigned` | NO | PK | Identificador del rol (PK). |
| `nombre` | `varchar(50)` | NO | UK | Nombre del rol: cliente, encargado, administrador. |
| `descripcion` | `varchar(200)` | SÍ (def. `NULL`) |  | Descripción del rol. |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## USUARIOS (`usuarios`)

Personas registradas con credenciales y rol.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_usuario` | `int(10) unsigned` | NO | PK |  |
| `nombre` | `varchar(100)` | NO |  |  |
| `email` | `varchar(150)` | NO | UK |  |
| `telefono` | `varchar(30)` | SÍ (def. `NULL`) |  |  |
| `contrasena_hash` | `varchar(255)` | NO |  | Hash bcrypt de la contraseña. |
| `id_rol` | `int(10) unsigned` | NO | FK → roles | Rol asignado: 1 cliente, 2 encargado, 4 administrador. |
| `activo` | `tinyint(1)` | NO (def. `1`) |  | Indica si el usuario puede iniciar sesión. |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |
| `fecha_actualizacion` | `datetime` | SÍ (def. `NULL`) |  |  |

## AREAS (`areas`)

Salones o zonas físicas del restaurante.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_area` | `int(10) unsigned` | NO | PK |  |
| `nombre` | `varchar(150)` | NO |  |  |
| `descripcion` | `varchar(255)` | SÍ (def. `NULL`) |  |  |
| `activo` | `tinyint(1)` | NO (def. `1`) |  | Indica si el área está habilitada. |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## MESAS (`mesas`)

Mesas físicas con capacidad, dentro de un área.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_mesa` | `int(10) unsigned` | NO | PK |  |
| `id_area` | `int(10) unsigned` | NO | FK → areas | Área a la que pertenece la mesa. |
| `numero_mesa` | `varchar(20)` | NO |  | Identificador legible de la mesa. |
| `capacidad` | `int(11)` | NO (def. `1`) |  | Cantidad de comensales que admite. |
| `activo` | `tinyint(1)` | NO (def. `1`) |  |  |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## HORARIOS_FUNCIONAMIENTO (`horarios_funcionamiento`)

Horario de apertura y cierre por área y día.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_horario` | `int(10) unsigned` | NO | PK |  |
| `id_area` | `int(10) unsigned` | NO | FK → areas |  |
| `dia_semana` | `tinyint(3) unsigned` | NO |  | Día de la semana (0 = domingo). |
| `hora_apertura` | `time` | NO |  | Hora de apertura del área ese día. |
| `hora_cierre` | `time` | NO |  | Hora de cierre del área ese día. |
| `activo` | `tinyint(1)` | NO (def. `1`) |  |  |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## DISPONIBILIDAD_MESA (`disponibilidad_mesa`)

Cupos de reserva por fecha y franja horaria.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_disponibilidad` | `int(10) unsigned` | NO | PK |  |
| `fecha` | `date` | NO | Índice | Fecha del cupo. |
| `hora_inicio` | `time` | NO |  | Inicio de la franja horaria. |
| `hora_fin` | `time` | NO |  | Fin de la franja horaria. |
| `mesas_disponibles` | `int(11)` | NO (def. `1`) |  | Cantidad de mesas que se pueden reservar en esa franja. |
| `activo` | `tinyint(1)` | NO (def. `1`) |  |  |
| `creado_por` | `int(10) unsigned` | SÍ (def. `NULL`) | FK → usuarios | Usuario que generó el cupo. |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## BLOQUES_MESA (`bloques_mesa`)

Bloqueos de una mesa en un rango de fechas.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_bloque` | `int(10) unsigned` | NO | PK |  |
| `id_mesa` | `int(10) unsigned` | NO | FK → mesas | Mesa bloqueada. |
| `fecha_inicio` | `datetime` | NO | Índice | Inicio del bloqueo. |
| `fecha_fin` | `datetime` | NO |  | Fin del bloqueo. |
| `motivo` | `varchar(200)` | SÍ (def. `NULL`) |  | Motivo del bloqueo. |
| `creado_por` | `int(10) unsigned` | SÍ (def. `NULL`) | FK → usuarios | Usuario que registró el bloqueo. |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## RESERVAS (`reservas`)

Reservas de mesa confirmadas con datos del cliente.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_reserva` | `int(10) unsigned` | NO | PK |  |
| `id_mesa` | `int(10) unsigned` | NO | FK → mesas | Mesa reservada. |
| `id_usuario` | `int(10) unsigned` | NO | FK → usuarios | Cliente que reservó. |
| `comensales` | `int(10) unsigned` | NO (def. `1`) |  | Cantidad de personas. |
| `telefono` | `varchar(30)` | SÍ (def. `NULL`) |  |  |
| `ocasion` | `varchar(100)` | SÍ (def. `NULL`) |  |  |
| `fecha_reserva` | `date` | NO | Índice | Día de la reserva. |
| `hora_inicio` | `time` | NO |  | Hora de inicio de la reserva. |
| `hora_fin` | `time` | NO |  | Hora de fin de la reserva. |
| `estado` | `varchar(20)` | NO (def. `'pendiente'`) | Índice | Estado: pendiente, confirmada, cancelada, vencida, completada. |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |
| `fecha_actualizacion` | `datetime` | SÍ (def. `NULL`) |  |  |
| `observaciones` | `varchar(500)` | SÍ (def. `NULL`) |  | Observaciones del cliente (alergias, ocasión, etc.). |

## CATEGORIAS (`categorias`)

Categorías de la carta (Entradas, Platos principales, Postres, Bebidas, Coctelería).

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_categoria` | `int(10) unsigned` | NO | PK |  |
| `nombre` | `varchar(50)` | NO | UK | Nombre de la categoría (único). |
| `activo` | `tinyint(1)` | NO (def. `1`) |  | Indica si la categoría se muestra en la carta. |

## PRODUCTOS (`productos`)

Catálogo de platos y bebidas con precio e imagen.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_producto` | `int(10) unsigned` | NO | PK |  |
| `nombre` | `varchar(150)` | NO |  |  |
| `id_categoria` | `int(10) unsigned` | SÍ (def. `NULL`) | FK → categorias | Categoría a la que pertenece el producto. |
| `precio` | `decimal(10,2)` | NO |  | Precio de venta. |
| `imagen_url` | `varchar(500)` | SÍ (def. `NULL`) |  | URL de la imagen del plato. |
| `imagen_public_id` | `varchar(200)` | SÍ (def. `NULL`) |  | Identificador de la imagen en el gestor de imágenes. |
| `activo` | `tinyint(1)` | NO (def. `1`) |  | Indica si el producto se muestra en la carta. |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## STOCK_PRODUCTO (`stock_producto`)

Inventario y límite de venta por producto (1:1).

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_stock` | `int(10) unsigned` | NO | PK |  |
| `id_producto` | `int(10) unsigned` | NO | UK | Producto al que corresponde el stock (1:1). |
| `stock_actual` | `int(11)` | NO (def. `0`) |  | Unidades disponibles. |
| `limite_venta` | `int(11)` | NO (def. `0`) |  | Máximo de unidades que se pueden vender (0 = sin límite). |
| `activo` | `tinyint(1)` | NO (def. `1`) |  |  |
| `fecha_actualizacion` | `datetime` | SÍ (def. `NULL`) |  |  |

## PEDIDOS (`pedidos`)

Pedidos con tipo, seña, saldo y estado de entrega.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_pedido` | `int(10) unsigned` | NO | PK |  |
| `id_mesa` | `int(10) unsigned` | SÍ (def. `NULL`) | FK → mesas | Mesa donde se consumió el pedido. |
| `id_mesero` | `int(10) unsigned` | SÍ (def. `NULL`) | FK → usuarios | Mesero responsable del pedido. |
| `id_usuario` | `int(10) unsigned` | SÍ (def. `NULL`) | FK → usuarios | Cliente que realizó el pedido. |
| `fecha_pedido` | `datetime` | NO (def. `current_timestamp()`) | Índice |  |
| `estado` | `varchar(20)` | NO (def. `'pendiente'`) | Índice | Estado: pendiente, en_preparacion, listo, entregado, cancelado. |
| `total` | `decimal(10,2)` | NO (def. `0.00`) |  | Total del pedido. |
| `fecha_actualizacion` | `datetime` | SÍ (def. `NULL`) |  |  |
| `tipo_pedido` | `enum('con_reserva','para_llevar','domicilio')` | NO (def. `'con_reserva'`) | Índice | Tipo: con_reserva, para_llevar, domicilio. |
| `senia` | `decimal(10,2)` | NO (def. `0.00`) |  | Seña anticipada (50 % por defecto). |
| `estado_senia` | `enum('pendiente','pagada','devuelta')` | NO (def. `'pendiente'`) |  | Estado de la seña: pendiente, pagada, devuelta. |
| `saldo_pendiente` | `decimal(12,2)` | NO (def. `0.00`) | Índice | Saldo restante tras la seña. |
| `direccion_domicilio` | `varchar(255)` | SÍ (def. `NULL`) |  | Dirección de entrega (solo domicilio). |
| `numero_pedido` | `varchar(30)` | SÍ (def. `NULL`) | Índice | Código legible PED-YYYYMMDD-XXXX. |
| `id_zona_delivery` | `int(10) unsigned` | SÍ (def. `NULL`) | FK → zonas_delivery | Zona de entrega (solo domicilio). |
| `costo_envio` | `decimal(10,2)` | NO (def. `0.00`) |  | Costo de envío (solo domicilio). |
| `id_repartidor` | `int(10) unsigned` | SÍ (def. `NULL`) | FK → repartidores | Repartidor asignado (solo domicilio). |
| `estado_entrega` | `enum('pendiente','en_camino','entregado','fallido')` | NO (def. `'pendiente'`) | Índice | Estado de entrega a domicilio. |
| `fecha_pedido_hora` | `datetime` | SÍ (def. `NULL`) |  |  |
| `observaciones` | `varchar(255)` | SÍ (def. `NULL`) |  | Observaciones del pedido de domicilio. |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## DETALLE_PEDIDOS (`detalle_pedidos`)

Líneas de cada pedido: producto, cantidad y precio unitario.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_detalle` | `int(10) unsigned` | NO | PK |  |
| `id_pedido` | `int(10) unsigned` | NO | FK → pedidos | Pedido al que pertenece la línea. |
| `id_producto` | `int(10) unsigned` | NO | FK → productos | Producto vendido. |
| `cantidad` | `int(11)` | NO (def. `1`) |  | Unidades vendidas. |
| `precio_unitario` | `decimal(10,2)` | NO |  | Precio unitario al momento de la venta (snapshot). |

## MENU_SEMANAL (`menu_semanal`)

Menú publicado por semana.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_menu_semanal` | `int(10) unsigned` | NO | PK |  |
| `semana_inicio` | `date` | NO | UK | Lunes de la semana (único). |
| `semana_fin` | `date` | NO |  | Domingo de la semana. |
| `activo` | `tinyint(1)` | NO (def. `1`) |  |  |
| `creado_por` | `int(10) unsigned` | SÍ (def. `NULL`) | FK → usuarios | Usuario que publicó el menú. |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## MENU_SEMANAL_ITEMS (`menu_semanal_items`)

Platos del menú semanal con precio especial.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_item` | `int(10) unsigned` | NO | PK |  |
| `id_menu_semanal` | `int(10) unsigned` | NO | FK → menu_semanal | Menú al que pertenece el ítem. |
| `id_producto` | `int(10) unsigned` | NO | FK → productos | Producto incluido en el menú. |
| `disponible` | `tinyint(1)` | NO (def. `1`) |  | Indica si el producto está disponible esa semana. |
| `precio_semanal` | `decimal(10,2)` | SÍ (def. `NULL`) |  | Precio especial de la semana (si aplica). |

## ZONAS_DELIVERY (`zonas_delivery`)

Zonas de entrega con costo y tiempo estimado.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_zona` | `int(10) unsigned` | NO | PK |  |
| `nombre` | `varchar(100)` | NO |  |  |
| `costo_envio` | `decimal(10,2)` | NO (def. `0.00`) |  | Costo de envío a la zona. |
| `tiempo_estimado_minutos` | `int(10) unsigned` | NO (def. `45`) |  | Tiempo estimado de entrega. |
| `cobertura` | `text` | SÍ (def. `NULL`) |  | Descripción del área de cobertura. |
| `activa` | `tinyint(1)` | NO (def. `1`) | Índice | Indica si la zona está habilitada. |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## REPARTIDORES (`repartidores`)

Personal de reparto con su usuario asociado.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_repartidor` | `int(10) unsigned` | NO | PK |  |
| `id_usuario` | `int(10) unsigned` | SÍ (def. `NULL`) | UK | Usuario asociado al repartidor (1:1). |
| `nombre` | `varchar(100)` | NO |  |  |
| `telefono` | `varchar(50)` | SÍ (def. `NULL`) |  | Teléfono de contacto. |
| `activo` | `tinyint(1)` | NO (def. `1`) | Índice | Indica si el repartidor está habilitado. |
| `fecha_creacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## CONFIGURACION_SISTEMA (`configuracion_sistema`)

Parámetros del restaurante (límites de pedidos, seña, moneda).

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `clave` | `varchar(100)` | NO | PK | Clave del parámetro (PK). |
| `valor` | `text` | SÍ (def. `NULL`) |  | Valor del parámetro. |
| `descripcion` | `varchar(255)` | SÍ (def. `NULL`) |  | Descripción del parámetro. |
| `fecha_actualizacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |
| `actualizado_por` | `int(10) unsigned` | SÍ (def. `NULL`) | FK → usuarios | Usuario que lo modificó por última vez. |

## CONTADOR_PEDIDOS_SIN_RESERVA (`contador_pedidos_sin_reserva`)

Contador diario de pedidos sin reserva por modalidad.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `fecha` | `date` | NO | PK | Día del conteo (PK compuesta). |
| `tipo_pedido` | `enum('para_llevar','domicilio')` | NO | PK | Modalidad: para_llevar o domicilio (PK compuesta). |
| `cantidad_pedidos` | `int(10) unsigned` | NO (def. `0`) |  | Cantidad de pedidos sin reserva ese día. |
| `fecha_actualizacion` | `datetime` | NO (def. `current_timestamp()`) |  |  |

## HISTORIAL_MOVIMIENTOS (`historial_movimientos`)

Auditoría de todos los movimientos del sistema.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id_historial` | `int(10) unsigned` | NO | PK |  |
| `tabla_afectada` | `varchar(50)` | NO | Índice | Tabla sobre la que se actuó. |
| `id_registro` | `int(10) unsigned` | SÍ (def. `NULL`) |  | Identificador del registro afectado. |
| `accion` | `varchar(20)` | NO | Índice | Acción realizada (INSERT, UPDATE, ASIGNAR_MESERO, etc.). |
| `detalles` | `longtext` | SÍ (def. `NULL`) |  | Detalle en formato JSON. |
| `id_usuario` | `int(10) unsigned` | SÍ (def. `NULL`) | FK → usuarios | Usuario que realizó el movimiento. |
| `fecha_movimiento` | `datetime` | NO (def. `current_timestamp()`) |  | Fecha y hora del movimiento. |
| `direccion_ip` | `varchar(45)` | SÍ (def. `NULL`) |  | Dirección IP del usuario. |

## INTENTOS_LOGIN (`intentos_login`)

Auditoría de intentos de ingreso (control de fuerza bruta).

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `email` | `varchar(150)` | NO | Índice | Correo con el que se intentó ingresar. |
| `exitoso` | `tinyint(1)` | NO |  | Indica si el intento fue exitoso. |
| `fecha_intento` | `datetime` | NO (def. `current_timestamp()`) |  | Fecha y hora del intento. |
| `direccion_ip` | `varchar(45)` | SÍ (def. `NULL`) |  | Dirección IP del intento. |

## RATE_LIMIT_STATE (`rate_limit_state`)

Cubo de tokens del límite de peticiones.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `clave` | `varchar(128)` | NO | PK | Clave del cubo de tokens (PK). |
| `tokens` | `decimal(10,4)` | NO (def. `0.0000`) |  | Tokens restantes en el cubo. |
| `ultima_actualizacion` | `datetime` | NO |  | Última actualización del cubo. |

## MIGRACIONES_APLICADAS (`migraciones_aplicadas`)

Registro de migraciones aplicadas a la base de datos.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `version` | `varchar(50)` | NO | PK | Versión de la migración (PK). |
| `fecha_aplicacion` | `datetime` | NO (def. `current_timestamp()`) |  | Fecha de aplicación. |

