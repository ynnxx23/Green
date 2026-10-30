# Diagrama de Casos de Uso — Sistema de Gestión de Restaurante "Green en la Casa Blanca"

*Generado con Mermaid (sintaxis .md). Equivalente al entregable "CASOSDEUSOPAÑOLL.drawio" de referencia, adaptado al proyecto del restaurante.*

```mermaid
useCaseDiagram
    %% Actores del sistema (3 roles)
    actor "Cliente" as Cliente
    actor "Encargado" as Encargado
    actor "Administrador" as Admin

    %% Casos de uso - Cliente
    usecase "Iniciar sesión" as CU1
    usecase "Registrarse" as CU2
    usecase "Ver carta de productos" as CU3
    usecase "Ver menú semanal" as CU4
    usecase "Hacer reserva (1 fecha)" as CU5
    usecase "Ver mis reservas" as CU6
    usecase "Cancelar reserva propia" as CU7
    usecase "Hacer pedido (con_reserva / para_llevar / domicilio)" as CU8
    usecase "Ver mis pedidos y estado de seña" as CU9

    %% Casos de uso - Encargado
    usecase "Gestionar stock y límite de venta" as CU10
    usecase "Abrir disponibilidad de mesas (multifecha)" as CU11
    usecase "Gestionar menú semanal" as CU12
    usecase "Ver todos los pedidos y cambiar estado" as CU13
    usecase "Gestionar estado de seña (pendiente/pagada/devuelta)" as CU14
    usecase "Ver todas las reservas y cambiar estado" as CU15
    usecase "Bloquear mesa" as CU16

    %% Casos de uso - Administrador
    usecase "Gestionar usuarios (CRUD)" as CU17
    usecase "Ver reportes (ventas, productos más vendidos, reservas/día, ventas/día)" as CU18
    usecase "Ver historial de movimientos" as CU19

    %% Relaciones actor - caso de uso
    Cliente --> CU1 : autenticar
    Cliente --> CU2 : registrar
    Cliente --> CU3 : consultar
    Cliente --> CU4 : consultar
    Cliente --> CU5 : crear
    Cliente --> CU6 : consultar
    Cliente --> CU7 : cancelar
    Cliente --> CU8 : crear
    Cliente --> CU9 : consultar

    Encargado --> CU1 : autenticar
    Encargado --> CU10 : gestionar
    Encargado --> CU11 : crear
    Encargado --> CU12 : publicar
    Encargado --> CU13 : cambiar estado
    Encargado --> CU14 : gestionar
    Encargado --> CU15 : cambiar estado
    Encargado --> CU16 : crear

    Admin --> CU1 : autenticar
    Admin --> CU17 : gestionar
    Admin --> CU18 : consultar
    Admin --> CU19 : consultar

    %% Relaciones <<include>> / <<extend>>
    CU5 ..> CU1 : <<include>>
    CU8 ..> CU1 : <<include>>
    CU7 ..> CU6 : <<include>>
    CU13 ..> CU15 : <<include>>

    %% Notas de alcance
    note right of CU5 : Solo fechas con disponibilidad abierta (días verdes). Validar solapamiento y mesas disponibles.
    note right of CU8 : Seña 50% en pedidos anticipados. Validar stock y límite de venta. Domicilio requiere dirección.
    note right of CU11 : Múltiples fechas y rango de hora. Días verdes para el cliente.
    note right of CU18 : Exclusivo rol administrador.
    note right of CU19 : Auditoría completa (historial_movimientos).
```

## Descripción de los actores

| Actor         | Descripción                                                                                 |
| ------------- | ------------------------------------------------------------------------------------------- |
| Cliente       | Usuario autenticado que verá la carta, hará reservas (1 fecha) y pedidos.                   |
| Encargado     | Gestiona stock, disponibilidad multifecha, menú semanal, estados de pedido/reserva y señas. |
| Administrador | Gestión total: usuarios, reportes y historial de movimientos.                               |

## Casos de uso principales

| ID   | Caso de uso            | Actor principal           | Descripción breve                                                  |
| ---- | ---------------------- | ------------------------- | ------------------------------------------------------------------ |
| CU1  | Iniciar sesión         | Cliente, Encargado, Admin | Autenticación con email + contraseña (bcrypt) y sesión HttpOnly.   |
| CU2  | Registrarse            | Cliente, Encargado        | Registro público como cliente o encargado.                         |
| CU3  | Ver carta              | Cliente                   | Catálogo de productos activos con filtros por categoría.           |
| CU4  | Ver menú semanal       | Cliente                   | Platos publicados por el encargado para la semana vigente.         |
| CU5  | Hacer reserva          | Cliente                   | Selecciona 1 fecha disponible, mesa y hora; valida solapamiento.   |
| CU6  | Ver mis reservas       | Cliente                   | Listado de reservas propias con estado.                            |
| CU7  | Cancelar reserva       | Cliente                   | Cancela reservas propias pendientes o confirmadas.                 |
| CU8  | Hacer pedido           | Cliente                   | Carrito con seña 50%, tipo de entrega y dirección si es domicilio. |
| CU9  | Ver mis pedidos        | Cliente                   | Pedidos propios con estado y seña.                                 |
| CU10 | Gestionar stock        | Encargado                 | Stock actual y límite de venta por producto.                       |
| CU11 | Disponibilidad mesas   | Encargado                 | Abre disponibilidad por múltiples fechas con rango de hora.        |
| CU12 | Menú semanal           | Encargado                 | Publica platos disponibles cada semana con precio semanal.         |
| CU13 | Cambiar estado pedido  | Encargado, Admin          | pendiente → en_preparación → listo → entregado / cancelado.        |
| CU14 | Estado de seña         | Encargado                 | pendiente → pagada → devuelta.                                     |
| CU15 | Cambiar estado reserva | Encargado, Admin          | pendiente → confirmada / cancelada / vencida / completada.         |
| CU16 | Bloquear mesa          | Encargado                 | Bloquea una mesa en un rango de fechas con motivo.                 |
| CU17 | Gestionar usuarios     | Admin                     | Crear, activar, desactivar, eliminar usuarios y roles.             |
| CU18 | Reportes               | Admin                     | Ventas, productos más vendidos, reservas/día, ventas/día.          |
| CU19 | Historial              | Admin                     | Auditoría de movimientos con filtros.                              |
