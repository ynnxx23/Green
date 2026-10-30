# 05 - Sistema funcional y pruebas

Documentación de las pruebas realizadas, errores corregidos, ejecución del sistema y conclusión del proyecto "Green en la Casa Blanca".

## 1. Pruebas realizadas

Se verificó el correcto funcionamiento de las siguientes funcionalidades:

- Login y registro como cliente.
- Login y registro como encargado.
- Creación de reserva en fecha disponible (día verde) con éxito.
- Rechazo de reserva en fecha no disponible (día gris).
- Creación de pedido respetando stock y límite de ventas por plato.
- Cambio de estado de pedido por encargado.
- Cambio de estado de reserva por administrador.
- Creación de usuario por administrador.
- Apertura de disponibilidad multifecha por encargado (rango de hora y días seleccionados).
- Bloqueo de mesa por administrador.

## 2. Corrección de errores

Durante las pruebas se detectaron y corrigieron los siguientes errores:

| Error detectado | Corrección aplicada |
|---|---|
| Navbar roto: links apilados verticalmente | Navbar con flexbox: logo a la izquierda, enlaces centrados, sesión a la derecha |
| Fondo hero roto: imagen `hero-comida` inexistente | Fondo cambiado a `plato.png` con opacidad suave |
| Verde de marca muy oscuro | Verde aclarado: primario `#2E5F4E`, secundario `#4A8C6F` |
| Roles reducidos/inconsistentes | Estandarizados a 3 roles: cliente, encargado, administrador |
| No había botón de registro en el navbar | Registro movido dentro del flujo de login (por solicitud del diseño) |
| `id_mesa` era NOT NULL en pedidos | `id_mesa` ahora es nullable para permitir pedidos sin mesa |
| No existía historial de movimientos | Se agregó registro de historial en cada acción relevante |
| No existían reportes administrativos | Se implementaron reportes de ventas, productos, reservas y dias |

## 3. Pruebas de historial

Se verificó que se registren movimientos de historial en las siguientes acciones:

| Acción | Movimiento esperado |
|---|---|
| Crear reserva | Registro de nueva reserva con fecha, mesa y cliente |
| Cancelar reserva | Registro de cancelación con motivo y usuario |
| Cambiar estado de reserva | Registro de transición de estado (ej. pendiente → confirmada) |
| Crear pedido | Registro de nuevo pedido con platos, mesa y total |
| Cambiar estado de pedido | Registro de transición de estado (ej. pendiente → enviado) |

Cada movimiento queda asociado al usuario que lo ejecutó y a la fecha/hora del suceso.

## 4. Pruebas de reportes

Se verificó que el administrador pueda consultar los siguientes reportes desde el panel:

- Ventas por periodo: filtro por rango de fechas con totales de venta.
- Productos más vendidos: ranking de platos por cantidad vendida.
- Días con más reservas: conteo de reservas agrupado por fecha.
- Días con más ventas: conteo y monto de ventas agrupado por fecha.
- Historial filtrado: búsqueda de movimientos por usuario, acción o rango de fechas.

## 5. Pruebas de normalización

Se verificó la creación de pedidos sin depender de una mesa física:

- Pedido para llevar: se crea un pedido sin asignar `id_mesa` (mesa nula).
- Pedido a domicilio: se crea un pedido sin mesa y con dirección de entrega registrada.

## 6. Cómo ejecutar el sistema

### Requisitos

- XAMPP o Docker.
- PHP (versión compatible con el proyecto).
- MariaDB / MySQL.

### Pasos

1. Clonar o copiar el proyecto en el servidor web (carpeta `public/` como raíz pública).
2. Crear el archivo `.env` con las credenciales de conexión a la base de datos.
3. Aplicar las migraciones en orden: `001`, `002`, `003`, `004`.
4. Acceder al sistema por la ruta `public/`.

## 4. Usuario de prueba

- Correo: `admin@restaurante.local`
- Contraseña: `admin123`
- Rol: administrador

## 5. Conclusión

El sistema se encuentra completamente funcional con los 3 roles definidos (cliente, encargado, administrador). Todas las funcionalidades del problema han sido resueltas: autenticación, carta, reservas con disponibilidad por fecha, pedidos con control de stock, cambio de estados, gestión de usuarios, bloqueo de mesas y disponibilidad multifecha para encargado. El panel despachador redirige correctamente según el rol de cada usuario.

---

**Archivo creado:** `documentacion_integrador/05_sistema_funcional_pruebas.md`
