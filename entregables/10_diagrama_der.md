# Diagrama Entidad-Relación (DER) — Sistema de Gestión de Restaurante "Green en la Casa Blanca"

*Entregable 10 — Documento de texto equivalente (Mermaid) del diagrama `Diagrama_DER_Green.drawio`, siguiendo el modelo de referencia `entregables/referencia/DER_Panol_Barker.drawio` (notación Barker).*

> **Esquema simplificado (migración 025).** La base de datos pasó de 36 a **23 tablas**.
> Se eliminaron las funcionalidades sin interfaz (`recuperar_contrasena`, `cierre_caja`, `respaldos_bd`,
> `notificaciones`, `calificaciones_platos`, `preferencias_cliente`, `platos_favoritos`, `puntos_fidelidad`,
> `reservas_recurrentes`, `sucursales`, `permisos`/`roles_permisos` y `cupones_descuento`/`uso_cupon_cliente`)
> y se normalizó `productos.categoria` en la nueva tabla `categorias`.

## Diagrama en formato `.drawio`

Archivo editable: **`Diagrama_DER_Green.drawio`** (draw.io / diagrams.net).

Convenciones de la notación Barker empleados:

| Elemento | Representación |
|---|---|
| Entidad | Rectángulo con nombre en negrita y lista de atributos |
| Relación 1 : 1 | Flecha con `ERone` en ambos extremos |
| Relación 1 : N | Flecha con `ERone` en el lado padre y `ERmany` en el lado hijo |
| Relación N : N | Flecha con `ERmany` en ambos extremos |
| Clave primaria | Sufijo `PK` en el nombre del atributo |
| Clave foránea | Sufijo `FK` en el nombre del atributo |

## Diagrama en sintaxis Mermaid

```mermaid
erDiagram
    ROLES ||--o{ USUARIOS : posee
    AREAS ||--o{ MESAS : contiene
    AREAS ||--o{ HORARIOS_FUNCIONAMIENTO : opera
    AREAS ||--o{ BLOQUES_MESA : gestiona
    MESAS ||--o{ DISPONIBILIDAD_MESA : genera_cupos
    MESAS ||--o{ RESERVAS : recibe
    MESAS ||--o{ BLOQUES_MESA : sufre
    MESAS |o--o{ PEDIDOS : alberga
    USUARIOS |o--o{ DISPONIBILIDAD_MESA : abre
    USUARIOS |o--o{ BLOQUES_MESA : registra
    USUARIOS ||--o{ RESERVAS : efectua
    USUARIOS |o--o{ PEDIDOS : realiza
    USUARIOS |o--o{ MENU_SEMANAL : publica
    USUARIOS |o--o{ HISTORIAL_MOVIMIENTOS : audita
    USUARIOS |o--o{ CONFIGURACION_SISTEMA : modifica
    USUARIOS |o--|| REPARTIDORES : es
    CATEGORIAS ||--o{ PRODUCTOS : agrupa
    PRODUCTOS ||--|| STOCK_PRODUCTO : inventaria
    PRODUCTOS ||--o{ DETALLE_PEDIDOS : vendido_en
    PRODUCTOS ||--o{ MENU_SEMANAL_ITEMS : incluido_en
    PEDIDOS ||--|{ DETALLE_PEDIDOS : contiene
    PEDIDOS }o--o| ZONAS_DELIVERY : entrega_en
    ZONAS_DELIVERY ||--o{ REPARTIDORES : asigna
    MENU_SEMANAL ||--o{ MENU_SEMANAL_ITEMS : contiene

    %% ===== Entidades =====
    AREAS {
        int id_area PK
        string nombre
        string descripcion
        tinyint activo
        datetime fecha_creacion
    }
    BLOQUES_MESA {
        int id_bloque PK
        int id_mesa FK
        datetime fecha_inicio FK
        datetime fecha_fin
        string motivo
        int creado_por FK
        datetime fecha_creacion
    }
    CATEGORIAS {
        int id_categoria PK
        string nombre UK
        tinyint activo
    }
    CONFIGURACION_SISTEMA {
        string clave PK
        text valor
        string descripcion
        datetime fecha_actualizacion
        int actualizado_por FK
    }
    CONTADOR_PEDIDOS_SIN_RESERVA {
        date fecha PK
        enum tipo_pedido PK
        int cantidad_pedidos
        datetime fecha_actualizacion
    }
    DETALLE_PEDIDOS {
        int id_detalle PK
        int id_pedido FK
        int id_producto FK
        int cantidad
        decimal precio_unitario
    }
    DISPONIBILIDAD_MESA {
        int id_disponibilidad PK
        date fecha FK
        time hora_inicio
        time hora_fin
        int mesas_disponibles
        tinyint activo
        int creado_por FK
        datetime fecha_creacion
    }
    HISTORIAL_MOVIMIENTOS {
        int id_historial PK
        string tabla_afectada FK
        int id_registro
        string accion FK
        text detalles
        int id_usuario FK
        datetime fecha_movimiento
        string direccion_ip
    }
    HORARIOS_FUNCIONAMIENTO {
        int id_horario PK
        int id_area FK
        tinyint dia_semana
        time hora_apertura
        time hora_cierre
        tinyint activo
        datetime fecha_creacion
    }
    INTENTOS_LOGIN {
        string email FK
        tinyint exitoso
        datetime fecha_intento
        string direccion_ip
    }
    MENU_SEMANAL {
        int id_menu_semanal PK
        date semana_inicio UK
        date semana_fin
        tinyint activo
        int creado_por FK
        datetime fecha_creacion
    }
    MENU_SEMANAL_ITEMS {
        int id_item PK
        int id_menu_semanal FK
        int id_producto FK
        tinyint disponible
        decimal precio_semanal
    }
    MESAS {
        int id_mesa PK
        int id_area FK
        string numero_mesa
        int capacidad
        tinyint activo
        datetime fecha_creacion
    }
    MIGRACIONES_APLICADAS {
        string version PK
        datetime fecha_aplicacion
    }
    PEDIDOS {
        int id_pedido PK
        int id_mesa FK
        int id_mesero FK
        int id_usuario FK
        datetime fecha_pedido FK
        string estado FK
        decimal total
        datetime fecha_actualizacion
        enum tipo_pedido FK
        decimal senia
        enum estado_senia
        decimal saldo_pendiente FK
        string direccion_domicilio
        string numero_pedido FK
        int id_zona_delivery FK
        decimal costo_envio
        int id_repartidor FK
        enum estado_entrega FK
        datetime fecha_pedido_hora
        string observaciones
        datetime fecha_creacion
    }
    PRODUCTOS {
        int id_producto PK
        string nombre
        int id_categoria FK
        decimal precio
        string imagen_url
        string imagen_public_id
        tinyint activo
        datetime fecha_creacion
    }
    RATE_LIMIT_STATE {
        string clave PK
        decimal tokens
        datetime ultima_actualizacion
    }
    REPARTIDORES {
        int id_repartidor PK
        int id_usuario UK
        string nombre
        string telefono
        tinyint activo FK
        datetime fecha_creacion
    }
    RESERVAS {
        int id_reserva PK
        int id_mesa FK
        int id_usuario FK
        int comensales
        string telefono
        string ocasion
        date fecha_reserva FK
        time hora_inicio
        time hora_fin
        string estado FK
        datetime fecha_creacion
        datetime fecha_actualizacion
        string observaciones
    }
    ROLES {
        int id_rol PK
        string nombre UK
        string descripcion
        datetime fecha_creacion
    }
    STOCK_PRODUCTO {
        int id_stock PK
        int id_producto UK
        int stock_actual
        int limite_venta
        tinyint activo
        datetime fecha_actualizacion
    }
    USUARIOS {
        int id_usuario PK
        string nombre
        string email UK
        string telefono
        string contrasena_hash
        int id_rol FK
        tinyint activo
        datetime fecha_creacion
        datetime fecha_actualizacion
    }
    ZONAS_DELIVERY {
        int id_zona PK
        string nombre
        decimal costo_envio
        int tiempo_estimado_minutos
        text cobertura
        tinyint activa FK
        datetime fecha_creacion
    }
```

## Diccionario de datos

El detalle campo por campo de las 23 entidades (tipo, tamaño, nulidad, valor por defecto y
descripción funcional) está en el entregable **`13_diccionario_datos.md`**.

## Resumen de entidades

| # | Entidad | PK | FK principales | Descripción |
|---|---|---|---|---|
| 1 | `areas` | id_area | — | Salones o zonas físicas del restaurante. |
| 2 | `bloques_mesa` | id_bloque | creado_por → usuarios, id_mesa → mesas | Bloqueos de una mesa en un rango de fechas. |
| 3 | `categorias` | id_categoria | — | Categorías de la carta (Entradas, Platos principales, Postres, Bebidas, Coctelería). |
| 4 | `configuracion_sistema` | clave | actualizado_por → usuarios | Parámetros del restaurante (límites de pedidos, seña, moneda). |
| 5 | `contador_pedidos_sin_reserva` | fecha | — | Contador diario de pedidos sin reserva por modalidad. |
| 6 | `detalle_pedidos` | id_detalle | id_pedido → pedidos, id_producto → productos | Líneas de cada pedido: producto, cantidad y precio unitario. |
| 7 | `disponibilidad_mesa` | id_disponibilidad | creado_por → usuarios | **Cupos de reserva**: fecha + franja horaria + mesas disponibles. |
| 8 | `historial_movimientos` | id_historial | id_usuario → usuarios | Auditoría de todos los movimientos del sistema. |
| 9 | `horarios_funcionamiento` | id_horario | id_area → areas | Horario de apertura y cierre por área y día. |
| 10 | `intentos_login` |  | — | Auditoría de intentos de ingreso (control de fuerza bruta). |
| 11 | `menu_semanal` | id_menu_semanal | creado_por → usuarios | Menú publicado por semana. |
| 12 | `menu_semanal_items` | id_item | id_menu_semanal → menu_semanal, id_producto → productos | Platos del menú semanal con precio especial. |
| 13 | `mesas` | id_mesa | id_area → areas | Mesas físicas con capacidad, dentro de un área. |
| 14 | `migraciones_aplicadas` | version | — | Registro de migraciones aplicadas a la base de datos. |
| 15 | `pedidos` | id_pedido | id_mesa → mesas, id_mesero → usuarios, id_repartidor → repartidores, id_usuario → usuarios, id_zona_delivery → zonas_delivery | Pedidos con tipo, seña, saldo y estado de entrega. |
| 16 | `productos` | id_producto | id_categoria → categorias | Catálogo de platos y bebidas con precio e imagen. |
| 17 | `rate_limit_state` | clave | — | Cubo de tokens del límite de peticiones. |
| 18 | `repartidores` | id_repartidor | id_usuario → usuarios | Personal de reparto con su usuario asociado. |
| 19 | `reservas` | id_reserva | id_mesa → mesas, id_usuario → usuarios | Reservas de mesa **confirmadas** con los datos del cliente. |
| 20 | `roles` | id_rol | — | Roles del sistema (cliente, encargado, administrador). |
| 21 | `stock_producto` | id_stock | id_producto → productos | Inventario y límite de venta por producto (1:1). |
| 22 | `usuarios` | id_usuario | id_rol → roles | Personas registradas con credenciales y rol. |
| 23 | `zonas_delivery` | id_zona | — | Zonas de entrega con costo y tiempo estimado. |
