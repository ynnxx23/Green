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

El presente documento describe el análisis, diseño e implementación de un sistema web de gestión para el restaurante "Green en la Casa Blanca", desarrollado como Proyecto Integrador Cuatrimestral 2026. El sistema permite administrar reservas de mesas, registrar pedidos, controlar inventario (stock y límite de venta), publicar el menú semanal, gestionar señas del 50 %, administrar usuarios con 3 roles (cliente, encargado, administrador) y generar reportes de ventas e historial de movimientos. El backend está construido en PHP con arquitectura front controller y el frontend en HTML/CSS y JavaScript vanilla, conectado a una base de datos MariaDB/MySQL mediante migraciones versionadas. El sistema está listo para pruebas en entorno local (XAMPP/Docker) y cumple con los requerimientos funcionales y no funcionales definidos en la etapa 1 del proyecto integrador.

**Palabras clave:** restaurante, gestión de reservas, pedidos, stock, menú semanal, PHP, MariaDB, 3 roles, reportes, historial, APA 7.

---

## Contenido

1. Portada — 3  
2. Resumen — 4  
3. Situación Problemática — 4  
4. Diagrama de Gantt — 5  
5. Entrevistas — 5  
6. Entrevista al Administrador — 6  
7. Preguntas abierta — Administrador — 6  
8. Preguntas cerrada — Administrador (Sí/No) — 6  
9. Entrevista al Encargado — 7  
10. Preguntas abierta — Encargado — 7  
11. Preguntas cerrada — Encargado (Sí/No) — 7  
12. Entrevista al Cliente — 8  
13. Preguntas abierta — Cliente — 8  
14. Preguntas cerrada — Cliente (Sí/No) — 8  
15. Cuadro del Mundo del Problema — 9  
16. Matriz FODA — 9  
17. Objetivos — 10  
18. Objetivo General — 10  
19. Objetivos Específicos — 11  
20. Requerimientos Funcionales — 11  
21. Gestión de Usuarios (R1–R8) — 11  
22. Gestión de Reservas (R9–R14) — 13  
23. Gestión de Mesas y Áreas (R15–R18) — 15  
24. Gestión de Horarios y Disponibilidad (R19–R21) — 17  
25. Gestión de Pedidos (R22–R29) — 18  
26. Gestión de Productos y Menú (R30–R35) — 21  
27. Gestión de Delivery (R36–R39) — 24  
28. Gestión de Reportes (R40–R43) — 26  
29. Gestión de Fidelidad y Preferencias (R44–R48) — 28  
30. Gestión de Reservas Recurrentes e Imágenes (R49–R50) — 30  
31. Requerimientos No Funcionales — 31  
32. Alcance del Sistema — 32  
33. Alcance General — 32  
34. Funciones Incluidas en el Sistema — 32  
35. Despliegue del Programa — 35  
36. Limitaciones del Sistema — 35  
37. Especificaciones — 36  
38. Entregables del Proyecto — 38

---

## Situación Problemática

El restaurante "Green en la Casa Blanca" presenta dificultades para organizar las reservas de mesas, registrar los pedidos y controlar su estado de forma ordenada. Actualmente el control se realiza de manera manual o desordenada, lo que genera demoras en la atención, mesas ocupadas sin reserva registrada, pedidos extraviados o mal anotados, falta de trazabilidad sobre el estado de cada orden y errores al momento de cobrar o administrar las señas. Esto afecta la experiencia del cliente y la operación del local.

Frente a esta problemática surge la necesidad de desarrollar un sistema digital de gestión que permita registrar usuarios, reservas, pedidos, señas, stock y estados de los materiales, mejorando la administración, organización y seguimiento de los recursos disponibles dentro del restaurante.

De acuerdo con Pressman (2010), un relevamiento deficiente de los procesos manuales incrementa el riesgo de pérdida de trazabilidad y dificulta la toma de decisiones operativas, por lo que la digitalización se vuelve una estrategia prioritaria para comercios con atención intensiva al cliente.

---

## Diagrama de Gantt

El cronograma se organizó en semanas de desarrollo, distribuyendo las actividades críticas y no críticas según la disponibilidad horaria del equipo. Las actividades críticas se destacan en el diagrama de Gantt (Figura 1), entregado como archivo independiente `11_diagrama_gantt.md` (sintaxis Mermaid) dentro de `/entregables`.

**Figura 1 — Diagrama de Gantt: Cronograma de actividades (cuatrimestre 2026)**

Etapas del cronograma:
- Etapa 1: Análisis y Requerimientos (análisis de problemática, requerimientos funcionales y no funcionales, FODA y APA 7).
- Etapa 2: Diseño del Sistema (modelo de datos y DER, casos de uso, arquitectura y API REST, reglas de negocio).
- Etapa 3: Desarrollo (Core) (backend PHP, frontend HTML/JS vanilla, autenticación y middlewares, integración API-frontend).
- Etapa 4: Base de Datos (Core) (migraciones 001–003 esquema, migraciones 004–006 stock y normalización, datos de prueba migración 007).
- Etapa 5: Pruebas y Documentación (pruebas funcionales y de errores, correcciones, documentación integrador, entregables).

**Nota.** El diagrama de Gantt se encuentra en `entregables/11_diagrama_gantt.md`. Para visualizarlo, abrir el archivo en un visor que soporte Mermaid o pegar el bloque de código en mermaid.live.

---

## Entrevistas

Para el relevamiento se diseñaron entrevistas abiertas y cerradas dirigidas a los tres perfiles clave del sistema: **administrador**, **encargado** y **cliente**. Las entrevistas permiten contrastar la percepción operativa, de gestión y de uso final del sistema.

---

## Entrevista al Administrador

### Preguntas abierta — Administrador

1. ¿Cómo administra actualmente los usuarios y roles del personal del restaurante?  
2. ¿Qué reportes le serían más útiles para supervisar el negocio (ventas, reservas, pedidos)?  
3. ¿Cómo da seguimiento al historial de cambios y movimientos críticos del sistema?  
4. ¿Qué tan difícil es para usted supervisar múltiples áreas o mesas al mismo tiempo?  
5. ¿Qué funcionalidades considera prioritarias para el rol de administrador?

### Preguntas cerrada — Administrador (Sí/No)

Preguntas cerradas dirigidas al administrador

1. ¿Existe un registro de usuarios con roles diferenciados en el restaurante? Sí/No  
2. ¿Genera reportes de ventas o reservas periódicamente? Sí/No  
3. ¿Puede consultar el historial de movimientos de pedidos y reservas? Sí/No  
4. ¿Bloquea mesas por mantenimiento o eventos especiales? Sí/No  
5. ¿Considera necesario un panel exclusivo de administración? Sí/No  
6. ¿Asigna roles y permisos a los usuarios del sistema? Sí/No  
7. ¿Requiere exportar reportes a PDF o Excel? Sí/No  
8. ¿Considera útil recibir alertas automáticas de stock bajo? Sí/No  
9. ¿Apoyaría la implementación de un sistema digital para el restaurante? Sí/No  
10. ¿Utilizaría un panel web para administrar usuarios y reportes? Sí/No

---

## Entrevista al Encargado

### Preguntas abierta — Encargado

1. ¿Cómo gestiona actualmente la disponibilidad de mesas y el calendario de reservas?  
2. ¿Qué problemas le genera no tener control del stock y el límite de venta por plato?  
3. ¿Cómo coordina los estados de los pedidos entre la cocina, el salón y la caja?  
4. ¿Qué tan difícil es para usted gestionar las señas y los tipos de entrega (para llevar, domicilio)?  
5. ¿Qué mejoras le gustaría ver en el panel de control del restaurante?

### Preguntas cerrada — Encargado (Sí/No)

Preguntas cerradas dirigidas al encargado

1. ¿Registra las reservas de mesas de forma digital o en papel? Sí/No  
2. ¿Tiene un sistema para abrir disponibilidad de mesas por varias fechas? Sí/No  
3. ¿Controla el stock de productos en el restaurante? Sí/No  
4. ¿Cambia el estado de los pedidos (pendiente, listo, entregado)? Sí/No  
5. ¿Gestiona el estado de las señas de los pedidos anticipados? Sí/No  
6. ¿Registra los productos del menú semanal con precios y disponibilidad? Sí/No  
7. ¿Considera útil un historial digital de movimientos de pedidos? Sí/No  
8. ¿Sería útil recibir alertas automáticas de pedidos vencidos o stock bajo? Sí/No  
9. ¿Considera necesario generar reportes automáticos de ventas? Sí/No  
10. ¿Apoyaría la implementación de un sistema digital para el restaurante? Sí/No

---

## Entrevista al Cliente

### Preguntas abierta — Cliente

1. ¿Cómo describiría su experiencia actual al intentar reservar una mesa en el restaurante?  
2. ¿Qué información le gustaría encontrar al consultar la carta o el menú semanal antes de visitar el local?  
3. ¿Qué tan importante es para usted poder consultar el estado de su pedido en tiempo real?  
4. ¿Qué dificultades ha tenido al realizar un pedido para llevar o a domicilio?  
5. ¿Qué le parecería poder pagar una seña del 50 % al confirmar su pedido anticipado?

### Preguntas cerrada — Cliente (Sí/No)

Preguntas cerradas dirigidas al cliente

1. ¿Ha realizado alguna reserva en el restaurante? Sí/No  
2. ¿Prefiere reservar con anticipación o llegar sin reserva? Sí/No  
3. ¿Consulta el menú semanal antes de visitar el restaurante? Sí/No  
4. ¿Le interesa recibir notificaciones del estado de su pedido? Sí/No  
5. ¿Considera útil poder cancelar su reserva desde una aplicación web? Sí/No  
6. ¿Ha pedido para llevar o a domicilio en el restaurante? Sí/No  
7. ¿Le gustaría consultar el menú semanal desde su celular? Sí/No  
8. ¿Considera útil pagar una seña del 50 % al confirmar un pedido anticipado? Sí/No  
9. ¿Le sería útil ver el historial de sus propios pedidos y reservas? Sí/No  
10. ¿Utilizaría un sistema digital para reservar mesas y hacer pedidos? Sí/No

---

## Cuadro del Mundo del Problema

**Tabla 1 — Cuadro del mundo del problema**

| Elemento | Descripción |
|---|---|
| Cliente | Restaurante "Green en la Casa Blanca" — clientes, encargado y administrador |
| Mundo del problema | El restaurante presenta la necesidad de mejorar la organización, el control y la administración de las reservas, pedidos, stock y señas utilizadas en el local. |
| Actualmente existen dificultades para | Registrar información, realizar seguimientos, controlar disponibilidades y acceder a datos actualizados para la gestión. Se requiere una solución informática que permita optimizar estas tareas y facilitar la administración de la información. |
| Requerimientos funcionales (RF) | Registrar, almacenar y administrar información. Permitir la consulta, actualización y eliminación de datos. Gestionar usuarios, reservas, pedidos, menú, stock y señas del restaurante. Facilitar el seguimiento de uso del sistema. Brindar herramientas de búsqueda y filtrado de información. |
| Requerimientos no funcionales (RNF) | Proteger la información almacenada en el sistema. Garantizar el correcto funcionamiento del sistema. Facilitar el uso del sistema por parte de los usuarios. Mantener la información disponible y organizada. Permitir futuras mejoras y actualizaciones. |

---

## Matriz FODA

**Tabla 2 — Matriz FODA — Factores del sistema de gestión del restaurante**

| Fortalezas | Debilidades |
|---|---|
| Tres roles claros: cliente, encargado y administrador, con menús y permisos distintos. | Requiere conexión a internet y dispositivos para operar. |
| Reservas organizadas por calendario con días disponibles y no disponibles. | Curva de aprendizaje para el personal no acostumbrado a tecnología. |
| Pedidos registrados con productos, cantidades, tipo de pedido y estado trazable. | Costo de mantenimiento del software y hosting. |
| Regla de seña del 50 % aplicada a pedidos anticipados con o sin reserva. | Posibles errores de carga manual si el personal no es constante. |
| Control del menú semanal y límite de venta por plato según disponibilidad. | |
| Mesas bloqueables por mantenimiento o eventos. | |
| Gestión de tipos de pedido: con reserva, para llevar y domicilio. | |

| Oportunidades | Amenazas |
|---|---|
| Escalabilidad a más módulos (facturación, delivery, múltiples sucursales). | Riesgo de seguridad si no se protegen credenciales y datos de usuarios. |
| Mejora continua basada en uso real y reportes del sistema. | Dependencia de proveedor de hosting o dominio. |
| Diferenciación competitiva por atención ordenada y rápida en el restaurante. | Cambios en normativas locales que afecten gestión de datos. |
| Control centralizado de señas, pedidos y reservas del menú semanal. | Pérdida de clientes por demoras y errores en la atención. |
| Digitalización de procesos administrativos dentro del restaurante. | Competencia con restaurantes que ya usan sistemas de gestión. |
| Generar historial de pedidos, reservas y señas para análisis futuro. | |

---

## Objetivos

### Objetivo General

Desarrollar un sistema web funcional de gestión de restaurante con 3 roles de usuario (cliente, encargado, administrador) que automatice reservas, pedidos, stock, menú semanal, señas y reportes, mejorando la organización y el control del restaurante.

### Objetivos Específicos

1. Implementar autenticación y autorización por roles (cliente, encargado, administrador).  
2. Gestionar reservas de mesas con validación de disponibilidad y solapamiento de horarios.  
3. Registrar pedidos con validación de stock y límite de venta, descuento automático de stock y seña del 50 %.  
4. Soportar 3 tipos de pedido: `con_reserva`, `para_llevar` y `domicilio` (con dirección).  
5. Publicar y consultar el menú semanal con platos disponibles por semana.  
6. Generar reportes de ventas y consultar historial de movimientos (rol administrador).  
7. Gestionar usuarios (CRUD) y asignar roles y permisos (rol administrador).

---

## Requerimientos Funcionales

A continuación se detallan los 50 requerimientos funcionales identificados, organizados por gestión. La especificación sigue el formato **Nombre-Tipo-Prioridad-Descripción-Entrada-Proceso-Salida**, recomendado por Sommerville (2016) para la documentación de requisitos. Cada requerimiento se especifica en una tabla propia.

---

### Gestión de Usuarios (R1–R8)

#### Tabla 3 — R1: Iniciar sesión / cerrar sesión / verificar sesión (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R1: Iniciar sesión / cerrar sesión / verificar sesión |
| Tipo | Seguridad |
| Prioridad | Alta |
| Descripción | Permite acceder al sistema mediante credenciales y mantener la sesión activa, con verificación de expiración. |
| Entrada | Usuario y contraseña. |
| Proceso | Verifica credenciales registradas y crea la sesión de usuario. |
| Salida | Acceso autorizado o rechazado. |

**Nota:** Permite acceder al sistema mediante credenciales y cerrar la sesión cuando el usuario lo requiera.

#### Tabla 4 — R2: Registrarse como cliente o encargado (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R2: Registrarse como cliente o encargado |
| Tipo | Registro |
| Prioridad | Alta |
| Descripción | Permite registrar nuevos usuarios en el sistema con rol cliente o encargado. |
| Entrada | Nombre, apellido, DNI, rol, usuario, contraseña. |
| Proceso | Valida y almacena la información ingresada. |
| Salida | Usuario registrado correctamente. |

**Nota:** Permite registrar nuevos usuarios en el sistema.

#### Tabla 5 — R3: Recuperar contraseña (solicitar token) (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R3: Recuperar contraseña |
| Tipo | Seguridad |
| Prioridad | Alta |
| Descripción | Permite a un usuario solicitar la recuperación de su contraseña mediante email, generando un token de restablecimiento con rate limiting. |
| Entrada | Email del usuario. |
| Proceso | Valida el email, aplica rate limiting y genera un token de recuperación. |
| Salida | Token de restablecimiento y tiempo de expiración. |

**Nota:** Permite solicitar recuperación de contraseña.

#### Tabla 6 — R4: Restablecer contraseña con token (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R4: Restablecer contraseña |
| Tipo | Seguridad |
| Prioridad | Alta |
| Descripción | Permite al usuario restablecer su contraseña usando un token de recuperación válido. |
| Entrada | Token, nueva contraseña. |
| Proceso | Valida el token y actualiza la contraseña del usuario. |
| Salida | Contraseña restablecida. |

**Nota:** Permite restablecer contraseña con token.

#### Tabla 7 — R5: Cambiar contraseña (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R5: Cambiar contraseña |
| Tipo | Seguridad |
| Prioridad | Alta |
| Descripción | Permite al usuario autenticado cambiar su contraseña proporcionando la contraseña actual y la nueva. |
| Entrada | Contraseña actual, nueva contraseña. |
| Proceso | Verifica la contraseña actual y actualiza con la nueva. |
| Salida | Contraseña cambiada. |

**Nota:** Permite cambiar la contraseña del usuario autenticado.

#### Tabla 8 — R6: Editar perfil de usuario (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R6: Editar perfil de usuario |
| Tipo | Actualización |
| Prioridad | Alta |
| Descripción | Permite al usuario modificar sus datos personales (nombre, email, contraseña). |
| Entrada | Datos modificados del usuario. |
| Proceso | Valida y actualiza el registro correspondiente. |
| Salida | Perfil de usuario actualizado. |

**Nota:** Permite actualizar la información del perfil del usuario.

#### Tabla 9 — R7: Gestionar usuarios (CRUD) (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R7: Gestionar usuarios (CRUD) |
| Tipo | Administración |
| Prioridad | Alta |
| Descripción | Permite al administrador crear, leer, actualizar y desactivar usuarios del sistema. |
| Entrada | Datos del usuario y rol asignado. |
| Proceso | Valida y ejecuta la operación de alta, modificación o baja. |
| Salida | Usuario dado de alta, modificado o desactivado. |

**Nota:** Permite administrar usuarios del sistema con roles diferenciados.

#### Tabla 10 — R8: Asignar roles y permisos (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R8: Asignar roles y permisos |
| Tipo | Administración |
| Prioridad | Alta |
| Descripción | Permite al administrador asignar roles (cliente, encargado, administrador) y permisos a cada usuario. |
| Entrada | Usuario y rol seleccionado. |
| Proceso | Asigna el rol y los permisos correspondientes al usuario. |
| Salida | Usuario con rol y permisos asignados. |

**Nota:** Permite asignar roles y permisos a los usuarios del sistema.

---

### Gestión de Reservas (R9–R14)

#### Tabla 11 — R9: Hacer reserva de mesa (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R9: Hacer reserva de mesa |
| Tipo | Reserva |
| Prioridad | Alta |
| Descripción | Permite al cliente reservar una mesa seleccionando fecha, horario y cantidad de comensales en días con disponibilidad abierta. |
| Entrada | Fecha, horario, cantidad de comensales, id_usuario, id_mesa. |
| Proceso | Valida disponibilidad y solapamiento de horarios, y crea la reserva. |
| Salida | Reserva registrada con estado pendiente. |

**Nota:** Permite hacer reservas de mesas en fechas con disponibilidad abierta (días verdes).

#### Tabla 12 — R10: Ver y cancelar reservas propias (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R10: Ver y cancelar reservas propias |
| Tipo | Consulta |
| Prioridad | Alta |
| Descripción | Permite al cliente consultar sus reservas y cancelar aquellas pendientes. |
| Entrada | id_usuario, id_reserva. |
| Proceso | Realiza la búsqueda de reservas del usuario y cambia el estado a cancelada. |
| Salida | Listado de reservas y confirmación de cancelación. |

**Nota:** Permite ver y cancelar reservas propias desde el perfil del cliente.

#### Tabla 13 — R11: Consultar detalle de reserva (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R11: Consultar detalle de reserva |
| Tipo | Consulta |
| Prioridad | Media |
| Descripción | Permite al dueño de la reserva o a un administrador/encargado consultar el detalle completo de una reserva (mesa, usuario, fechas, estado). |
| Entrada | id_reserva. |
| Proceso | Valida propiedad o rol y devuelve el detalle de la reserva. |
| Salida | Detalle de la reserva con datos del usuario y mesa. |

**Nota:** Permite consultar el detalle de una reserva.

#### Tabla 14 — R12: Abrir disponibilidad de mesas (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R12: Abrir disponibilidad de mesas |
| Tipo | Configuración |
| Prioridad | Alta |
| Descripción | Permite al encargado abrir disponibilidad de mesas para una fecha y rango horario, definiendo cuántas mesas están disponibles. |
| Entrada | Fecha, hora_inicio, hora_fin, mesas_disponibles. |
| Proceso | Valida y registra la disponibilidad de mesas para reservas. |
| Salida | Disponibilidad guardada o actualizada. |

**Nota:** Permite abrir disponibilidad de mesas para reservas.

#### Tabla 15 — R13: Cambiar estado de reservas (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R13: Cambiar estado de reservas |
| Tipo | Gestión |
| Prioridad | Alta |
| Descripción | Permite al encargado cambiar el estado de las reservas (pendiente, confirmada, cancelada, vencida, completada). |
| Entrada | id_reserva, nuevo estado. |
| Proceso | Valida y actualiza el estado de la reserva. |
| Salida | Reserva con estado actualizado. |

**Nota:** Permite cambiar estados de reservas.

#### Tabla 16 — R14: Bloquear mesas (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R14: Bloquear mesas |
| Tipo | Gestión |
| Prioridad | Alta |
| Descripción | Permite al encargado bloquear una mesa por un rango de fechas con un motivo (mantenimiento, evento, etc.). |
| Entrada | mesa_id, fecha_inicio, fecha_fin, motivo. |
| Proceso | Valida y registra el bloque de la mesa. |
| Salida | Bloque creado con identificador. |

**Nota:** Permite bloquear mesas por mantenimiento o eventos.

---

### Gestión de Mesas y Áreas (R15–R18)

#### Tabla 17 — R15: Listar áreas del restaurante (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R15: Listar áreas del restaurante |
| Tipo | Consulta |
| Prioridad | Media |
| Descripción | Permite consultar las áreas o salones del restaurante (ej. salón principal, terraza, salón privado). |
| Entrada | Ninguna. |
| Proceso | Lista todas las áreas registradas en el sistema. |
| Salida | Listado de áreas con nombre, descripción y estado. |

**Nota:** Permite listar áreas del restaurante.

#### Tabla 18 — R16: Gestionar áreas (CRUD) (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R16: Gestionar áreas |
| Tipo | Administración |
| Prioridad | Media |
| Descripción | Permite al administrador crear, editar y desactivar áreas del restaurante. |
| Entrada | Nombre, descripción, id_area (para editar/desactivar). |
| Proceso | Valida y almacena o modifica el área en la base de datos. |
| Salida | Área creada, editada o desactivada. |

**Nota:** Permite gestionar áreas del restaurante.

#### Tabla 19 — R17: Listar mesas por área (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R17: Listar mesas por área |
| Tipo | Consulta |
| Prioridad | Media |
| Descripción | Permite listar las mesas de un área específica, con opción de incluir inactivas. |
| Entrada | id_area, incluir_inactivas (opcional). |
| Proceso | Lista las mesas filtradas por área y estado de activación. |
| Salida | Listado de mesas con número, capacidad y estado. |

**Nota:** Permite listar mesas por área.

#### Tabla 20 — R18: Gestionar mesas (CRUD) (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R18: Gestionar mesas |
| Tipo | Administración |
| Prioridad | Media |
| Descripción | Permite al administrador crear, editar y desactivar mesas asignándolas a un área con número y capacidad. |
| Entrada | id_area, numero_mesa, capacidad, id_mesa (para editar/desactivar). |
| Proceso | Valida y almacena o modifica la mesa en la base de datos. |
| Salida | Mesa creada, editada o desactivada. |

**Nota:** Permite gestionar mesas del restaurante.

---

### Gestión de Horarios y Disponibilidad (R19–R21)

#### Tabla 21 — R19: Gestionar horarios de funcionamiento (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R19: Gestionar horarios de funcionamiento |
| Tipo | Configuración |
| Prioridad | Media |
| Descripción | Permite al encargado crear, editar y desactivar horarios de apertura y cierre por área y día de la semana. |
| Entrada | area_id, dia_semana, hora_apertura, hora_cierre. |
| Proceso | Valida y almacena o modifica el horario de funcionamiento. |
| Salida | Horario creado, editado o desactivado. |

**Nota:** Permite gestionar horarios de funcionamiento por área.

#### Tabla 22 — R20: Consultar disponibilidad de mesas por fecha (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R20: Consultar disponibilidad de mesas por fecha |
| Tipo | Consulta |
| Prioridad | Alta |
| Descripción | Permite al cliente consultar qué mesas están disponibles en una fecha dada, con horarios disponibles. |
| Entrada | Fecha (YYYY-MM-DD), id_mesa (opcional). |
| Proceso | Calcula la disponibilidad basada en disponibilidad_mesa y reservas existentes. |
| Salida | Horarios disponibles para la mesa/fecha consultada. |

**Nota:** Permite consultar disponibilidad de mesas para reservas.

#### Tabla 23 — R21: Gestionar bloques de mesa (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R21: Gestionar bloques de mesa |
| Tipo | Gestión |
| Prioridad | Media |
| Descripción | Permite al encargado crear y eliminar bloques de mesa para reservar temporalmente una mesa (mantenimiento, evento). |
| Entrada | mesa_id, fecha_inicio, fecha_fin, motivo, id_bloque (para eliminar). |
| Proceso | Valida y registra o elimina el bloque de la mesa. |
| Salida | Bloque creado o eliminado. |

**Nota:** Permite gestionar bloques de mesas.

---

### Gestión de Pedidos (R22–R29)

#### Tabla 24 — R22: Hacer pedido con seña 50 % y tipo de entrega (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R22: Hacer pedido con seña 50 % y tipo de entrega |
| Tipo | Pedido |
| Prioridad | Alta |
| Descripción | Permite al cliente registrar un pedido seleccionando productos y cantidades, con tipo de entrega y seña del 50 %. |
| Entrada | id_usuario, id_mesa (nullable), productos, cantidades, tipo_pedido, seña, direccion_domicilio. |
| Proceso | Valida stock y límite de venta, descuenta stock, registra el pedido y la seña. |
| Salida | Pedido registrado con estado pendiente y seña registrada. |

**Nota:** Permite hacer pedidos con seña del 50 % y tipos de entrega (con reserva, para llevar, domicilio).

#### Tabla 25 — R23: Ver mis pedidos y estado de seña (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R23: Ver mis pedidos y estado de seña |
| Tipo | Consulta |
| Prioridad | Alta |
| Descripción | Permite al cliente consultar sus propios pedidos y el estado de la seña asociada. |
| Entrada | id_usuario. |
| Proceso | Realiza la búsqueda de pedidos del usuario y su estado de seña. |
| Salida | Listado de pedidos con estado de seña (pendiente/pagada/devuelta). |

**Nota:** Permite ver mis pedidos y el estado de la seña desde el perfil del cliente.

#### Tabla 26 — R24: Consultar detalle de pedido con ítems (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R24: Consultar detalle de pedido |
| Tipo | Consulta |
| Prioridad | Media |
| Descripción | Permite al dueño del pedido o a un administrador/encargado consultar el detalle completo de un pedido incluyendo sus ítems, categorías y subtotales. |
| Entrada | id_pedido. |
| Proceso | Valida propiedad o rol y devuelve el pedido con sus líneas de detalle. |
| Salida | Detalle del pedido con ítems y categorías. |

**Nota:** Permite consultar el detalle de un pedido con sus ítems.

#### Tabla 27 — R25: Cambiar estado de pedidos (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R25: Cambiar estado de pedidos |
| Tipo | Gestión |
| Prioridad | Alta |
| Descripción | Permite al encargado (y administrador) cambiar el estado de los pedidos (pendiente, en_preparación, listo, entregado, cancelado). |
| Entrada | id_pedido, nuevo estado. |
| Proceso | Valida y actualiza el estado del pedido; registra en historial. |
| Salida | Pedido con estado actualizado. |

**Nota:** Permite cambiar el estado de los pedidos entre la cocina, salón y caja.

#### Tabla 28 — R26: Asignar mesero/responsable a un pedido (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R26: Asignar mesero/responsable a un pedido |
| Tipo | Gestión |
| Prioridad | Media |
| Descripción | Permite al encargado o administrador asignar un mesero o responsable a un pedido específico. |
| Entrada | id_pedido, id_mesero. |
| Proceso | Valida que el pedido exista y que el mesero sea activo con rol válido. |
| Salida | Pedido con mesero asignado. |

**Nota:** Permite asignar mesero o responsable a un pedido.

#### Tabla 29 — R27: Validar stock y límite de venta al crear pedido (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R27: Validar stock y límite de venta al crear pedido |
| Tipo | Validación |
| Prioridad | Alta |
| Descripción | Valida que exista stock suficiente y que no se supere el límite de venta por plato antes de confirmar el pedido. |
| Entrada | id_producto, cantidad solicitada, stock_actual, límite de venta. |
| Proceso | Compara cantidad solicitada contra stock y límite de venta; rechaza si no hay disponibilidad. |
| Salida | Pedido autorizado o rechazado por falta de stock/límite. |

**Nota:** Valida stock y límite de venta antes de crear pedidos.

#### Tabla 30 — R28: Descontar stock automáticamente al confirmar pedido (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R28: Descontar stock automáticamente |
| Tipo | Proceso |
| Prioridad | Alta |
| Descripción | Al confirmar un pedido, el sistema descuenta automáticamente las unidades vendidas del stock de cada producto. |
| Entrada | id_pedido, detalle_pedidos (id_producto, cantidad). |
| Proceso | Valida disponibilidad y reduce stock_actual de cada producto por la cantidad vendida. |
| Salida | Stock actualizado tras la confirmación del pedido. |

**Nota:** Descuenta automáticamente el stock al confirmar pedidos.

#### Tabla 31 — R29: Actualizar estado de seña (pendiente/pagada/devuelta) (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R29: Actualizar estado de seña |
| Tipo | Gestión |
| Prioridad | Alta |
| Descripción | Permite al encargado gestionar el estado de la seña de los pedidos anticipados (pendiente, pagada, devuelta). |
| Entrada | id_pedido, estado_seña. |
| Proceso | Valida y actualiza el estado de la seña del pedido. |
| Salida | Seña con estado actualizado. |

**Nota:** Permite gestionar el estado de la seña (pendiente/pagada/devuelta).

---

### Gestión de Productos y Menú (R30–R35)

#### Tabla 32 — R30: Ver carta de productos con filtros por categoría (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R30: Ver carta de productos con filtros por categoría |
| Tipo | Consulta |
| Prioridad | Alta |
| Descripción | Permite consultar la carta de productos con filtros por categoría (entradas, principales, postres, bebidas). |
| Entrada | Categoría (filtro opcional). |
| Proceso | Lista los productos activos, opcionalmente filtrados por categoría. |
| Salida | Listado de productos de la carta. |

**Nota:** Permite ver la carta de productos con filtros por categoría.

#### Tabla 33 — R31: Ver menú semanal publicado (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R31: Ver menú semanal publicado |
| Tipo | Consulta |
| Prioridad | Alta |
| Descripción | Permite al cliente consultar el menú semanal publicado con los platos disponibles de la semana. |
| Entrada | id_menu_semanal, semana_inicio, semana_fin. |
| Proceso | Busca el menú semanal activo y lista sus platos. |
| Salida | Menú semanal con platos disponibles y precios. |

**Nota:** Permite ver el menú semanal publicado por el encargado.

#### Tabla 34 — R32: Gestionar productos (CRUD) (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R32: Gestionar productos |
| Tipo | Registro |
| Prioridad | Alta |
| Descripción | Permite al encargado crear, editar y activar/desactivar productos del catálogo (nombre, categoría, precio). |
| Entrada | id_producto, nombre, categoría, precio, activo. |
| Proceso | Valida y crea, edita o cambia el estado de activación del producto. |
| Salida | Producto creado, editado o activado/desactivado. |

**Nota:** Permite crear, editar y activar/desactivar productos del catálogo.

#### Tabla 35 — R33: Gestionar stock y límite de venta por producto (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R33: Gestionar stock y límite de venta |
| Tipo | Gestión |
| Prioridad | Alta |
| Descripción | Permite al encargado administrar el stock actual y establecer el límite máximo de venta por producto. |
| Entrada | id_producto, stock_actual, límite de venta. |
| Proceso | Valida y actualiza el registro de stock del producto. |
| Salida | Stock y límite de venta actualizados. |

**Nota:** Permite gestionar stock y límite de venta por producto.

#### Tabla 36 — R34: Gestionar menú semanal (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R34: Gestionar menú semanal |
| Tipo | Gestión |
| Prioridad | Alta |
| Descripción | Permite al encargado gestionar el menú semanal: alta, modificación y desactivación de platos, precios y disponibilidad. |
| Entrada | id_menu_semanal, platos, precios, disponibilidad, activo. |
| Proceso | Valida y publica o actualiza el menú semanal. |
| Salida | Menú semanal publicado o actualizado. |

**Nota:** Permite gestionar el menú semanal (alta, modificación y desactivación de platos).

#### Tabla 37 — R35: Consultar stock bajo / alertas (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R35: Consultar stock bajo / alertas |
| Tipo | Consulta |
| Prioridad | Alta |
| Descripción | Permite consultar los productos con stock bajo y recibir alertas de productos que requieren reposición. |
| Entrada | Umbral de stock bajo (opcional). |
| Proceso | Compara stock actual contra el umbral y lista productos por debajo. |
| Salida | Listado de productos con stock bajo y alertas activas. |

**Nota:** Permite consultar stock bajo y recibir alertas de reposición.

---

### Gestión de Delivery (R36–R39)

#### Tabla 38 — R36: Gestionar zonas de delivery (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R36: Gestionar zonas de delivery |
| Tipo | Configuración |
| Prioridad | Media |
| Descripción | Permite al encargado crear, editar y desactivar zonas de entrega a domicilio con costo de envío y tiempo estimado. |
| Entrada | Nombre, costo_envio, tiempo_estimado_minutos, cobertura, id_zona (para editar/desactivar). |
| Proceso | Valida y almacena o modifica la zona de delivery. |
| Salida | Zona de delivery creada, editada o desactivada. |

**Nota:** Permite gestionar zonas de delivery.

#### Tabla 39 — R37: Gestionar repartidores (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R37: Gestionar repartidores |
| Tipo | Gestión |
| Prioridad | Media |
| Descripción | Permite al encargado crear, editar y desactivar repartidores para pedidos a domicilio. |
| Entrada | Nombre, telefono, id_usuario, id_repartidor, activo (para editar/desactivar). |
| Proceso | Valida y almacena o modifica el repartidor. |
| Salida | Repartidor creado, editado o desactivado. |

**Nota:** Permite gestionar repartidores de delivery.

#### Tabla 40 — R38: Actualizar estado de entrega de delivery (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R38: Actualizar estado de entrega de delivery |
| Tipo | Gestión |
| Prioridad | Media |
| Descripción | Permite al encargado actualizar el estado de entrega de un pedido a domicilio (pendiente, en_camino, entregado, fallido). |
| Entrada | id_pedido, estado_entrega. |
| Proceso | Valida el estado y actualiza el registro del pedido; registra en historial. |
| Salida | Estado de entrega actualizado. |

**Nota:** Permite actualizar el estado de entrega de pedidos a domicilio.

#### Tabla 41 — R39: Cierre de caja diario (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R39: Cierre de caja diario |
| Tipo | Gestión |
| Prioridad | Media |
| Descripción | Permite al encargado abrir y cerrar la caja diaria registrando montos por medio de pago (efectivo, tarjeta, transferencia) y calculando la diferencia. |
| Entrada | Fecha, monto_apertura, monto_cierre, efectivo, tarjeta, transferencia, observaciones. |
| Proceso | Registra la apertura y cierre de caja, calcula la diferencia. |
| Salida | Caja abierta o cerrada con diferencia calculada. |

**Nota:** Permite gestionar el cierre de caja diario.

---

### Gestión de Reportes (R40–R43)

#### Tabla 42 — R40: Reportes de ventas (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R40: Reportes de ventas |
| Tipo | Reporte |
| Prioridad | Alta |
| Descripción | Permite al administrador consultar reportes de ventas totales por período, con filtro de rango de fechas. |
| Entrada | Fecha desde, fecha hasta (opcional). |
| Proceso | Agrega y calcula métricas de ventas desde la base de datos. |
| Salida | Reportes de ventas por período. |

**Nota:** Permite ver reportes de ventas.

#### Tabla 43 — R41: Productos más vendidos (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R41: Productos más vendidos |
| Tipo | Reporte |
| Prioridad | Media |
| Descripción | Permite consultar el ranking de productos más vendidos basado en los detalles de pedidos no cancelados. |
| Entrada | Ninguna. |
| Proceso | Agrupa y suma cantidades vendidas por producto. |
| Salida | Listado de productos más vendidos con cantidades. |

**Nota:** Permite consultar productos más vendidos.

#### Tabla 44 — R42: Reservas por día (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R42: Reservas por día |
| Tipo | Reporte |
| Prioridad | Media |
| Descripción | Permite al administrador consultar la cantidad de reservas agrupadas por día. |
| Entrada | Fecha desde, fecha hasta (opcional). |
| Proceso | Agrupa y cuenta reservas por fecha. |
| Salida | Reportes de reservas por día. |

**Nota:** Permite ver reportes de reservas por día.

#### Tabla 45 — R43: Exportar reportes a CSV (Prioridad: Alta)

| Campo | Detalle |
|---|---|
| Nombre | R43: Exportar reportes a CSV |
| Tipo | Reporte |
| Prioridad | Alta |
| Descripción | Permite al administrador exportar el reporte de ventas filtrado por rango de fechas a formato CSV descargable. |
| Entrada | Fecha desde, fecha hasta, formato. |
| Proceso | Genera el reporte y lo envía como archivo CSV descargable. |
| Salida | Archivo CSV con el reporte de ventas. |

**Nota:** Permite exportar reportes de ventas a CSV.

---

### Gestión de Fidelidad y Preferencias (R44–R48)

#### Tabla 46 — R44: Gestionar cupones de descuento (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R44: Gestionar cupones de descuento |
| Tipo | Registro |
| Prioridad | Media |
| Descripción | Permite al encargado crear, editar y desactivar cupones de descuento (porcentaje o monto fijo) con fechas de vigencia y límites de uso. |
| Entrada | Código, tipo_descuento, valor_descuento, monto_minimo, fecha_inicio, fecha_fin, uso_maximo, id_cupon (para editar/desactivar). |
| Proceso | Valida y almacena o modifica el cupón de descuento. |
| Salida | Cupón creado, editado o desactivado. |

**Nota:** Permite gestionar cupones de descuento.

#### Tabla 47 — R45: Calificar platos (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R45: Calificar platos |
| Tipo | Registro |
| Prioridad | Media |
| Descripción | Permite al cliente autenticado calificar un producto con una puntuación del 1 al 5 y un comentario opcional. |
| Entrada | id_producto, puntuación (1-5), comentario. |
| Proceso | Valida la puntuación y guarda o actualiza la calificación. |
| Salida | Calificación guardada. |

**Nota:** Permite calificar platos con puntuación del 1 al 5.

#### Tabla 48 — R46: Gestionar preferencias y alergias del cliente (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R46: Gestionar preferencias y alergias |
| Tipo | Registro |
| Prioridad | Media |
| Descripción | Permite al cliente registrar, consultar y eliminar sus preferencias, alergias y restricciones alimentarias. |
| Entrada | Tipo (alergia, preferencia, restriccion), descripción, id_preferencia (para eliminar). |
| Proceso | Valida el tipo y almacena o elimina la preferencia del cliente. |
| Salida | Preferencia registrada, consultada o eliminada. |

**Nota:** Permite gestionar preferencias y alergias del cliente.

#### Tabla 49 — R47: Gestionar puntos de fidelidad (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R47: Gestionar puntos de fidelidad |
| Tipo | Gestión |
| Prioridad | Media |
| Descripción | Permite al cliente consultar sus puntos de fidelidad y al encargado/administrador sumar, restar o establecer puntos. |
| Entrada | id_usuario, puntos, tipo (sumar/restar/set). |
| Proceso | Actualiza los puntos de fidelidad del cliente. |
| Salida | Puntos actualizados. |

**Nota:** Permite gestionar puntos de fidelidad de clientes.

#### Tabla 50 — R48: Gestionar platos favoritos (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R48: Gestionar platos favoritos |
| Tipo | Registro |
| Prioridad | Media |
| Descripción | Permite al cliente agregar, consultar y remover platos de su lista de favoritos. |
| Entrada | id_producto, id_usuario (autenticado). |
| Proceso | Inserta, lista o elimina el producto de favoritos del cliente. |
| Salida | Plato agregado, listado o removido de favoritos. |

**Nota:** Permite gestionar platos favoritos del cliente.

#### Tabla 51 — R49: Gestionar reservas recurrentes (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R49: Gestionar reservas recurrentes |
| Tipo | Registro |
| Prioridad | Media |
| Descripción | Permite al cliente crear, consultar y gestionar reservas recurrentes (misma mesa, día y hora cada semana). |
| Entrada | id_mesa, dia_semana, hora_inicio, hora_fin, cantidad_personas, id_reserva_recurrente (para gestionar). |
| Proceso | Valida y registra o gestiona la reserva recurrente. |
| Salida | Reserva recurrente creada o gestionada. |

**Nota:** Permite gestionar reservas recurrentes del cliente.

#### Tabla 52 — R50: Subir imágenes de productos (Prioridad: Media)

| Campo | Detalle |
|---|---|
| Nombre | R50: Subir imágenes de productos |
| Tipo | Registro |
| Prioridad | Media |
| Descripción | Permite al encargado o administrador subir una imagen de producto al servidor y obtener su URL pública. |
| Entrada | Archivo de imagen. |
| Proceso | Valida, guarda el archivo y genera una URL única. |
| Salida | URL de la imagen subida. |

**Nota:** Permite subir imágenes de productos.

---

## Requerimientos No Funcionales

**Seguridad:** Contraseñas con hash bcrypt; sesiones cookie HttpOnly; rate limiting en login/registro.  
**Roles:** Exactamente 3 roles (cliente, encargado, administrador).  
**Base de datos:** MariaDB/MySQL, migraciones versionadas (001→007), normalización 1FN/2FN/3FN.  
**Backend:** PHP, front controller (`public/index.php`), controladores en `src/Controller/`.  
**Frontend:** HTML/CSS/JS vanilla, páginas en `public/`, API en `/api/`.  
**Entorno:** XAMPP local o Docker Compose.  
**Rendimiento:** Tiempo de respuesta inferior a 3 segundos para cargar menú, calendario y pedidos.  
**Usabilidad:** Interfaz clara e intuitiva, navegable desde computadora y celular; mensajes en español.  
**Disponibilidad:** Sistema disponible durante el horario de atención del restaurante; copias de seguridad periódicas.  
**Compatibilidad:** Compatible con navegadores web modernos y diseño responsivo.

---

## Alcance del Sistema

### Alcance General

El Sistema de Gestión de Restaurante "Green en la Casa Blanca" tiene como finalidad mejorar la administración de reservas, pedidos, stock, menú semanal, señas y usuarios mediante una plataforma web que permita registrar, consultar y controlar la información relacionada con los recursos disponibles en el restaurante.

El sistema estará destinado al uso de administradores, encargados y clientes autorizados, proporcionando mecanismos para gestionar reservas, pedidos, stock, menú semanal, señas, historial de movimientos y generación de reportes.

### Funciones Incluidas en el Sistema

#### Gestión de Usuarios

- Registro de usuarios  
- Inicio de sesión  
- Recuperación de contraseña  
- Edición de perfil  
- Gestión de usuarios (CRUD)  
- Administración de roles y permisos  

#### Gestión de Reservas

- Registro de reservas  
- Consulta de reservas activas  
- Cancelación de reservas propias  
- Cambio de estado de reservas  
- Control de disponibilidad de mesas  
- Bloqueo de mesas por mantenimiento o eventos  

#### Gestión de Mesas y Áreas

- Listado de áreas y mesas  
- Gestión administrativa de áreas y mesas  

#### Gestión de Horarios y Disponibilidad

- Gestión de horarios de funcionamiento por área  
- Consulta de disponibilidad de mesas por fecha  
- Gestión de bloques de mesa  

#### Gestión de Pedidos

- Registro de pedidos  
- Consulta de pedidos propios  
- Cambio de estado de pedidos  
- Consulta de detalle de pedidos  
- Asignación de mesero/responsable  
- Tipos de pedido: con reserva, para llevar, domicilio  

#### Gestión de Señas

- Registro de seña del 50 %  
- Gestión de estado de seña (pendiente/pagada/devuelta)  
- Consulta de estado de seña  

#### Gestión de Productos y Menú

- Publicación del menú semanal  
- Consulta de carta de productos  
- Gestión de menú semanal  
- Creación, edición y activación/desactivación de productos  

#### Gestión de Stock

- Gestión de stock y límite de venta por producto  
- Consulta de stock en tiempo real  
- Alerta de stock bajo  
- Descuento automático de stock al confirmar pedidos  

#### Gestión de Delivery

- Gestión de zonas de delivery  
- Gestión de repartidores  
- Actualización de estado de entrega  
- Cierre de caja diario  

#### Gestión de Reportes

- Reportes de ventas  
- Reportes de productos más vendidos  
- Reportes de reservas por día  
- Filtro de reportes por rango de fechas  
- Exportación e impresión de reportes  

#### Gestión de Fidelidad y Preferencias

- Gestión de cupones de descuento  
- Calificación de platos  
- Gestión de preferencias y alergias  
- Gestión de puntos de fidelidad  
- Gestión de platos favoritos  
- Gestión de reservas recurrentes  
- Subida de imágenes de productos  

#### Gestión de Historial

- Registro automático de movimientos  
- Consulta de historial general  
- Consulta de historial filtrado  

---

## Despliegue del Programa

- Contratación de un hosting en la nube (por ejemplo, servicio con soporte PHP/MySQL o Node.js según el stack final).  
- Despliegue (subida) de la base de datos a un hosting propio, con configuración de backups automáticos.  
- Publicación del frontend y backend con dominio institucional o subdominio.  
- Entorno local: XAMPP (Apache + PHP + MariaDB) o Docker Compose.

---

## Limitaciones del Sistema

El sistema no incluirá:

- Pagos en línea o integración con pasarelas de pago reales (la seña se gestiona como estado pendiente/pagada/devuelta).  
- Facturación electrónica vinculada a AFIP u otro ente fiscal.  
- Integración con impresoras de cocina o comandas físicas automáticas.  
- Gestión de proveedores y compras de insumos.  
- Módulo de delivery o envíos a domicilio propio (el domicilio se gestiona como tipo de pedido con dirección).  
- Aplicación móvil nativa para Android o iOS (el acceso será vía navegador web).  
- Control mediante códigos QR o RFID.  
- Seguimiento geográfico (GPS) de pedidos.  
- Impresión directa de documentación integrada (se exporta a PDF/Excel para impresión externa).  
- Múltiples sucursales en esta versión; opera para un solo local.

---

## Especificaciones

- El sistema no comprará automáticamente los productos ni repondrá automáticamente el stock.  
- El sistema no gestionará ni hará los cálculos de presupuesto utilizados para los insumos del restaurante.  
- El sistema no gestionará nada académico como notas; se limitará solamente a facilitar la gestión del restaurante.  
- El sistema no controlará asistencias o inasistencias de empleados; solamente se limitará a controlar el estado de los pedidos.  
- El sistema requiere computadoras o dispositivos con acceso a internet o red local para su funcionamiento.  
- La información mostrada por el sistema dependerá de que los usuarios registren correctamente reservas, pedidos y movimientos.  
- Solo podrán utilizar el sistema los usuarios que posean credenciales válidas y permisos asignados.  
- Problemas de hardware, software, energía eléctrica o conectividad podrán afectar temporalmente el acceso al sistema.  
- Será necesario registrar previamente todas las mesas, productos, categorías, stock y usuarios para comenzar a utilizar el sistema.  
- Los responsables del restaurante y administradores deberán conocer el funcionamiento básico del sistema para operarlo correctamente.  
- El sistema funcionará de manera independiente y no se integrará con plataformas de terceros u otros sistemas institucionales.  
- El acceso se realizará mediante navegador web, sin aplicaciones específicas para Android o iOS.

---

## Entregables del Proyecto

- Informe técnico (presenta documento) con análisis, requerimientos y diseño.  
- Presentación del proyecto.  
- Manual de usuario para clientes, encargados y administradores.  
- Manual técnico de instalación y mantenimiento del sistema.  
- Diagrama de casos de uso y diagrama de Gantt.  
- Diagrama entidad-relación y diccionario de datos.  
- Código fuente del sistema de gestión desarrollado.  
- Informe final del proyecto integrador.

---

*Fin del documento. Sistema completo listo para pruebas (aplicar migraciones 001→007).*
