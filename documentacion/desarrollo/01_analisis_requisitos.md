# 01. Analisis de requisitos — Sistema de gestion de un restaurante

> **Estado:** borrador de planificacion (etapa 1 - sin programar).  
> **Proyecto:** Sistema de gestion de un restaurante (reservas de mesas, pedidos y control de estado).  
> **Fuente principal:** PDF "proyecto integrador cuatrimestral 2026.pdf" (EPET Nº 7 "Vicente Arcadio Salemi").  
> **Idioma:** espanol.

---

## 1. Resumen del PDF consultado

| Dato | Valor |
|------|-------|
| Institucion | EPET Nº 7 "VICENTE ARCADIO SALEMI" |
| Materia | Laboratorio de Desarrollo de Aplicaciones III y Practicas Pre Profesionalizantes |
| Curso | 6 IV CST |
| Profesor(es) | VERAS PEDRO - YANINA MARTINEZ |
| Ciclo lectivo | 2026 |
| Fecha de elevo | 23 de octubre de 2026 |
| Fecha de entrega con defensa oral | 19 de noviembre de 2026 |
| Recuperatorio | 27 de noviembre de 2026 |
| Perfil tecnico | Orientacion en Programacion |
| Modalidad de trabajo | Individual o grupos de hasta 2 integrantes |

### Contenido del PDF (etapas requeridas por el proyecto)
1. Analisis de la problematica (descripcion, identificacion, usuarios).
2. Primera etapa (entrevista, mundo del problema, matriz FODA, objetivos general y especificos, especificacion de requerimientos funcionales y no funcionales, alcance, limitaciones, entregables, formato APA 7ma edicion).
3. Analisis y diseno (diagrama de Gantt, diagrama de casos de uso, diagrama entidad-relacion, diccionario de datos, diagrama de clases).
4. Desarrollo (diseno de interfaz, base de datos, programacion, integracion aplicacion-BD, funcionalidades definidas).
5. Sistema funcional (sistema funcional que demuestre las funcionalidades principales, pruebas y correccion de errores).
6. Plan de mantenimiento.
7. Plan de capacitacion.
8. Presentacion final (documentacion completa, diagramas, sistema funcional, exposicion y demostracion).

### Criterios de evaluacion
1. Documentacion completa, con claridad, coherencia y cohesión en la expresion escrita.
2. Aplicacion del pensamiento logico, resolucion de problemas y conocimientos tecnicos en diseno y desarrollo.
3. Correspondencia entre la documentacion, los diagramas y el funcionamiento del prototipo presentado.
4. Expresion oral, uso del vocabulario tecnico y capacidad para explicar y fundamentar las decisiones tomadas.
5. Responsabilidad, autonomia, trabajo colaborativo, cumplimiento de tareas y plazos, respeto.

**Condicion de aprobacion:** la documentacion debe estar aprobada y coincidir con los diagramas y el prototipo funcional. Cada estudiante debe demostrar individualmente que comprende el proyecto y puede explicar las decisiones tomadas. Si no logra fundamentar el trabajo, queda desaprobado.

---

## 2. Actividad 2 — Sistema de gestion de un restaurante

**Nombre:** Sistema de gestion de un restaurante.

**Problematica textual del PDF:** "El restaurante presenta dificultades para organizar las reservas de mesas, registrar pedidos y controlar su estado, lo que puede generar demoras y errores en la atencion."

**Interpretacion del alcance real (según el PDF):**
- El dominio es un restaurante con una o varias mesas distribuidas en areas/salones.
- El problema central tiene tres aristas:
  1. **Reservas de mesas:** registros manuales que pueden provocar superposicion de horarios y dificultades para consultar disponibilidad.
  2. **Registro de pedidos:** no hay control sistematizado de los pedidos.
  3. **Control de estado de pedidos:** desconocimiento del estado de cada pedido (pendiente, en preparacion, listo, entregado), generando demoras y errores en la atencion.
- El sistema debe reemplazar el registro manual por un sistema informatico que permita consultar disponibilidad de mesas, crear reservas, registrar pedidos y controlar su estado.

**Qué NO dice el PDF (no se asume como requisito):**
- No menciona pagos en linea, facturacion electronica, reseñas, descuentos, geolocalizacion, delivery, notificaciones por email/SMS, subida de archivos, autenticacion con redes sociales, ni API para apps moviles.
- No especifica tecnologías (no nombra PHP, MySQL, Docker, JWT, bcrypt).
- No especifica roles de usuario detallados (solo se infiere que hay quien reserva/cliente, quien atiende/mesero, quien cocina/cocinero y quien administra).
- No especifica cantidades de mesas, areas, horarios de funcionamiento ni duraciones de turno.

**Decisión:** Todo lo tecnológico y de detalle funcional proviene de la instrucción de planificacion (secciones 2 a 19 del encargo). El PDF aporta el **dominio y la problemática**. Las mejoras tecnicas propuestas se identifican claramente como tales (no como requisitos del PDF).

---

## 3. Requisitos funcionales (RF)

Se agrupan por modulo. Cada RF trae identificador `RF-XX`.

### Módulo: Autenticación y sesiones
| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-01 | Registro de usuario | Permitir que una persona se registre como usuario del sistema con datos personales y credenciales. | Usuario no autenticado |
| RF-02 | Inicio de sesion | Permitir al usuario autenticarse con credenciales y obtener una sesion activa. | Usuario registrado |
| RF-03 | Cierre de sesion | Permitir finalizar la sesion activa y invalidar la cookie de sesion. | Usuario autenticado |
| RF-04 | Recuperacion de contrasena | Permitir restablecer la contrasena olvidada mediante un proceso seguro (token de recuperacion temporal). | Usuario registrado |

### Módulo: Usuarios y roles
| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-05 | Gestion de roles | Definir roles del sistema (cliente, mesero, cocinero, administrador) con permisos asociados. | Administrador |
| RF-06 | Gestion de usuarios | Crear, editar, desactivar y listar usuarios del sistema. | Administrador |

### Módulo: Areas y mesas
| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-07 | Consulta de areas | Listar areas/salones del restaurante con sus caracteristicas. | Cualquier usuario (incluso no autenticado) |
| RF-08 | Consulta de mesas | Listar mesas de un area con sus características (numero, capacidad). | Cualquier usuario |
| RF-09 | Gestion de areas y mesas | Crear, editar, desactivar areas y mesas. | Administrador |

### Módulo: Disponibilidad y reservas de mesas
| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-10 | Consulta de disponibilidad de mesas | Consultar horarios disponibles de una mesa para una fecha dada, considerando reservas existentes, bloqueos y horarios de funcionamiento. | Cualquier usuario |
| RF-11 | Seleccion de fecha y horario | Elegir fecha y turno disponible para reservar una mesa. | Usuario autenticado |
| RF-12 | Creacion de reserva | Crear una reserva confirmada para una mesa, fecha y horario seleccionados. | Usuario autenticado |
| RF-13 | Prevencion de reservas superpuestas | Impedir que una mesa tenga dos reservas confirmadas que se solapen en el tiempo. Servidor + base de datos. | Sistema (servidor) |
| RF-14 | Consulta de reservas propias | Ver el historial y estado de las reservas del usuario autenticado. | Usuario autenticado |
| RF-15 | Cancelacion de reserva | Cancelar una reserva propia, liberando la mesa. | Usuario autenticado |
| RF-16 | Gestion administrativa de reservas | Listar todas las reservas, ver reservas de otros usuarios, cambiar estado, cancelar reservas ajenas. | Administrador |
| RF-17 | Gestion de estados de reserva | Reservas pueden estar en estados: pendiente, confirmada, cancelada, vencida, completada. | Sistema / Administrador |
| RF-18 | Gestion de horarios de funcionamiento y duracion de turno | Definir horarios de apertura/cierre de cada area/mesa y duracion estándar de los turnos. | Administrador |
| RF-19 | Periodos de disponibilidad | Definir periodos en los que una mesa esta disponible o bloqueada (mantenimiento, cerrado). | Administrador |

### Módulo: Pedidos y control de estado
| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-20 | Registro de pedido | Registrar un pedido asociado a una mesa y un mesero, con productos y cantidades. | Mesero |
| RF-21 | Consulta de pedidos | Ver pedidos propios (mesero) o todos (administrador, cocinero). | Mesero, Cocinero, Administrador |
| RF-22 | Cambio de estado de pedido | Cambiar el estado de un pedido: pendiente, en_preparacion, listo, entregado, cancelado. | Mesero, Cocinero, Administrador |
| RF-23 | Gestion de productos | Crear, editar, desactivar productos del menu (nombre, categoria, precio). | Administrador |
| RF-24 | Control de estado de mesa | Una mesa con pedido activo o reserva confirmada se considera ocupada; al finalizar se libera. | Sistema / Mesero |

### Módulo: Validación y seguridad
| ID | Nombre | Descripcion | Actor |
|----|--------|-------------|-------|
| RF-25 | Validacion de reglas de negocio en servidor | Toda regla de negocio se valida en el servidor (PHP), nunca solo en el cliente. | Sistema |
| RF-26 | Proteccion de reservas simultaneas | Evitar reservas duplicadas bajo concurrencia (transacciones / bloqueo). | Sistema |
| RF-27 | Limitacion de solicitudes (rate limiting) | Aplicar Token Bucket para limitar peticiones por cliente/endpoint. | Sistema |
| RF-28 | Control de acceso por rol | El servidor verifica rol y permisos en cada operacion protegida. | Sistema |

**Notificaciones:** el PDF no las menciona. Se propone como **mejora opcional** y NO se incluye como requisito base.

**Pagos/facturacion:** el PDF no los menciona. NO se incluyen como requisito base. Si se requieren, serian una mejora aprobada por separado.

---

## 4. Requisitos no funcionales (RNF)

| ID | Nombre | Descripcion |
|----|--------|-------------|
| RNF-01 | Portabilidad de entorno | La misma aplicacion debe ejecutarse en XAMPP, Docker Compose y hosting remoto sin cambios de logica. |
| RNF-02 | Stack tecnologico | PHP para backend/API, JavaScript para cliente, MySQL/MariaDB para BD, HTML/CSS para presentacion. Sin frameworks innecesarios. |
| RNF-03 | POO | Logica de backend organizada con Programacion Orientada a Objetos. |
| RNF-04 | Seguridad de contrasenas | Almacenamiento con bcrypt (`password_hash` / `password_verify`). Nunca SHA-256 para contrasenas. |
| RNF-05 | Sesiones seguras | Cookies de sesion `HttpOnly`, `Secure` (HTTPS), `SameSite`. |
| RNF-06 | Consulta segura | Uso de consultas preparadas (PDO) para evitar inyeccion SQL. |
| RNF-07 | Versionamiento | Git y GitHub con ramas `main` y `develop`. |
| RNF-08 | Accesibilidad | Objetivo de referencia WCAG 2.2 nivel AA (según alcance del proyecto escolar). |
| RNF-09 | Diseno adaptable | Funcionar en computadoras, tablets y celulares (responsive). |
| RNF-10 | Manejo de errores | Respuestas HTTP estandar, mensajes sin revelar rutas internas ni consultas SQL. |
| RNF-11 | Configuracion por entorno | Variables de entorno; secretos nunca en el repositorio. |
| RNF-12 | Migraciones | Esquema de BD versionado y reproducible en los tres entornos. |

---

## 5. Reglas de negocio (RB)

| ID | Regla | Descripcion |
|----|-------|-------------|
| RB-01 | Una mesa, una reserva confirmada por horario | Una mesa no puede tener dos reservas confirmadas que se superpongan en el mismo intervalo de tiempo. |
| RB-02 | Disponibilidad derivada | La disponibilidad se calcula a partir de horarios de funcionamiento, reservas existentes, bloqueos y reglas aplicables. No se almacena disponibilidad precalculada como dato fuente. |
| RB-03 | Validacion en servidor | Toda operacion de reserva y pedido se valida en servidor y en BD, independientemente de validaciones del cliente. |
| RB-04 | Concurrencia protegida | La creacion de reserva usa transaccion y bloqueo para evitar doble reserva ante peticiones simultaneas. |
| RB-05 | Estados de reserva | Estados validos: pendiente, confirmada, cancelada, vencida, completada. Transiciones definidas. |
| RB-06 | Cancelacion libera mesa | Al cancelar una reserva, la mesa queda disponible para otros usuarios. |
| RB-07 | Acceso a propios recursos | Un cliente solo puede ver/cancelar sus propias reservas. Un mesero solo puede ver/gestionar sus propios pedidos. Un administrador puede ver/gestionar todo. |
| RB-08 | Horarios dentro de funcionamiento | Una reserva solo puede crearse dentro del horario de funcionamiento de la mesa/area y en turnos validos. |
| RB-09 | Contrasena segura | Las contrasenas se hashean con bcrypt. No se almacenan en texto plano ni con SHA-256. |
| RB-10 | Sesion expirada | Las sesiones tienen expiracion; tras ella el usuario debe autenticarse de nuevo. |
| RB-11 | Pedido asociado a mesa y mesero | Todo pedido debe pertenecer a una mesa existente y a un mesero autenticado. |
| RB-12 | Estados de pedido | Estados validos: pendiente, en_preparacion, listo, entregado, cancelado. Transiciones definidas (p. ej. pendiente → en_preparación → listo → entregado). |

---

## 6. Restricciones

| ID | Restriccion | Origen |
|----|-------------|--------|
| RES-01 | Sin frameworks innecesarios | Instruccion de planificacion (seccion 3). |
| RES-02 | No pagos en linea ni facturacion obligatoria | No contemplados en el PDF ni requeridos. |
| RES-03 | No notificaciones obligatorias | No contempladas en el PDF; opcionales y justificadas si se agregan. |
| RES-04 | SHA-256 solo si hay necesidad tecnica justificada | Instruccion de planificacion. No para contrasenas ni firma JWT. |
| RES-05 | JWT solo si realmente se requiere | Instruccion de planificacion. No se agrega automaticamente con sesiones de cookie. |
| RES-06 | Tres entornos soportados (XAMPP, Docker, hosting) | Instruccion de planificacion (seccion Entornos de ejecucion). |
| RES-07 | Sin secretos en el repositorio | Instruccion de planificacion. |
| RES-08 | Identificadores en espanol | Instruccion de planificacion (seccion 6). |

---

## 7. Mejoras propuestas (requieren aprobacion, NO son requisitos base)

Se listan separadamente para no confundirlas con lo solicitado:

1. **Notificaciones por email/SMS** (confirmacion de reserva, pedido listo). Requieren servicio de correo y credenciales.
2. **JWT / access token / refresh token** para una API stateless (solo si se decide exponer API para clientes externos). Actualmente se usa sesion de cookie nativa de PHP.
3. **Pagos en linea / facturacion electronica**. Requieren proveedor de pagos y cumplimiento normativo.
4. **Subida de imagenes de platos / areas**. Requieren validacion y almacenamiento de archivos.
5. **Reseñas y calificaciones de platos/mesas**.
6. **Geolocalizacion / mapa del restaurante**.
7. **Delivery / pedidos para llevar**.
8. **Autenticacion mediante proveedor externo (ID token)** (OAuth/OIDC). Solo si existe un proveedor de identidad que lo justifique.

Cada mejora debe documentarse como un bloqueo si depende de un servicio o credencial no disponible (principio: no implementar lo que no se puede completar).

---

## 8. Ambigüedades y decisiones pendientes

| # | Ambiguedad / decision pendente | Impacto | Propuesta temporal | Requiere confirmacion |
|---|-------------------------------|---------|--------------------|-----------------------|
| A-01 | Cantidad de areas y mesas | Modelado de BD y capacidad | Diseno para N areas y M mesas por area, configurable | No (es de diseno) |
| A-02 | Duracion estándar del turno de reserva | Slots de disponibilidad | 60 minutos por defecto, configurable por administrador | No |
| A-03 | Horarios de funcionamiento por area/mesa | Validacion de reserva | Configurables por administrador (apertura/cierre) | No |
| A-04 | Uso de JWT vs sesion de cookie | Arquitectura de autenticacion | Sesion de cookie nativa de PHP. JWT solo si se exige API stateless | Si |
| A-05 | Notificaciones (email/SMS) | Alcance funcional | No incluidas en version base. Mejora opcional | Si |
| A-06 | Politica de vencimiento de reservas | Estados y transiciones | Reserva pendiente se vence si no se confirma en X minutos (por definir) | Si |
| A-07 | Tiempo de anticipacion maxima/minima para reservar | Reglas de negocio | Por definir (ej. reservar con hasta 7 dias de anticipacion) | Si |
| A-08 | Categorías de productos | Menu del restaurante | Por definir (ej. bebidas, platos principales, postres) | No |
| A-09 | Grupo de 1 o 2 integrantes | Planificacion de commits y carga de trabajo | Se planifica para 1 integrante, escalable a 2 | No |

---

## 9. Matriz de trazabilidad PDF -> Plan

| Elemento del PDF | Donde se refleja en el plan |
|------------------|------------------------------|
| Actividad 2: gestion de un restaurante | 01_analisis_requisitos.md (seccion 2), 03_base_de_datos.md, modulos RF |
| Problema: dificultades organizar reservas de mesas | RB-01, RF-13, transacciones/bloqueo en 03_base_de_datos.md |
| Problema: registrar pedidos | RF-20, RF-21, modulo pedidos |
| Problema: controlar estado de pedidos | RF-22, RB-12, estados de pedido |
| Problema: demoras y errores en la atencion | RF-22, estados de pedido, diseno de interfaz en 04_diseno_y_accesibilidad.md |
| Etapas del proyecto (analisis, diseno, desarrollo, pruebas, presentacion) | 08_hoja_de_ruta.md (fases) |
| Criterios de evaluacion (documentacion, diagramas, prototipo, defensa oral) | 06_pruebas_y_calidad.md, 08_hoja_de_ruta.md |
| Modalidad individual o hasta 2 | 07_git_ci_cd_y_docker.md (plan de commits escalable) |
