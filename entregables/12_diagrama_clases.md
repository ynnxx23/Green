# Diagrama de Clases — Sistema de Gestión de Restaurante "Green en la Casa Blanca"

*Generado con Mermaid (sintaxis .md). Equivalente al entregable "Diagrama_Clases_Panol.drawio" de referencia, adaptado al proyecto del restaurante.*

```mermaid
classDiagram
    %% ============================================================
    %% CLASES CORE
    %% ============================================================
    class Rol {
        <<enumeration>>
        int id_rol PK
        string nombre UK
        string descripcion
        datetime fecha_creacion
    }
    class Usuario {
        int id_usuario PK
        string nombre
        string email UK
        string contrasena_hash
        int id_rol FK
        bool activo
        int id_sucursal FK NULL
        string avatar NULL
        string telefono NULL
        date fecha_nacimiento NULL
        datetime fecha_creacion
        datetime fecha_actualizacion
        +login(email, password)
        +logout()
        +verPerfil()
        +editarPerfil()
    }
    class Area {
        int id_area PK
        string nombre
        string descripcion
        bool activo
        datetime fecha_creacion
    }
    class Mesa {
        int id_mesa PK
        int id_area FK
        string numero_mesa UK(area)
        int capacidad
        bool activo
        datetime fecha_creacion
    }
    class Producto {
        int id_producto PK
        string nombre
        string categoria
        decimal precio
        bool activo
        datetime fecha_creacion
    }
    class StockProducto {
        int id_stock PK
        int id_producto FK UK
        int stock_actual
        int limite_venta
        bool activo
        datetime fecha_actualizacion
    }
    class Pedido {
        int id_pedido PK
        int id_mesa FK NULL
        int id_mesero FK NULL
        int id_usuario FK NULL
        int id_zona_delivery FK NULL
        int id_repartidor FK NULL
        int id_sucursal FK NULL
        datetime fecha_pedido
        string estado
        string estado_entrega NULL
        decimal total
        string tipo_pedido
        decimal seña
        string estado_seña
        string direccion_domicilio NULL
        decimal costo_envio NULL
        string observaciones NULL
        datetime fecha_creacion
        datetime fecha_actualizacion
    }
    class DetallePedido {
        int id_detalle PK
        int id_pedido FK
        int id_producto FK
        int cantidad
        decimal precio_unitario
    }
    class Reserva {
        int id_reserva PK
        int id_mesa FK
        int id_usuario FK
        int id_sucursal FK NULL
        date fecha_reserva
        time hora_inicio
        time hora_fin
        string estado
        string observaciones NULL
        datetime fecha_creacion
        datetime fecha_actualizacion
    }
    class DisponibilidadMesa {
        int id_disponibilidad PK
        date fecha UK(hora_inicio)
        time hora_inicio
        time hora_fin
        int mesas_disponibles
        bool activo
        int creado_por FK NULL
        datetime fecha_creacion
    }
    class HorarioFuncionamiento {
        int id_horario PK
        int id_area FK
        tinyint dia_semana
        time hora_apertura
        time hora_cierre
        bool activo
        datetime fecha_creacion
    }
    class BloqueMesa {
        int id_bloque PK
        int id_mesa FK
        datetime fecha_inicio
        datetime fecha_fin
        string motivo NULL
        int creado_por FK NULL
        datetime fecha_creacion
    }
    class MenuSemanal {
        int id_menu_semanal PK
        date semana_inicio UK
        date semana_fin
        bool activo
        int creado_por FK NULL
        datetime fecha_creacion
    }
    class MenuSemanalItem {
        int id_item PK
        int id_menu_semanal FK
        int id_producto FK
        bool disponible
        decimal precio_semanal NULL
    }
    class HistorialMovimiento {
        int id_historial PK
        string tabla_afectada
        int id_registro NULL
        string accion
        json detalles NULL
        int id_usuario FK NULL
        datetime fecha_movimiento
        string direccion_ip NULL
    }

    %% ============================================================
    %% CLASES EXTENDIDAS (RF-68 a RF-105 / migraciones 008-013)
    %% ============================================================
    class ConfiguracionSistema {
        int id_config PK
        string clave UK
        text valor
        string descripcion
        string tipo
        datetime fecha_actualizacion
    }
    class ContadorPedidosSinReserva {
        int id_contador PK
        string periodo UK
        date fecha_inicio
        date fecha_fin
        int cantidad
        int limite_maximo
        bool bloqueado
        datetime fecha_actualizacion
    }
    class CuponDescuento {
        int id_cupon PK
        string codigo UK
        string tipo_descuento
        decimal valor_descuento
        decimal pedido_minimo
        date fecha_inicio
        date fecha_fin
        int usos_maximos_totales
        int usos_maximos_por_cliente
        int usos_actuales
        bool activo
        int creado_por FK
        datetime fecha_creacion
    }
    class UsoCuponCliente {
        int id_uso PK
        int id_cupon FK
        int id_usuario FK
        int id_pedido FK NULL
        datetime fecha_uso
    }
    class CalificacionPlato {
        int id_calificacion PK
        int id_usuario FK
        int id_producto FK
        int id_pedido FK
        tinyint puntuacion
        text comentario NULL
        datetime fecha_creacion
    }
    class PreferenciaCliente {
        int id_preferencia PK
        int id_usuario FK UK
        bool vegetariano
        bool vegano
        bool sin_gluten
        bool sin_lactosa
        text alergenos NULL
        text platos_favoritos NULL
        text observaciones NULL
        datetime fecha_actualizacion
    }
    class ReservaRecurrente {
        int id_reserva_recurrente PK
        int id_usuario FK
        int id_mesa FK NULL
        tinyint dia_semana
        time hora_inicio
        time hora_fin
        int cantidad_personas
        date fecha_inicio
        date fecha_fin NULL
        bool activa
        datetime fecha_creacion
    }
    class PuntosFidelidad {
        int id_puntos PK
        int id_usuario FK UK
        int puntos_actuales
        int puntos_totales_ganados
        int puntos_totales_canjeados
        datetime fecha_actualizacion
    }
    class ZonaDelivery {
        int id_zona_delivery PK
        string nombre
        text descripcion NULL
        decimal costo_envio
        decimal radio_km NULL
        int tiempo_estimado_min
        bool activa
        datetime fecha_creacion
    }
    class Repartidor {
        int id_repartidor PK
        string nombre
        string telefono NULL
        string vehiculo NULL
        string placa NULL
        int id_zona_delivery FK NULL
        string estado
        datetime fecha_creacion
    }
    class TokenRecuperacion {
        int id_token PK
        int id_usuario FK
        string token UK
        datetime expira_en
        bool usado
        datetime fecha_creacion
    }
    class PlatoFavorito {
        int id_favorito PK
        int id_usuario FK
        int id_producto FK
        datetime fecha_agregado
    }
    class Notificacion {
        int id_notificacion PK
        int id_usuario FK
        string tipo
        string canal
        string asunto
        text mensaje NULL
        string estado
        datetime fecha_creacion
        datetime fecha_envio NULL
        datetime fecha_lectura NULL
    }
    class CierreCaja {
        int id_cierre PK
        date fecha UK
        int apertura_por FK NULL
        int cierre_por FK NULL
        decimal monto_apertura
        decimal monto_cierre
        decimal ventas_totales
        int pedidos_count
        decimal efectivo
        decimal tarjeta
        decimal transferencia
        decimal diferencia
        string estado
        datetime fecha_apertura NULL
        datetime fecha_cierre NULL
        text observaciones NULL
    }
    class Sucursal {
        int id_sucursal PK
        string nombre
        string direccion NULL
        string telefono NULL
        string email NULL
        bool activa
        datetime fecha_creacion
    }
    class RespaldoBD {
        int id_respaldo PK
        string nombre_archivo
        string tipo
        int tamano_bytes NULL
        datetime fecha_creacion
        int creado_por FK NULL
        string estado
    }
    class Permiso {
        int id_permiso PK
        string nombre
        string codigo UK
        string modulo
        string descripcion NULL
    }
    class RolPermiso {
        int id_rol FK
        int id_permiso FK
    }

    %% ============================================================
    %% RELACIONES CORE
    %% ============================================================
    Rol ||--o{ Usuario : posee
    Usuario ||--|| Reserva : realiza
    Usuario ||--|| Pedido : realiza
    Usuario ||--o{ DisponibilidadMesa : crea
    Usuario ||--o{ BloqueMesa : crea
    Usuario ||--o{ MenuSemanal : publica
    Usuario ||--o{ HistorialMovimiento : ejecuta

    Area ||--|| Mesa : contiene
    Mesa ||--o{ Reserva : soporta
    Mesa ||--o{ Pedido : aloja_opcional
    Mesa ||--o{ BloqueMesa : sufre

    Producto ||--|| StockProducto : tiene
    Producto ||--o{ DetallePedido : incluido_en
    Producto ||--o{ MenuSemanalItem : forma_parte_de

    Pedido ||--|| DetallePedido : contiene
    Reserva ||--|| Mesa : usa

    MenuSemanal ||--|| MenuSemanalItem : contiene

    %% ============================================================
    %% RELACIONES EXTENDIDAS
    %% ============================================================
    Usuario ||--o{ CuponDescuento : crea
    Usuario ||--o{ UsoCuponCliente : usa
    Usuario ||--o{ CalificacionPlato : califica
    Usuario ||--|| PreferenciaCliente : tiene
    Usuario ||--o{ ReservaRecurrente : programa
    Usuario ||--|| PuntosFidelidad : acumula
    Usuario ||--o{ Repartidor : gestiona
    Usuario ||--o{ ZonaDelivery : gestiona
    Usuario ||--o{ ConfiguracionSistema : configura
    Usuario ||--o{ TokenRecuperacion : solicita
    Usuario ||--o{ PlatoFavorito : marca
    Usuario ||--o{ Notificacion : recibe
    Usuario ||--o{ CierreCaja : abre_cierra
    Usuario ||--o{ RespaldoBD : genera

    Producto ||--o{ CalificacionPlato : recibe
    Producto ||--o{ PlatoFavorito : favorito_de

    Pedido ||--o{ UsoCuponCliente : aplica
    Pedido ||--o| Repartidor : asigna
    Pedido ||--o| ZonaDelivery : zona
    Pedido ||--o| Sucursal : pertenece

    Reserva ||--o| Sucursal : pertenece

    ZonaDelivery ||--o{ Repartidor : asigna

    Rol ||--o{ RolPermiso : tiene
    Permiso ||--o{ RolPermiso : asignado_a

    Sucursal ||--o{ Usuario : tiene
    Sucursal ||--o{ Pedido : tiene
    Sucursal ||--o{ Reserva : tiene
```

## Descripción de las clases

| Clase | Responsabilidad |
|---|---|
| `Rol` | Define los roles del sistema (cliente, encargado, administrador). |
| `Usuario` | Datos de acceso y autenticación de cada usuario con su rol asignado. |
| `Area` | Salones o áreas físicas del restaurante. |
| `Mesa` | Mesas físicas pertenecientes a un área. |
| `Producto` | Catálogo de productos vendibles (platos y bebidas). |
| `StockProducto` | Inventario y límite de venta por producto. |
| `Pedido` | Pedido de productos con estado, seña, tipo de entrega y totales. |
| `DetallePedido` | Líneas de detalle (ítems) de cada pedido. |
| `Reserva` | Reserva de mesa realizada por un cliente. |
| `DisponibilidadMesa` | Disponibilidad de mesas abierta por el encargado para reservas. |
| `HorarioFuncionamiento` | Horarios de apertura y cierre por área y día. |
| `BloqueMesa` | Bloqueo de mesas por mantenimiento o eventos. |
| `MenuSemanal` | Menú semanal publicado por el encargado. |
| `MenuSemanalItem` | Platos disponibles y precio de cada semana. |
| `HistorialMovimiento` | Auditoría de movimientos del sistema (quién, qué, cuándo). |

## Clases extendidas (funcionalidades RF-68 a RF-105)

Soportan cupones, calificaciones, preferencias, fidelidad, delivery, repartidores, recuperación de contraseña, cierre de caja, sucursales, respaldos y permisos granulares por rol, tal como se definen en las migraciones 008 a 013 y en los casos de uso extendidos (CU20–CU33).

## Trazabilidad

- Las clases `Rol`, `Usuario`, `Area`, `Mesa`, `Producto`, `StockProducto`, `Pedido`, `DetallePedido`, `Reserva`, `DisponibilidadMesa`, `HorarioFuncionamiento`, `BloqueMesa`, `MenuSemanal`, `MenuSemanalItem` y `HistorialMovimiento` corresponden a las entidades del DER (`10_diagrama_der.md`).
- Las clases extendidas se derivan de los casos de uso extendidos CU20–CU33 (`09_diagrama_caso_uso.md`) y de los requerimientos RF-68 a RF-105.

--- 

**Archivo creado:** `entregables/12_diagrama_clases.md` como equivalente `.md` (Mermaid) del entregable de referencia `Diagrama_Clases_Panol.drawio`.
