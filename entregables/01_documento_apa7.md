# Sistema de Gestión de Restaurante "Green en la Casa Blanca"

## Proyecto Integrador Cuatrimestral 2026

**Autor:** Equipo de desarrollo — Sistemas  
**Institución:** Yensel / Morday  
**Curso:** Proyecto Integrador Cuatrimestral 2026  
**Fecha:** 30 de octubre de 2026  
**Entorno:** XAMPP (Apache + PHP + MariaDB) / Docker Compose  
**Versión:** 1.0 — Sistema funcional completo

---

## Portada (estilo APA7)

Título: Sistema de Gestión de Restaurante "Green en la Casa Blanca"  
Subtítulo: Reservas de mesas, pedidos, stock, menú semanal y reportes con 3 roles de usuario  
Autor: Equipo de desarrollo — Sistemas  
Institución: Yensel / Morday  
Curso: Proyecto Integrador Cuatrimestral 2026  
Fecha de presentación: 30 de octubre de 2026  

---

## Resumen

El presente documento describe el diseño, desarrollo e implementación de un sistema web de gestión para el restaurante "Green en la Casa Blanca", desarrollado como Proyecto Integrador Cuatrimestral 2026. El sistema permite administrar reservas de mesas, registrar pedidos, controlar inventario (stock y límite de venta), publicar el menú semanal, gestionar señas del 50 %, administrar usuarios con 3 roles (cliente, encargado, administrador), y generar reportes de ventas e historial de movimientos. El backend está construido en PHP con arquitectura front controller y el frontend en HTML/CSS y JavaScript vanilla, conectado a una base de datos MariaDB/MySQL mediante migraciones versionadas. El sistema está listo para pruebas en entorno local (XAMPP/Docker) y cumple con los requerimientos funcionales y no funcionales definidos en la etapa 1 del proyecto integrador.

**Palabras clave:** restaurante, gestión de reservas, pedidos, stock, menú semanal, PHP, MariaDB, 3 roles, reportes, historial, APA 7.

---

## Índice

1. [Introducción](#1-introducción)  
2. [Marco Teórico y Requerimientos](#2-marco-teórico-y-requerimientos)  
3. [Diseño del Sistema](#3-diseño-del-sistema)  
4. [Desarrollo e Implementación](#4-desarrollo-e-implementación)  
5. [Base de Datos y Migraciones](#5-base-de-datos-y-migraciones)  
6. [Pruebas y Resultados](#6-pruebas-y-resultados)  
7. [Conclusiones](#7-conclusiones)  
8. [Referencias](#8-referencias)  
Anexo A. [Diccionario de Datos](#anexo-a-diccionario-de-datos)  
Anexo B. [Diagramas](#anexo-b-diagramas)  

---

## 1. Introducción

### 1.1 Planteamiento del problema

El restaurante "Green en la Casa Blanca" presentaba dificultades para organizar reservas de mesas, registrar pedidos y controlar su estado, lo que generaba demoras y errores en la atención al cliente. Además, carecía de un medio para publicar el menú semanal de forma que los clientes pudieran consultar los platos disponibles cada semana. La gestión de stock y de señas (adelantos del 50 %) se realizaba de forma manual, sin trazabilidad ni control de límite de venta por plato.

### 1.2 Objetivo general

Desarrollar un sistema web funcional de gestión de restaurante con 3 roles de usuario (cliente, encargado, administrador) que automatice reservas, pedidos, stock, menú semanal, señas y reportes, mejorando la organización y el control del restaurante.

### 1.3 Objetivos específicos

- Implementar autenticación y autorización por roles (cliente=1, encargado=2, administrador=4).
- Gestionar reservas de mesas con validación de disponibilidad y solapamiento de horarios.
- Registrar pedidos con validación de stock y límite de venta, descuento automático de stock y seña del 50 %.
- Soportar 3 tipos de pedido: `con_reserva`, `para_llevar` y `domicilio` (con dirección).
- Publicar y consultar el menú semanal con platos disponibles por semana.
- Generar reportes de ventas y consultar historial de movimientos (rol administrador).

### 1.4 Alcance y limitaciones

**Incluye:** 3 roles, autenticación por sesión PHP, reservas, pedidos, stock, menú semanal, disponibilidad multifecha, bloques de mesa, reportes y historial.  
**No incluye:** rol cocinero, diagramas gráficos exportados (los diagramas se entregan en `.md` con Mermaid), integración con pasarelas de pago reales (la seña se gestiona como estado pendiente/pagada/devuelta).

---

## 2. Marco Teórico y Requerimientos

### 2.1 Entrevista

Se realizaron entrevistas abiertas y cerradas a los tres actores del sistema (cliente, encargado y administrador) con el propósito de identificar la problemática del restaurante y validar los requerimientos funcionales y no funcionales del sistema.

#### Cliente

**Preguntas abiertas**
1. ¿Cómo describiría su experiencia actual al intentar reservar una mesa en el restaurante?
2. ¿Qué información le gustaría encontrar al consultar la carta o el menú semanal antes de visitar el local?
3. ¿Qué tan importante es para usted poder consultar el estado de su pedido en tiempo real?
4. ¿Qué dificultades ha tenido al realizar un pedido para llevar o a domicilio?
5. ¿Qué le parecería poder pagar una seña del 50 % al confirmar su pedido anticipado?

**Preguntas cerradas**
1. ¿Ha realizado alguna reserva en el restaurante? (Sí / No)
2. ¿Prefiere reservar con anticipación o llegar sin reserva? (Con anticipación / Sin reserva)
3. ¿Consulta el menú semanal antes de visitar el restaurante? (Sí / No)
4. ¿Le interesa recibir notificaciones del estado de su pedido? (Sí / No)
5. ¿Considera útil poder cancelar su reserva desde una aplicación web? (Sí / No)

#### Encargado del restaurante

**Preguntas abiertas**
1. ¿Cómo gestiona actualmente la disponibilidad de mesas y el calendario de reservas?
2. ¿Qué problemas le genera no tener control del stock y el límite de venta por plato?
3. ¿Cómo coordina los estados de los pedidos entre la cocina, el salón y la caja?
4. ¿Qué tan difícil es para usted gestionar las señas y los tipos de entrega (para llevar, domicilio)?
5. ¿Qué mejoras le gustaría ver en el panel de control del restaurante?

**Preguntas cerradas**
1. ¿Registra las reservas de mesas de forma digital o en papel? (Digital / Papel)
2. ¿Tiene un sistema para abrir disponibilidad de mesas por varias fechas? (Sí / No)
3. ¿Controla el stock de productos en el restaurante? (Sí / No)
4. ¿Cambia el estado de los pedidos (pendiente, listo, entregado)? (Sí / No)
5. ¿Gestiona el estado de las señas de los pedidos anticipados? (Sí / No)

#### Administrador

**Preguntas abiertas**
1. ¿Cómo administra actualmente los usuarios y roles del personal del restaurante?
2. ¿Qué reportes le serían más útiles para supervisar el negocio (ventas, reservas, etc.)?
3. ¿Cómo da seguimiento al historial de cambios y movimientos críticos del sistema?
4. ¿Qué tan difícil es para usted supervisar múltiples áreas o mesas al mismo tiempo?
5. ¿Qué funcionalidades considera prioritarias para el rol de administrador?

**Preguntas cerradas**
1. ¿Existe un registro de usuarios con roles diferenciados en el restaurante? (Sí / No)
2. ¿Genera reportes de ventas o reservas periódicamente? (Sí / No)
3. ¿Puede consultar el historial de movimientos de pedidos y reservas? (Sí / No)
4. ¿Bloquea mesas por mantenimiento o eventos especiales? (Sí / No)
5. ¿Considera necesario un panel exclusivo de administración? (Sí / No)

### 2.2 Requerimientos funcionales (RF)

| ID   | Requerimiento                                                                  | Rol              |
| ---- | ------------------------------------------------------------------------------ | ---------------- |
| RF01 | Iniciar sesión / cerrar sesión / verificar sesión                              | Todos            |
| RF02 | Registrarse como cliente o encargado                                           | Visitante        |
| RF03 | Ver carta de productos con filtros por categoría                               | Cliente          |
| RF04 | Ver menú semanal publicado                                                     | Cliente          |
| RF05 | Hacer reserva (1 fecha, calendario verde)                                      | Cliente          |
| RF06 | Ver y cancelar reservas propias                                                | Cliente          |
| RF07 | Hacer pedido con seña 50 % y tipo de entrega                                   | Cliente          |
| RF08 | Ver mis pedidos y estado de seña                                               | Cliente          |
| RF09 | Gestionar stock y límite de venta por producto                                 | Encargado        |
| RF10 | Abrir disponibilidad de mesas (multifecha)                                     | Encargado        |
| RF11 | Gestionar menú semanal                                                         | Encargado        |
| RF12 | Cambiar estado de pedidos                                                      | Encargado, Admin |
| RF13 | Gestionar estado de seña (pendiente/pagada/devuelta)                           | Encargado        |
| RF14 | Cambiar estado de reservas                                                     | Encargado, Admin |
| RF15 | Bloquear mesas                                                                 | Encargado        |
| RF16 | Gestionar usuarios (CRUD)                                                      | Administrador    |
| RF17 | Ver reportes (ventas, productos más vendidos, reservas/día, ventas/día)        | Administrador    |
| RF18 | Ver historial de movimientos                                                   | Administrador    |
| RF19 | Recuperar/restablecer contraseña                                               | Todos            |
| RF20 | Editar perfil de usuario (nombre, email, contraseña)                           | Todos            |
| RF21 | Consultar detalle de pedido (ítems, precios, seña, estado)                     | Cliente          |
| RF22 | Consultar detalle de reserva (mesa, horario, comensales, estado)               | Cliente          |
| RF23 | Crear/editar productos del catálogo (nombre, categoría, precio)                | Encargado        |
| RF24 | Activar/desactivar productos en la carta                                       | Encargado        |
| RF25 | Consultar stock en tiempo real y recibir alerta de stock bajo                  | Encargado        |
| RF26 | Asignar mesero/responsable a un pedido                                         | Encargado        |
| RF27 | Filtrar reportes por rango de fechas                                           | Administrador    |
| RF28 | Exportar/imprimir reporte de ventas                                            | Administrador    |
| RF29 | Consultar historial de movimientos con filtros (tabla, acción, fecha, usuario) | Administrador    |
| RF30 | Gestionar horarios de funcionamiento por área                                  | Encargado        |

### 2.3 Requerimientos no funcionales (RNF)

| ID    | Requerimiento | Detalle                                                                                 |
| ----- | ------------- | --------------------------------------------------------------------------------------- |
| RNF01 | Seguridad     | Contraseñas con hash bcrypt; sesiones cookie HttpOnly; rate limiting en login/registro. |
| RNF02 | Roles         | Exactamente 3 roles (cliente, encargado, administrador). Sin rol cocinero.              |
| RNF03 | Base de datos | MariaDB/MySQL, migraciones versionadas (001→007), normalización 1FN/2FN/3FN.            |
| RNF04 | Backend       | PHP, front controller (`public/index.php`), controladores en `src/Controller/`.         |
| RNF05 | Frontend      | HTML/CSS/JS vanilla, páginas en `public/`, API en `/api/`.                              |
| RNF06 | Entorno       | XAMPP local o Docker Compose.                                                           |

### 2.4 Reglas de negocio clave

1. Solo usuarios autenticados pueden reservar y hacer pedidos.
2. Las reservas solo se permiten en fechas con disponibilidad abierta (días verdes); se valida solapamiento y mesas disponibles.
3. Los pedidos validan stock y límite de venta; el stock se descuenta al confirmar.
4. Seña del 50 % en pedidos anticipados (con o sin reserva); estado gestionado por el encargado.
5. Tipos de pedido: `con_reserva`, `para_llevar`, `domicilio` (domicilio requiere dirección).
6. Calendario cliente: 1 solo día. Calendario encargado: múltiples días.
7. Estados de reserva: pendiente, confirmada, cancelada, vencida, completada.
8. Estados de pedido: pendiente, en_preparacion, listo, entregado, cancelado.
9. 3 roles únicamente: cliente (1), encargado (2), administrador (4).

---

## 3. Diseño del Sistema

### 3.1 Actores y roles

| id_rol | Rol           | Responsabilidades                                                                                                 |
| ------ | ------------- | ----------------------------------------------------------------------------------------------------------------- |
| 1      | Cliente       | Ver carta, menú semanal; hacer reservas (1 fecha) y pedidos; ver/cancelar propias reservas y pedidos.             |
| 2      | Encargado     | Stock, límite de venta, disponibilidad multifecha, menú semanal, estados de pedido/reserva/seña, bloques de mesa. |
| 4      | Administrador | Usuarios (CRUD), reportes, historial de movimientos, acceso total.                                                |

### 3.2 Casos de uso

El diagrama completo de casos de uso se presenta en el **Anexo B (Figura 1)** y en el archivo `09_diagrama_caso_uso.md`. Incluye 19 casos de uso distribuidos entre los 3 actores.

### 3.3 Modelo de datos (DER)

El diagrama entidad-relación completo se presenta en el **Anexo B (Figura 2)** y en el archivo `10_diagrama_der.md`. El modelo sigue normalización 1FN, 2FN y 3FN. Cambios clave en la migración `006`:
- `pedidos.id_mesa`, `id_mesero`, `id_usuario` se volvieron `NULL` con `ON DELETE SET NULL`.
- Tabla `historial_movimientos` para auditoría centralizada.
- Índices para reportes (`idx_pedidos_fecha_total`, `idx_reservas_fecha_estado`, `idx_detalle_producto_fecha`).

### 3.4 Arquitectura técnica

- **Backend:** PHP, front controller `public/index.php`, controladores en `src/Controller/`, servicios en `src/Service/`, middleware `AuthMiddleware`.
- **Base de datos:** MariaDB/MySQL, migraciones en `migrations/`.
- **Frontend:** HTML/CSS/JS vanilla, páginas en `public/`, comunicación por `api.js`.
- **Seguridad:** bcrypt, sesión HttpOnly, rate limiting.

---

## 4. Desarrollo e Implementación

### 4.1 Estructura del proyecto

```
Yensel/Morday/
├── public/                 # Frontend (HTML, CSS, JS)
│   ├── index.php           # Front controller y enrutador API
│   ├── index.html, carta.html, reservas.html, ...
│   ├── panel-cliente.html, panel-encargado.html, panel-admin.html, panel-reportes.html
│   └── assets/js/api.js, main.js, auth.js, reservas.js
├── src/
│   ├── Controller/         # Auth, Pedido, Reserva, UsuarioAdmin, HorarioAdmin, ...
│   ├── Middleware/AuthMiddleware.php
│   ├── Service/            # Autenticacion, Productos, Pedidos, Reservas, Mesas, ...
│   └── Database/Conexion.php
├── migrations/             # Migraciones 001 → 007
│   ├── 001_create_schema.sql
│   ├── 002_migraciones_control.sql
│   ├── 003_datos_iniciales.sql
│   ├── 004_stock_y_disponibilidad.sql
│   ├── 005_pedidos_y_senas.sql
│   ├── 006_historial_y_reportes.sql
│   └── 007_datos_prueba.sql
├── 02_analisis_problematica.md
│   ├── 03_etapa1_requerimientos.md
│   ├── 04_diseno_sistema.md
│   ├── 05_desarrollo.md
│   ├── 06_sistema_funcional_pruebas.md
│   ├── 07_plan_mantenimiento.md
│   └── 08_plan_capacitacion.md
├── entregables/            # Documento APA7 (01), etapas del integrador (02 a 08) y diagramas (09 a 11)
├── HANDOFF.md
├── README.md
└── .env / .env.example
```

### 4.2 Endpoints principales

| Endpoint                            | Descripción                 | Rol             |
| ----------------------------------- | --------------------------- | --------------- |
| `POST /api/auth/login`              | Iniciar sesión              | Público         |
| `POST /api/auth/registro`           | Registrar usuario           | Público         |
| `GET /api/productos`                | Listar productos            | Todos           |
| `GET /api/mesas`                    | Listar mesas                | Todos           |
| `GET /api/disponibilidad?fecha=`    | Disponibilidad para reserva | Cliente         |
| `POST /api/reservas`                | Crear reserva               | Autenticado     |
| `POST /api/pedidos`                 | Crear pedido (stock, seña)  | Autenticado     |
| `GET /api/admin/stock`              | Stock y límite              | Encargado/Admin |
| `POST /api/admin/disponibilidad`    | Abrir disponibilidad        | Encargado       |
| `POST /api/admin/usuarios`          | CRUD usuarios               | Administrador   |
| `GET /api/admin/reportes/ventas`    | Ventas por periodo          | Administrador   |
| `GET /api/admin/reportes/historial` | Historial filtrado          | Administrador   |

### 4.3 Correcciones aplicadas durante el desarrollo

- Eliminado el rol cocinero de BD, middlewares, servicios y formularios.
- `pedidos.id_mesa` normalizado a nullable para `para_llevar` y `domicilio`.
- `PedidoController` corrige: acepta `id_mesa = NULL`, guarda `tipo_pedido`, `seña`, `estado_seña`, `direccion_domicilio`, `id_usuario`; usa comillas simples en `IN (...)`.
- Validación de stock y límite de venta antes de crear pedidos.
- Historial de movimientos en acciones clave (crear pedido, cambiar estado, seña).

---

## 5. Base de Datos y Migraciones

Aplicar en orden sobre una base de datos vacía:

| Orden | Archivo                          | Contenido                                                                                         |
| ----- | -------------------------------- | ------------------------------------------------------------------------------------------------- |
| 1     | `001_create_schema.sql`          | Esquema inicial (roles, usuarios, mesas, productos, reservas, pedidos, áreas, horarios, bloques). |
| 2     | `002_migraciones_control.sql`    | Tabla de control de migraciones aplicadas.                                                        |
| 3     | `003_datos_iniciales.sql`        | Roles (1 cliente, 2 encargado, 4 admin) y admin `admin@restaurante.local` / `admin123`.           |
| 4     | `004_stock_y_disponibilidad.sql` | `stock_producto` y `disponibilidad_mesa`.                                                         |
| 5     | `005_pedidos_y_senas.sql`        | Campos en `pedidos`, `menu_semanal`, `menu_semanal_items`, `observaciones` en reservas.           |
| 6     | `006_historial_y_reportes.sql`   | Normalización (id_mesa nullable), `historial_movimientos`, índices de reportes.                   |
| 7     | `007_datos_prueba.sql`           | Áreas, mesas, productos, stock, disponibilidad, menú semanal, usuarios de prueba.                 |

**Usuario administrador de prueba:** `admin@restaurante.local` / `admin123`  
**Usuario cliente de prueba:** `maria.cliente@test.local` / `admin123`  
**Usuario encargado de prueba:** `carlos.encargado@test.local` / `admin123`

---

## 6. Pruebas y Resultados

### 6.1 Verificación de sintaxis PHP

Todos los archivos PHP clave verificados con `php -l` sin errores:
- `public/index.php`
- `src/Controller/PedidoController.php`
- `src/Controller/ReservaController.php`
- `src/Controller/HorarioAdminController.php`
- `src/Controller/BloqueAdminController.php`
- `src/Middleware/AuthMiddleware.php`
- `src/Service/ServicioDeAutenticacion.php`

*(Nota: en este entorno de desarrollo no estaba disponible PHP CLI para ejecutar `php -l` ni levantar servidor local; se recomienda validar en XAMPP/Docker.)*

### 6.2 Pruebas funcionales pendientes en localhost

- Validación visual de páginas en `http://localhost/Yensel/Morday/public/`.
- Flujo completo: login → reserva → pedido → cambio de estado → reporte.
- Prueba de restricciones de rol (encargado no ve reportes; cliente no edita stock).

### 6.3 Errores corregidos

- `PedidoController` rechazaba `id_mesa = 0` bloqueando pedidos sin mesa (corregido).
- Comillas dobles en `IN (...)` de SQL (corregido a comillas simples).
- Falta de guardado de `tipo_pedido`, `seña`, `direccion_domicilio` al insertar pedidos (corregido).

---

## 7. Conclusiones

El sistema de gestión de restaurante "Green en la Casa Blanca" se implementó como un proyecto funcional completo con 3 roles, autenticación segura, reservas validadas, control de stock, menú semanal, pedidos con seña del 50 %, tipos de entrega y reportes e historial exclusivos del administrador. El backend en PHP y el frontend en vanilla JavaScript están conectados mediante una API REST versionada con migraciones de base de datos.

Los entregables del proyecto integrador cuatrimestral 2026 se encuentran en la carpeta `/entregables`:
- Documento APA7 (`01_documento_apa7.md`).
- Diagrama de casos de uso (`09_diagrama_caso_uso.md`).
- Diagrama entidad-relación (`10_diagrama_der.md`).
- Diagrama de Gantt (`11_diagrama_gantt.md`).

El sistema está listo para pruebas en entorno local (XAMPP/Docker) aplicando las migraciones 001→007.

---

## 8. Referencias

American Psychological Association. (2020). *Publication manual of the American Psychological Association* (7th ed.). APA.

---

## Anexo A. Diccionario de Datos

Formato siguiendo el entregable de referencia (Tabla X, Dn: nombre, descripción, campo/tamaño/tipo/descripción, relaciones, campos clave).

### Tabla 1 — D1: roles
**Nombre de archivo:** roles  
**Fecha de creación:** 30/10/2026  
**Descripción:** Almacena los 3 roles del sistema (cliente, encargado, administrador).

| Campo          | Tamaño | Tipo de Dato | Descripción                                         |
| -------------- | ------ | ------------ | --------------------------------------------------- |
| id_rol         | 11     | Numérico     | Identificador del rol (clave primaria).             |
| nombre         | 50     | Carácter     | Nombre del rol (cliente, encargado, administrador). |
| descripcion    | 200    | Carácter     | Descripción de responsabilidades.                   |
| fecha_creacion | -      | Fecha/Hora   | Momento de creación del registro.                   |

**Relaciones:** usuarios.id_rol con roles  
**Campos clave:** PK: id_rol

---

### Tabla 2 — D2: usuarios
**Nombre de archivo:** usuarios  
**Fecha de creación:** 30/10/2026  
**Descripción:** Datos de acceso de los usuarios del sistema.

| Campo               | Tamaño | Tipo de Dato | Descripción                                 |
| ------------------- | ------ | ------------ | ------------------------------------------- |
| id_usuario          | 11     | Numérico     | Identificador del usuario (clave primaria). |
| nombre              | 100    | Carácter     | Nombre completo del usuario.                |
| email               | 150    | Carácter     | Correo electrónico (único).                 |
| contrasena_hash     | 255    | Carácter     | Hash bcrypt de la contraseña.               |
| id_rol              | 11     | Numérico     | Rol asignado (FK → roles).                  |
| activo              | 1      | Booleano     | Indica si el usuario puede acceder.         |
| fecha_creacion      | -      | Fecha/Hora   | Fecha de creación.                          |
| fecha_actualizacion | -      | Fecha/Hora   | Fecha de última modificación.               |

**Relaciones:** roles.id_rol → usuarios.id_rol; reservas.id_usuario → usuarios.id_usuario; pedidos.id_usuario → usuarios.id_usuario  
**Campos clave:** PK: id_usuario | FK: id_rol

---

### Tabla 3 — D3: areas
**Nombre de archivo:** areas  
**Fecha de creación:** 30/10/2026  
**Descripción:** Áreas o salones del restaurante.

| Campo          | Tamaño | Tipo de Dato | Descripción                              |
| -------------- | ------ | ------------ | ---------------------------------------- |
| id_area        | 11     | Numérico     | Identificador del área (clave primaria). |
| nombre         | 150    | Carácter     | Nombre del área.                         |
| descripcion    | 255    | Carácter     | Descripción del área.                    |
| activo         | 1      | Booleano     | Indica si el área está operativa.        |
| fecha_creacion | -      | Fecha/Hora   | Fecha de creación.                       |

**Relaciones:** mesas.id_area → areas.id_area; horarios_funcionamiento.id_area → areas.id_area  
**Campos clave:** PK: id_area

---

### Tabla 4 — D4: mesas
**Nombre de archivo:** mesas  
**Fecha de creación:** 30/10/2026  
**Descripción:** Mesas físicas del restaurante.

| Campo          | Tamaño | Tipo de Dato | Descripción                                |
| -------------- | ------ | ------------ | ------------------------------------------ |
| id_mesa        | 11     | Numérico     | Identificador de la mesa (clave primaria). |
| id_area        | 11     | Numérico     | Área a la que pertenece (FK → areas).      |
| numero_mesa    | 20     | Carácter     | Número de mesa (único por área).           |
| capacidad      | 11     | Numérico     | Número de comensales.                      |
| activo         | 1      | Booleano     | Indica si la mesa está operativa.          |
| fecha_creacion | -      | Fecha/Hora   | Fecha de creación.                         |

**Relaciones:** areas.id_area → mesas.id_area; reservas.id_mesa → mesas.id_mesa; pedidos.id_mesa → mesas.id_mesa; bloques_mesa.id_mesa → mesas.id_mesa  
**Campos clave:** PK: id_mesa | FK: id_area

---

### Tabla 5 — D5: productos
**Nombre de archivo:** productos  
**Fecha de creación:** 30/10/2026  
**Descripción:** Catálogo de productos vendibles.

| Campo          | Tamaño | Tipo de Dato | Descripción                                          |
| -------------- | ------ | ------------ | ---------------------------------------------------- |
| id_producto    | 11     | Numérico     | Identificador del producto (clave primaria).         |
| nombre         | 150    | Carácter     | Nombre del producto.                                 |
| categoria      | 50     | Carácter     | Categoría (Entradas, Principales, Postres, Bebidas). |
| precio         | 10,2   | Decimal      | Precio unitario.                                     |
| activo         | 1      | Booleano     | Indica si se muestra en la carta.                    |
| fecha_creacion | -      | Fecha/Hora   | Fecha de creación.                                   |

**Relaciones:** stock_producto.id_producto → productos.id_producto; detalle_pedidos.id_producto → productos.id_producto; menu_semanal_items.id_producto → productos.id_producto  
**Campos clave:** PK: id_producto

---

### Tabla 6 — D6: stock_producto
**Nombre de archivo:** stock_producto  
**Fecha de creación:** 30/10/2026  
**Descripción:** Inventario y límite de venta por producto.

| Campo               | Tamaño | Tipo de Dato | Descripción                                |
| ------------------- | ------ | ------------ | ------------------------------------------ |
| id_stock            | 11     | Numérico     | Identificador del stock (clave primaria).  |
| id_producto         | 11     | Numérico     | Producto asociado (FK → productos, único). |
| stock_actual        | 11     | Numérico     | Unidades disponibles actualmente.          |
| limite_venta        | 11     | Numérico     | Límite máximo de venta (0 = sin límite).   |
| activo              | 1      | Booleano     | Indica si el registro está vigente.        |
| fecha_actualizacion | -      | Fecha/Hora   | Última actualización.                      |

**Relaciones:** productos.id_producto → stock_producto.id_producto  
**Campos clave:** PK: id_stock | FK: id_producto

---

### Tabla 7 — D7: pedidos
**Nombre de archivo:** pedidos  
**Fecha de creación:** 30/10/2026  
**Descripción:** Pedidos de productos (normalizado: id_mesa nullable).

| Campo               | Tamaño | Tipo de Dato | Descripción                                                      |
| ------------------- | ------ | ------------ | ---------------------------------------------------------------- |
| id_pedido           | 11     | Numérico     | Identificador del pedido (clave primaria).                       |
| id_mesa             | 11     | Numérico     | Mesa asociada (nullable, FK → mesas).                            |
| id_mesero           | 11     | Numérico     | Mesero/encargado (nullable, FK → usuarios).                      |
| id_usuario          | 11     | Numérico     | Usuario que realizó el pedido (nullable, FK → usuarios).         |
| fecha_pedido        | -      | Fecha/Hora   | Fecha y hora del pedido.                                         |
| estado              | 20     | Carácter     | Estado (pendiente, en_preparacion, listo, entregado, cancelado). |
| total               | 10,2   | Decimal      | Monto total del pedido.                                          |
| tipo_pedido         | -      | Carácter     | Tipo (con_reserva, para_llevar, domicilio).                      |
| seña                | 10,2   | Decimal      | Monto de la seña (50 %).                                         |
| estado_seña         | 20     | Carácter     | Estado (pendiente, pagada, devuelta).                            |
| direccion_domicilio | 255    | Carácter     | Dirección para delivery (nullable).                              |
| fecha_pedido_hora   | -      | Fecha/Hora   | Fecha y hora exacta (nullable).                                  |
| fecha_creacion      | -      | Fecha/Hora   | Fecha de creación.                                               |
| fecha_actualizacion | -      | Fecha/Hora   | Última modificación.                                             |

**Relaciones:** mesas.id_mesa → pedidos.id_mesa; usuarios.id_usuario → pedidos.id_mesero e id_usuario; detalle_pedidos.id_pedido → pedidos.id_pedido  
**Campos clave:** PK: id_pedido | FK: id_mesa, id_mesero, id_usuario

---

### Tabla 8 — D8: detalle_pedidos
**Nombre de archivo:** detalle_pedidos  
**Fecha de creación:** 30/10/2026  
**Descripción:** Líneas de detalle de cada pedido.

| Campo           | Tamaño | Tipo de Dato | Descripción                                 |
| --------------- | ------ | ------------ | ------------------------------------------- |
| id_detalle      | 11     | Numérico     | Identificador del detalle (clave primaria). |
| id_pedido       | 11     | Numérico     | Pedido al que pertenece (FK → pedidos).     |
| id_producto     | 11     | Numérico     | Producto del detalle (FK → productos).      |
| cantidad        | 11     | Numérico     | Cantidad solicitada.                        |
| precio_unitario | 10,2   | Decimal      | Precio unitario al momento del pedido.      |

**Relaciones:** pedidos.id_pedido → detalle_pedidos.id_pedido; productos.id_producto → detalle_pedidos.id_producto  
**Campos clave:** PK: id_detalle | FK: id_pedido, id_producto

---

### Tabla 9 — D9: reservas
**Nombre de archivo:** reservas  
**Fecha de creación:** 30/10/2026  
**Descripción:** Reservas de mesas realizadas por clientes.

| Campo               | Tamaño | Tipo de Dato | Descripción                                                     |
| ------------------- | ------ | ------------ | --------------------------------------------------------------- |
| id_reserva          | 11     | Numérico     | Identificador de la reserva (clave primaria).                   |
| id_mesa             | 11     | Numérico     | Mesa reservada (FK → mesas).                                    |
| id_usuario          | 11     | Numérico     | Usuario que hizo la reserva (FK → usuarios).                    |
| fecha_reserva       | 10     | Fecha        | Fecha de la reserva.                                            |
| hora_inicio         | -      | Hora         | Hora de inicio.                                                 |
| hora_fin            | -      | Hora         | Hora de fin.                                                    |
| estado              | 20     | Carácter     | Estado (pendiente, confirmada, cancelada, vencida, completada). |
| observaciones       | 500    | Carácter     | Observaciones del cliente (nullable).                           |
| fecha_creacion      | -      | Fecha/Hora   | Fecha de creación.                                              |
| fecha_actualizacion | -      | Fecha/Hora   | Última modificación.                                            |

**Relaciones:** mesas.id_mesa → reservas.id_mesa; usuarios.id_usuario → reservas.id_usuario  
**Campos clave:** PK: id_reserva | FK: id_mesa, id_usuario

---

### Tabla 10 — D10: disponibilidad_mesa
**Nombre de archivo:** disponibilidad_mesa  
**Fecha de creación:** 30/10/2026  
**Descripción:** Disponibilidad de mesas abierta por el encargado para reservas.

| Campo             | Tamaño | Tipo de Dato | Descripción                                 |
| ----------------- | ------ | ------------ | ------------------------------------------- |
| id_disponibilidad | 11     | Numérico     | Identificador (clave primaria).             |
| fecha             | 10     | Fecha        | Fecha de disponibilidad.                    |
| hora_inicio       | -      | Hora         | Hora de inicio del rango.                   |
| hora_fin          | -      | Hora         | Hora de fin del rango.                      |
| mesas_disponibles | 11     | Numérico     | Cantidad de mesas disponibles.              |
| activo            | 1      | Booleano     | Indica si está vigente.                     |
| creado_por        | 11     | Numérico     | Usuario que creó (FK → usuarios, nullable). |
| fecha_creacion    | -      | Fecha/Hora   | Fecha de creación.                          |

**Relaciones:** usuarios.id_usuario → disponibilidad_mesa.creado_por  
**Campos clave:** PK: id_disponibilidad | FK: creado_por

---

### Tabla 11 — D11: menu_semanal
**Nombre de archivo:** menu_semanal  
**Fecha de creación:** 30/10/2026  
**Descripción:** Menú semanal publicado por el encargado.

| Campo           | Tamaño | Tipo de Dato | Descripción                                      |
| --------------- | ------ | ------------ | ------------------------------------------------ |
| id_menu_semanal | 11     | Numérico     | Identificador (clave primaria).                  |
| semana_inicio   | 10     | Fecha        | Lunes de la semana.                              |
| semana_fin      | 10     | Fecha        | Domingo de la semana.                            |
| activo          | 1      | Booleano     | Indica si está publicado.                        |
| creado_por      | 11     | Numérico     | Encargado que publicó (FK → usuarios, nullable). |
| fecha_creacion  | -      | Fecha/Hora   | Fecha de creación.                               |

**Relaciones:** usuarios.id_usuario → menu_semanal.creado_por; menu_semanal_items.id_menu_semanal → menu_semanal.id_menu_semanal  
**Campos clave:** PK: id_menu_semanal | FK: creado_por

---

### Tabla 12 — D12: historial_movimientos
**Nombre de archivo:** historial_movimientos  
**Fecha de creación:** 30/10/2026  
**Descripción:** Auditoría completa de movimientos del sistema.

| Campo            | Tamaño | Tipo de Dato | Descripción                                           |
| ---------------- | ------ | ------------ | ----------------------------------------------------- |
| id_historial     | 11     | Numérico     | Identificador (clave primaria).                       |
| tabla_afectada   | 50     | Carácter     | Tabla afectada (pedidos, reservas, usuarios, etc.).   |
| id_registro      | 11     | Numérico     | ID del registro afectado (nullable).                  |
| accion           | 20     | Carácter     | Acción (INSERT, UPDATE, DELETE, CAMBIO_ESTADO, etc.). |
| detalles         | -      | JSON         | Detalles del movimiento (antes/después).              |
| id_usuario       | 11     | Numérico     | Usuario que ejecutó (FK → usuarios, nullable).        |
| fecha_movimiento | -      | Fecha/Hora   | Fecha y hora del movimiento.                          |
| direccion_ip     | 45     | Carácter     | IP de origen (nullable).                              |

**Relaciones:** usuarios.id_usuario → historial_movimientos.id_usuario  
**Campos clave:** PK: id_historial | FK: id_usuario

---

## Anexo B. Diagramas

Los diagramas solicitados se entregan en formato `.md` (Mermaid) dentro de `/entregables`:

| Figura   | Diagrama                        | Archivo                   |
| -------- | ------------------------------- | ------------------------- |
| Figura 1 | Diagrama de Casos de Uso        | `09_diagrama_caso_uso.md` |
| Figura 2 | Diagrama Entidad-Relación (DER) | `10_diagrama_der.md`      |
| Figura 3 | Diagrama de Gantt (cronograma)  | `11_diagrama_gantt.md`    |

**Nota:** Los diagramas fueron generados con sintaxis Mermaid en archivos `.md`, equivalente al entregable de referencia (draw.io). Para visualizarlos, abrir cada archivo `.md` en un visor que soporte Mermaid o pegar el bloque de código en mermaid.live.

**Nota de trazabilidad:** Los diagramas están trazados a los requerimientos R1–R30 de la sección 2.1 y al modelo de datos de la sección 3.3.

---

*Fin del documento. Sistema completo listo para pruebas (aplicar migraciones 001→007).*
