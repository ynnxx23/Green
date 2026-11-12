# Diagrama de Casos de Uso — Sistema de Gestión de Restaurante "Green en la Casa Blanca"

*Entregable 09 — Documento de texto equivalente (Mermaid) del diagrama `Diagrama_Caso_Uso_Green.drawio`, siguiendo el modelo de referencia `entregables/referencia/CASOSDEUSOPAÑOLL.drawio`.*

> **Regla de modelado aplicada:** todos los usuarios **deben iniciar sesión antes de operar**
> sobre el sistema. Los casos de uso que exigen operación incluyen `<<include>>` hacia
> **Iniciar sesión**. La interfaz pública permite explorar la carta sin sesión, pero el
> modelo de casos de uso-documental exige la autenticación previa.

```mermaid
useCaseDiagram
    %% Actores del sistema
    actor "Cliente" as Cliente
    actor "Encargado" as Encargado
    actor "Administrador" as Admin

    %% ---- Casos de uso ----
    usecase "Iniciar sesión" as CU1
    usecase "Registrarse" as CU2
    usecase "Cerrar sesión" as CU3
    usecase "Ver carta de productos y stock" as CU4
    usecase "Ver menú semanal" as CU5
    usecase "Reservar mesa (cupo disponible)" as CU6
    usecase "Ver / cancelar mis reservas" as CU7
    usecase "Gestionar carrito de compra" as CU8
    usecase "Realizar pedido (para llevar / domicilio)" as CU9
    usecase "Ver mis pedidos y estado de seña" as CU10
    usecase "Calificar platos" as CU11
    usecase "Consultar y canjear puntos de fidelidad" as CU12
    usecase "Gestionar stock y límite de venta" as CU13
    usecase "Generar cupos de reserva en lote" as CU14
    usecase "Agregar / editar platos de la carta" as CU15
    usecase "Cambiar estado de pedido y de seña" as CU16
    usecase "Cambiar estado de reserva" as CU17
    usecase "Configurar delivery (zonas y repartidores)" as CU18
    usecase "Gestionar usuarios y roles" as CU19
    usecase "Ver reportes y estadísticas" as CU20
    usecase "Consultar historial de movimientos" as CU21
    usecase "Cierre de caja y respaldo de BD" as CU22
    usecase "Gestionar límite de pedidos sin reserva" as CU23
    usecase "Gestionar zonas de delivery" as CU24
    usecase "Gestionar repartidores" as CU25
    usecase "Configuración del restaurante" as CU28
    usecase "Estado de entrega de delivery" as CU30

    %% ---- Relaciones actor / caso ----
    Cliente --> CU1 : autenticar
    Cliente --> CU2 : registrar
    Cliente --> CU3 : cerrar sesión
    Cliente --> CU4 : consultar
    Cliente --> CU5 : consultar
    Cliente --> CU6 : crear
    Cliente --> CU7 : consultar / cancelar
    Cliente --> CU8 : gestionar
    Cliente --> CU9 : crear
    Cliente --> CU10 : consultar
    Cliente --> CU11 : calificar
    Cliente --> CU12 : consultar / canjear
    Encargado --> CU1 : autenticar
    Encargado --> CU3 : cerrar sesión
    Encargado --> CU5 : publicar
    Encargado --> CU13 : gestionar
    Encargado --> CU14 : generar
    Encargado --> CU15 : agregar / editar
    Encargado --> CU16 : cambiar estado
    Encargado --> CU17 : cambiar estado
    Encargado --> CU18 : configurar
    Encargado --> CU23 : gestionar
    Encargado --> CU24 : gestionar
    Encargado --> CU25 : gestionar
    Encargado --> CU28 : configurar
    Encargado --> CU30 : cambiar estado
    Admin --> CU1 : autenticar
    Admin --> CU3 : cerrar sesión
    Admin --> CU13 : gestionar
    Admin --> CU14 : generar
    Admin --> CU15 : agregar / editar
    Admin --> CU16 : cambiar estado
    Admin --> CU17 : cambiar estado
    Admin --> CU18 : configurar
    Admin --> CU19 : gestionar
    Admin --> CU20 : consultar
    Admin --> CU21 : consultar
    Admin --> CU22 : operar
    Admin --> CU23 : gestionar
    Admin --> CU24 : gestionar
    Admin --> CU25 : gestionar
    Admin --> CU28 : configurar

    %% ---- <<include>>: la autenticación es requisito previo ----
    CU3 ..> CU1 : <<include>>
    CU4 ..> CU1 : <<include>>
    CU5 ..> CU1 : <<include>>
    CU6 ..> CU1 : <<include>>
    CU7 ..> CU1 : <<include>>
    CU8 ..> CU1 : <<include>>
    CU9 ..> CU1 : <<include>>
    CU10 ..> CU1 : <<include>>
    CU11 ..> CU1 : <<include>>
    CU12 ..> CU1 : <<include>>
    CU13 ..> CU1 : <<include>>
    CU14 ..> CU1 : <<include>>
    CU15 ..> CU1 : <<include>>
    CU16 ..> CU1 : <<include>>
    CU17 ..> CU1 : <<include>>
    CU18 ..> CU1 : <<include>>
    CU19 ..> CU1 : <<include>>
    CU20 ..> CU1 : <<include>>
    CU21 ..> CU1 : <<include>>
    CU22 ..> CU1 : <<include>>
    CU23 ..> CU1 : <<include>>
    CU24 ..> CU1 : <<include>>
    CU25 ..> CU1 : <<include>>
    CU28 ..> CU1 : <<include>>
    CU30 ..> CU1 : <<include>>
    CU8 ..> CU4 : <<include>>
    CU9 ..> CU8 : <<include>>
    CU16 ..> CU17 : <<extend>>
    CU22 ..> CU16 : <<include>>
    CU23 ..> CU9 : <<include>>
    CU30 ..> CU16 : <<include>>

```

## Diagrama en formato `.drawio`

Archivo editable: **`Diagrama_Caso_Uso_Green.drawio`** (draw.io / diagrams.net).

Símbolos y convenciones UML empleados:

| Elemento | Símbolo draw.io | Significado |
|---|---|---|
| Actor | `shape=umlActor` | Cliente, Encargado, Administrador |
| Caso de uso | `ellipse` | Operación del sistema |
| Asociación | línea gruesa `strokeWidth=7` sin flecha | Actor ↔ caso de uso |
| `<<include>>` | arista ortogonal + etiqueta `<<include>>` | Requisito previo obligatorio |
| `<<extend>>` | arista ortogonal + etiqueta `<<extend>>` | Comportamiento opcional |

## Actores

| Actor | Descripción |
|---|---|
| Cliente | Usuario que reserva mesa, pide comida a domicilio o para llevar, y califica platos. Requiere sesión iniciada. |
| Encargado | Opera el día a día: stock, cupos de reserva, platos de la carta, estados de pedido y reserva, configuración de delivery. Requiere sesión iniciada. |
| Administrador | Gestión total: usuarios y roles, reportes, historial, cierre de caja y respaldos. Además de todo lo del encargado. Requiere sesión iniciada. |

## Casos de uso

### Autenticación

| ID | Caso de uso | Actor principal | Descripción |
|---|---|---|---|
| CU1 | Iniciar sesión | Cliente, Encargado, Admin | Autenticación con correo y contraseña (bcrypt). Crea la sesión `restaurante_session` con cookie HttpOnly. Requisito previo de todos los demás casos. |
| CU2 | Registrarse | Cliente, Encargado | Registro público como cliente (rol 1) o encargado (rol 2). Validación: mínimo 8 caracteres, una mayúscula y un número. |
| CU3 | Cerrar sesión | Cliente, Encargado, Admin | Invalida la sesión y redirige al inicio. |

### Cliente

| ID | Caso de uso | Actor principal | Descripción |
|---|---|---|---|
| CU4 | Ver carta de productos y stock | Cliente | Catálogo de productos activos con precio, imagen, stock disponible y filtro por categoría. |
| CU5 | Ver menú semanal | Cliente | Platos publicados por el encargado para la semana vigente. |
| CU6 | Reservar mesa | Cliente | Selecciona un día con cupo y una hora disponible. El sistema asigna automáticamente una mesa libre con capacidad suficiente. La reserva queda **confirmada** de inmediato. |
| CU7 | Ver / cancelar mis reservas | Cliente | Listado de reservas propias con estado y cancelación de las pendientes o confirmadas. |
| CU8 | Gestionar carrito de compra | Cliente | Agrega platos con cantidad desde la modal, filtra por categoría y ajusta cantidades. Persiste en `localStorage`. |
| CU9 | Realizar pedido | Cliente | Confirma el carrito como pedido para llevar o a domicilio. Seña del 50 % automática, saldo pendiente y validación de stock y límite de venta. |
| CU10 | Ver mis pedidos y estado de seña | Cliente | Pedidos propios con tipo, total, seña, saldo y estado de entrega. |
| CU11 | Calificar platos | Cliente | Puntuación de 1 a 5 estrellas y comentario. |
| CU12 | Consultar y canjear puntos de fidelidad | Cliente | Puntos acumulados por consumo y canje por descuentos. |

### Encargado y Administrador

| ID | Caso de uso | Actor principal | Descripción |
|---|---|---|---|
| CU13 | Gestionar stock y límite de venta | Encargado, Admin | Edita `stock_actual` y `limite_venta` por producto. |
| CU14 | Generar cupos de reserva en lote | Encargado, Admin | **Nuevo.** Indica rango de fechas, días de la semana, hora de apertura/cierre y mesas por cupo; el sistema crea todos los cupos de una vez. |
| CU15 | Agregar / editar platos de la carta | Encargado, Admin | **Nuevo.** Alta, edición, activación y desactivación de productos con precio, categoría e imagen. |
| CU16 | Cambiar estado de pedido y de seña | Encargado, Admin | `pendiente → en_preparación → listo → entregado / cancelado`; seña `pendiente → pagada → devuelta`. |
| CU17 | Cambiar estado de reserva | Encargado, Admin | `pendiente → confirmada / cancelada / vencida / completada`. |
| CU18 | Configurar delivery | Encargado, Admin | Zonas con costo y tiempo estimado, y repartidores con su usuario asociado. |
| CU19 | Gestionar usuarios y roles | Admin | Alta, activación, desactivación y eliminación de usuarios; asignación de rol. |
| CU20 | Ver reportes y estadísticas | Admin | Ventas, productos más vendidos, reservas por día, ventas por día y reportes avanzados. |
| CU21 | Consultar historial de movimientos | Admin | Auditoría completa en `historial_movimientos`. |
| CU22 | Cierre de caja y respaldo de BD | Admin | Apertura y cierre por día con arqueo por medio de pago; historial de respaldos. |

## Casos de uso extendidos (RF-68 a RF-105)

| ID | Caso de uso | Actor principal | Descripción |
|---|---|---|---|
| CU23 | Gestionar límite de pedidos sin reserva | Encargado, Admin | Límite máximo por modalidad y día, con contador automático. |
| CU24 | Gestionar zonas de delivery | Encargado, Admin | Alta y edición de zonas con costo de envío y cobertura. |
| CU25 | Gestionar repartidores | Encargado, Admin | Alta/baja y asignación de zona. |
| CU28 | Configuración del restaurante | Encargado, Admin | Nombre, dirección, contacto, horarios, moneda, IVA y porcentaje de seña. |
| CU30 | Estado de entrega de delivery | Encargado | `pendiente → en_camino → entregado / fallido`. |

## Reglas de negocio asociadas

| Regla | Descripción |
|---|---|
| RB-01 | **Todo usuario debe iniciar sesión antes de operar.** Los casos de uso que implican operación incluyen `<<include>>` hacia *Iniciar sesión*. |
| RB-02 | La reserva de mesa se **confirma directamente**: no existe un estado intermedio de "solicitud pendiente de aprobación". |
| RB-03 | El cliente puede reservar **sin sesión**: el sistema crea o reutiliza el usuario a partir del correo y el teléfono. |
| RB-04 | La mesa se asigna **automáticamente** por capacidad y ausencia de solapamiento. |
| RB-05 | El selector de fecha solo habilita días con cupo abierto; el selector de hora solo ofrece las franjas de ese día. |
| RB-06 | Los cupos se generan en **lote**: rango de fechas + días de la semana + franja horaria. |
| RB-07 | La seña por defecto es del **50 %** del total; el saldo queda pendiente. |
| RB-08 | No se puede pedir más stock del disponible ni superar el límite de venta por producto. |
| RB-09 | El carrito persiste en `localStorage` y muestra el stock disponible de cada plato. |
| RB-10 | Los pedidos a domicilio exigen dirección de entrega y una zona de cobertura. |

## Trazabilidad con la implementación

| Caso | Endpoint principal | Archivo |
|---|---|---|
| CU1 | `POST /api/auth/login` | `src/Controller/AuthController.php` |
| CU6 | `POST /api/reservas` | `src/Controller/ReservaController.php` |
| CU8 | `localStorage` + `assets/js/carrito.js` | `public/assets/js/carrito.js` |
| CU9 | `POST /api/pedidos` | `src/Controller/PedidoController.php` |
| CU13 | `POST /api/admin/stock` | `public/assets/js/gestion-panel.js` |
| CU14 | `POST /api/admin/disponibilidad/generar` | `public/assets/js/gestion-panel.js` |
| CU15 | `POST /api/productos` · `PUT /api/productos/{id}` | `public/assets/js/gestion-panel.js` |
