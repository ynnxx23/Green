# Diagrama Entidad-Relación (DER) — Sistema de Gestión de Restaurante "Green en la Casa Blanca"

*Generado con Mermaid (sintaxis .md). Equivalente al entregable "DER_Panol_Barker.drawio" de referencia, adaptado al proyecto del restaurante. Notación estilo crow's foot.*

```mermaid
erDiagram
    %% Entidades principales
    ROLES ||--o{ USUARIOS : posee
    USUARIOS ||--|| RESERVAS : realiza
    USUARIOS ||--|| PEDIDOS : realiza
    USUARIOS ||--o{ DISPONIBILIDAD_MESA : crea
    USUARIOS ||--o{ BLOQUES_MESA : crea
    USUARIOS ||--o{ MENU_SEMANAL : publica
    USUARIOS ||--o{ HISTORIAL_MOVIMIENTOS : ejecuta

    AREAS ||--|| MESAS : contiene
    MESAS ||--o{ RESERVAS : soporta
    MESAS ||--o{ PEDIDOS : aloja (opcional)
    MESAS ||--o{ BLOQUES_MESA : sufre

    PRODUCTOS ||--|| STOCK_PRODUCTO : tiene
    PRODUCTOS ||--o{ DETALLE_PEDIDOS : incluido en
    PRODUCTOS ||--o{ MENU_SEMANAL_ITEMS : forma parte de

    PEDIDOS ||--|| DETALLE_PEDIDOS : contiene
    RESERVAS ||--|| MESAS : usa

    MENU_SEMANAL ||--|| MENU_SEMANAL_ITEMS : contiene

    %% Atributos ROLES
    ROLES {
        int id_rol PK
        string nombre UK
        string descripcion
        datetime fecha_creacion
    }

    %% Atributos USUARIOS
    USUARIOS {
        int id_usuario PK
        string nombre
        string email UK
        string contrasena_hash
        int id_rol FK
        bool activo
        datetime fecha_creacion
        datetime fecha_actualizacion
    }

    %% Atributos AREAS
    AREAS {
        int id_area PK
        string nombre
        string descripcion
        bool activo
        datetime fecha_creacion
    }

    %% Atributos MESAS
    MESAS {
        int id_mesa PK
        int id_area FK
        string numero_mesa UK(area)
        int capacidad
        bool activo
        datetime fecha_creacion
    }

    %% Atributos PRODUCTOS
    PRODUCTOS {
        int id_producto PK
        string nombre
        string categoria
        decimal precio
        bool activo
        datetime fecha_creacion
    }

    %% Atributos STOCK_PRODUCTO
    STOCK_PRODUCTO {
        int id_stock PK
        int id_producto FK UK
        int stock_actual
        int limite_venta
        bool activo
        datetime fecha_actualizacion
    }

    %% Atributos PEDIDOS (normalizado 006)
    PEDIDOS {
        int id_pedido PK
        int id_mesa FK NULL
        int id_mesero FK NULL
        int id_usuario FK NULL
        datetime fecha_pedido
        string estado
        decimal total
        string tipo_pedido
        decimal seña
        string estado_seña
        string direccion_domicilio NULL
        datetime fecha_pedido_hora NULL
        datetime fecha_creacion
        datetime fecha_actualizacion
    }

    %% Atributos DETALLE_PEDIDOS
    DETALLE_PEDIDOS {
        int id_detalle PK
        int id_pedido FK
        int id_producto FK
        int cantidad
        decimal precio_unitario
    }

    %% Atributos RESERVAS
    RESERVAS {
        int id_reserva PK
        int id_mesa FK
        int id_usuario FK
        date fecha_reserva
        time hora_inicio
        time hora_fin
        string estado
        string observaciones NULL
        datetime fecha_creacion
        datetime fecha_actualizacion
    }

    %% Atributos DISPONIBILIDAD_MESA
    DISPONIBILIDAD_MESA {
        int id_disponibilidad PK
        date fecha UK(hora_inicio)
        time hora_inicio
        time hora_fin
        int mesas_disponibles
        bool activo
        int creado_por FK NULL
        datetime fecha_creacion
    }

    %% Atributos HORARIOS_FUNCIONAMIENTO
    HORARIOS_FUNCIONAMIENTO {
        int id_horario PK
        int id_area FK
        tinyint dia_semana
        time hora_apertura
        time hora_cierre
        bool activo
        datetime fecha_creacion
    }

    %% Atributos BLOQUES_MESA
    BLOQUES_MESA {
        int id_bloque PK
        int id_mesa FK
        datetime fecha_inicio
        datetime fecha_fin
        string motivo NULL
        int creado_por FK NULL
        datetime fecha_creacion
    }

    %% Atributos MENU_SEMANAL
    MENU_SEMANAL {
        int id_menu_semanal PK
        date semana_inicio UK
        date semana_fin
        bool activo
        int creado_por FK NULL
        datetime fecha_creacion
    }

    %% Atributos MENU_SEMANAL_ITEMS
    MENU_SEMANAL_ITEMS {
        int id_item PK
        int id_menu_semanal FK
        int id_producto FK
        bool disponible
        decimal precio_semanal NULL
    }

    %% Atributos HISTORIAL_MOVIMIENTOS
    HISTORIAL_MOVIMIENTOS {
        int id_historial PK
        string tabla_afectada
        int id_registro NULL
        string accion
        json detalles NULL
        int id_usuario FK NULL
        datetime fecha_movimiento
        string direccion_ip NULL
    }

    %% Tablas de soporte original
    INTENTOS_LOGIN {
        int id_intento PK
        string email
        bool exitoso
        datetime fecha_intento
        string direccion_ip NULL
    }

    RATE_LIMIT_STATE {
        string clave PK
        decimal tokens
        datetime ultima_actualizacion
    }

    MIGRACIONES_APLICADAS {
        string version PK
        datetime fecha_aplicacion
    }

    %% ============================================================
    %% NUEVAS ENTIDADES (Migraciones 009–013) - RFs extendidos
    %% ============================================================

    %% CONFIGURACION_SISTEMA (009)
    CONFIGURACION_SISTEMA {
        int id_config PK
        string clave UK
        text valor
        string descripcion
        string tipo
        datetime fecha_actualizacion
    }

    %% CONTADOR_PEDIDOS_SIN_RESERVA (009)
    CONTADOR_PEDIDOS_SIN_RESERVA {
        int id_contador PK
        string periodo UK
        date fecha_inicio
        date fecha_fin
        int cantidad
        int limite_maximo
        bool bloqueado
        datetime fecha_actualizacion
    }

    %% CUPONES_DESCUENTO (010)
    CUPONES_DESCUENTO {
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

    %% USO_CUPON_CLIENTE (010)
    USO_CUPON_CLIENTE {
        int id_uso PK
        int id_cupon FK
        int id_usuario FK
        int id_pedido FK NULL
        datetime fecha_uso
    }

    %% CALIFICACIONES_PLATOS (011)
    CALIFICACIONES_PLATOS {
        int id_calificacion PK
        int id_usuario FK
        int id_producto FK
        int id_pedido FK
        tinyint puntuacion
        text comentario NULL
        datetime fecha_creacion
    }

    %% PREFERENCIAS_CLIENTE (011)
    PREFERENCIAS_CLIENTE {
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

    %% RESERVAS_RECURRENTES (011)
    RESERVAS_RECURRENTES {
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

    %% PUNTOS_FIDELIDAD (011)
    PUNTOS_FIDELIDAD {
        int id_puntos PK
        int id_usuario FK UK
        int puntos_actuales
        int puntos_totales_ganados
        int puntos_totales_canjeados
        datetime fecha_actualizacion
    }

    %% ZONAS_DELIVERY (012)
    ZONAS_DELIVERY {
        int id_zona_delivery PK
        string nombre
        text descripcion NULL
        decimal costo_envio
        decimal radio_km NULL
        int tiempo_estimado_min
        bool activa
        datetime fecha_creacion
    }

    %% REPARTIDORES (012)
    REPARTIDORES {
        int id_repartidor PK
        string nombre
        string telefono NULL
        string vehiculo NULL
        string placa NULL
        int id_zona_delivery FK NULL
        string estado
        datetime fecha_creacion
    }

    %% TOKENS_RECUPERACION (008)
    TOKENS_RECUPERACION {
        int id_token PK
        int id_usuario FK
        string token UK
        datetime expira_en
        bool usado
        datetime fecha_creacion
    }

    %% Extensiones a PEDIDOS (009, 012, 013)
    PEDIDOS {
        int id_pedido PK
        int id_mesa FK NULL
        int id_mesero FK NULL
        int id_usuario FK NULL
        int id_zona_delivery FK NULL
        int id_repartidor FK NULL
        int id_sucursal FK NULL
        datetime fecha_pedido
        string estado
        string estado_entrega
        decimal total
        string tipo_pedido
        decimal seña
        string estado_seña
        string direccion_domicilio NULL
        decimal costo_envio
        string observaciones NULL
        datetime fecha_creacion
        datetime fecha_actualizacion
    }

    %% Extensiones a RESERVAS (013)
    RESERVAS {
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

    %% Extensiones a USUARIOS (008, 013)
    USUARIOS {
        int id_usuario PK
        string nombre
        string email UK
        string contrasena_hash
        int id_rol FK
        int id_sucursal FK NULL
        bool activo
        string avatar NULL
        string telefono NULL
        date fecha_nacimiento NULL
        datetime fecha_creacion
        datetime fecha_actualizacion
    }

    %% CIERRE_CAJA (013)
    CIERRE_CAJA {
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

    %% PLATOS_FAVORITOS (013)
    PLATOS_FAVORITOS {
        int id_favorito PK
        int id_usuario FK
        int id_producto FK
        datetime fecha_agregado
    }

    %% NOTIFICACIONES (013)
    NOTIFICACIONES {
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

    %% SUCURSALES (013)
    SUCURSALES {
        int id_sucursal PK
        string nombre
        string direccion NULL
        string telefono NULL
        string email NULL
        bool activa
        datetime fecha_creacion
    }

    %% RESPALDOS_BD (013)
    RESPALDOS_BD {
        int id_respaldo PK
        string nombre_archivo
        string tipo
        int tamano_bytes NULL
        datetime fecha_creacion
        int creado_por FK NULL
        string estado
    }

    %% PERMISOS (013)
    PERMISOS {
        int id_permiso PK
        string nombre
        string codigo UK
        string modulo
        string descripcion NULL
    }

    %% ROLES_PERMISOS (013)
    ROLES_PERMISOS {
        int id_rol FK
        int id_permiso FK
    }

    %% ============================================================
    %% RELACIONES NUEVAS
    %% ============================================================
    USUARIOS ||--o{ CUPONES_DESCUENTO : crea
    USUARIOS ||--o{ USO_CUPON_CLIENTE : usa
    USUARIOS ||--o{ CALIFICACIONES_PLATOS : califica
    USUARIOS ||--|| PREFERENCIAS_CLIENTE : tiene
    USUARIOS ||--o{ RESERVAS_RECURRENTES : programa
    USUARIOS ||--|| PUNTOS_FIDELIDAD : acumula
    USUARIOS ||--o{ REPARTIDORES : gestiona
    USUARIOS ||--o{ ZONAS_DELIVERY : gestiona
    USUARIOS ||--o{ CONFIGURACION_SISTEMA : configura
    USUARIOS ||--o{ TOKENS_RECUPERACION : solicita
    USUARIOS ||--o{ PLATOS_FAVORITOS : marca
    USUARIOS ||--o{ NOTIFICACIONES : recibe
    USUARIOS ||--o{ CIERRE_CAJA : abre_cierra
    USUARIOS ||--o{ RESPALDOS_BD : genera

    PRODUCTOS ||--o{ CALIFICACIONES_PLATOS : recibe
    PRODUCTOS ||--o{ PLATOS_FAVORITOS : favorito_de

    PEDIDOS ||--o{ USO_CUPON_CLIENTE : aplica
    PEDIDOS ||--o| REPARTIDORES : asigna
    PEDIDOS ||--o| ZONAS_DELIVERY : zona
    PEDIDOS ||--o| SUCURSALES : pertenece

    RESERVAS ||--o| SUCURSALES : pertenece

    ZONAS_DELIVERY ||--o{ REPARTIDORES : asigna

    ROLES ||--o{ ROLES_PERMISOS : tiene
    PERMISOS ||--o{ ROLES_PERMISOS : asignado_a

    SUCURSALES ||--o{ USUARIOS : tiene
    SUCURSALES ||--o{ PEDIDOS : tiene
    SUCURSALES ||--o{ RESERVAS : tiene
```

## Diccionario de datos resumido

| Tabla                   | Descripción                    | PK                | FK principales                                         |
| ----------------------- | ------------------------------ | ----------------- | ------------------------------------------------------ |
| roles                   | 3 roles del sistema            | id_rol            | —                                                      |
| usuarios                | Usuarios autenticados          | id_usuario        | id_rol → roles                                         |
| areas                   | Salones del restaurante        | id_area           | —                                                      |
| mesas                   | Mesas físicas                  | id_mesa           | id_area → areas                                        |
| productos               | Catálogo vendible              | id_producto       | —                                                      |
| stock_producto          | Inventario y límite de venta   | id_stock          | id_producto → productos                                |
| pedidos                 | Pedidos (normalizado)          | id_pedido         | id_mesa→mesas, id_mesero→usuarios, id_usuario→usuarios |
| detalle_pedidos         | Líneas de pedido               | id_detalle        | id_pedido→pedidos, id_producto→productos               |
| reservas                | Reservas de mesas              | id_reserva        | id_mesa→mesas, id_usuario→usuarios                     |
| disponibilidad_mesa     | Disponibilidad para reservas   | id_disponibilidad | creado_por→usuarios                                    |
| horarios_funcionamiento | Apertura/cierre por área y día | id_horario        | id_area → areas                                        |
| bloques_mesa            | Bloqueos de mesas              | id_bloque         | id_mesa→mesas, creado_por→usuarios                     |
| menu_semanal            | Menú semanal publicado         | id_menu_semanal   | creado_por→usuarios                                    |
| menu_semanal_items      | Platos del menú semanal        | id_item           | id_menu_semanal→menu_semanal, id_producto→productos    |
| historial_movimientos   | Auditoría del sistema          | id_historial      | id_usuario→usuarios                                    |
