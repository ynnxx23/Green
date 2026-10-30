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

    %% Tablas de soporte
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
```

## Diccionario de datos resumido

| Tabla | Descripción | PK | FK principales |
|-------|-------------|----|----------------|
| roles | 3 roles del sistema | id_rol | — |
| usuarios | Usuarios autenticados | id_usuario | id_rol → roles |
| areas | Salones del restaurante | id_area | — |
| mesas | Mesas físicas | id_mesa | id_area → areas |
| productos | Catálogo vendible | id_producto | — |
| stock_producto | Inventario y límite de venta | id_stock | id_producto → productos |
| pedidos | Pedidos (normalizado) | id_pedido | id_mesa→mesas, id_mesero→usuarios, id_usuario→usuarios |
| detalle_pedidos | Líneas de pedido | id_detalle | id_pedido→pedidos, id_producto→productos |
| reservas | Reservas de mesas | id_reserva | id_mesa→mesas, id_usuario→usuarios |
| disponibilidad_mesa | Disponibilidad para reservas | id_disponibilidad | creado_por→usuarios |
| horarios_funcionamiento | Apertura/cierre por área y día | id_horario | id_area → areas |
| bloques_mesa | Bloqueos de mesas | id_bloque | id_mesa→mesas, creado_por→usuarios |
| menu_semanal | Menú semanal publicado | id_menu_semanal | creado_por→usuarios |
| menu_semanal_items | Platos del menú semanal | id_item | id_menu_semanal→menu_semanal, id_producto→productos |
| historial_movimientos | Auditoría del sistema | id_historial | id_usuario→usuarios |
