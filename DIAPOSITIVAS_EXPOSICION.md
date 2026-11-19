# DIAPOSITIVAS PARA EXPOSICIÓN — PROYECTO INTEGRADOR

**Sistema de Gestión de Restaurante "Green en la Casa Blanca"**
EPET No 7 "Vicente Arcadio Salemi" — Tecnicatura en Programación 6.º IV

---

## 1. PORTADA

**Título del proyecto:** Sistema de Gestión de Restaurante "Green en la Casa Blanca"

**Tipo:** Proyecto Integrador del Primer Cuatrimestre 2026

**Autores:** Acosta, Azul y Villalba, Yensel

**Institución:** EPET No 7 "Vicente Arcadio Salemi"

**Carrera:** Tecnicatura en Programación — 6.º IV

**Cátedras:** Laboratorio de Desarrollo de Aplicaciones III y Prácticas Pre-Profesionalizantes

**Profesores responsables:** Pedro Veray, Yanina Martínez

**Fecha:** 18 de Noviembre de 2026

---

## 2. ENTREVISTA

Para el relevamiento se diseñaron entrevistas abiertas y cerradas dirigidas a los tres perfiles clave del sistema: **administrador**, **encargado** y **cliente**. Las entrevistas permiten contrastar la percepción operativa, de gestión y de uso final del sistema.

### Entrevista al Administrador

**Preguntas abiertas:**
1. ¿Cómo administra actualmente los usuarios y roles del personal del restaurante?
2. ¿Qué reportes le serían más útiles para supervisar el negocio (ventas, reservas, pedidos)?
3. ¿Cómo da seguimiento al historial de cambios y movimientos críticos del sistema?
4. ¿Qué tan difícil es para usted supervisar múltiples áreas o mesas al mismo tiempo?
5. ¿Qué funcionalidades considera prioritarias para el rol de administrador?

**Preguntas cerradas (Sí/No):**
1. ¿Existe un registro de usuarios con roles diferenciados en el restaurante?
2. ¿Genera reportes de ventas o reservas periódicamente?
3. ¿Puede consultar el historial de movimientos de pedidos y reservas?
4. ¿Bloquea mesas por mantenimiento o eventos especiales?
5. ¿Considera necesario un panel exclusivo de administración?
6. ¿Asigna roles y permisos a los usuarios del sistema?
7. ¿Requiere exportar reportes a PDF o Excel?
8. ¿Considera útil recibir alertas automáticas de stock bajo?
9. ¿Apoyaría la implementación de un sistema digital para el restaurante?
10. ¿Utilizaría un panel web para administrar usuarios y reportes?

### Entrevista al Encargado

**Preguntas abiertas:**
1. ¿Cómo gestiona actualmente la disponibilidad de mesas y el calendario de reservas?
2. ¿Qué problemas le genera no tener control del stock y el límite de venta por plato?
3. ¿Cómo coordina los estados de los pedidos entre la cocina, el salón y la caja?
4. ¿Qué tan difícil es para usted gestionar las señas y los tipos de entrega (para llevar, domicilio)?
5. ¿Qué mejoras le gustaría ver en el panel de control del restaurante?

**Preguntas cerradas (Sí/No):**
1. ¿Registra las reservas de mesas de forma digital o en papel?
2. ¿Tiene un sistema para abrir disponibilidad de mesas por varias fechas?
3. ¿Controla el stock de productos en el restaurante?
4. ¿Cambia el estado de los pedidos (pendiente, listo, entregado)?
5. ¿Gestiona el estado de las señas de los pedidos anticipados?
6. ¿Registra los productos del menú semanal con precios y disponibilidad?
7. ¿Considera útil un historial digital de movimientos de pedidos?
8. ¿Sería útil recibir alertas automáticas de pedidos vencidos o stock bajo?
9. ¿Considera necesario generar reportes automáticos de ventas?
10. ¿Apoyaría la implementación de un sistema digital para el restaurante?

### Entrevista al Cliente

**Preguntas abiertas:**
1. ¿Cómo describiría su experiencia actual al intentar reservar una mesa en el restaurante?
2. ¿Qué información le gustaría encontrar al consultar la carta o el menú semanal antes de visitar el local?
3. ¿Qué tan importante es para usted poder consultar el estado de su pedido en tiempo real?
4. ¿Qué dificultades ha tenido al realizar un pedido para llevar o a domicilio?
5. ¿Qué le parecería poder pagar una seña del 50 % al confirmar su pedido anticipado?

**Preguntas cerradas (Sí/No):**
1. ¿Ha realizado alguna reserva en el restaurante?
2. ¿Prefiere reservar con anticipación o llegar sin reserva?
3. ¿Consulta el menú semanal antes de visitar el restaurante?
4. ¿Le interesa recibir notificaciones del estado de su pedido?
5. ¿Considera útil poder cancelar su reserva desde una aplicación web?
6. ¿Ha pedido para llevar o a domicilio en el restaurante?
7. ¿Le gustaría consultar el menú semanal desde su celular?
8. ¿Considera útil pagar una seña del 50 % al confirmar un pedido anticipado?
9. ¿Le sería útil ver el historial de sus propios pedidos y reservas?
10. ¿Utilizaría un sistema digital para reservar mesas y hacer pedidos?

---

## 3. MUNDO DEL PROBLEMA

### Situación problemática

El restaurante "Green en la Casa Blanca" presenta dificultades para organizar las reservas de mesas, registrar los pedidos y controlar su estado de forma ordenada. Actualmente el control se realiza de manera **manual o desordenada**, lo que genera:

- Demoras en la atención al cliente.
- Mesas ocupadas sin reserva registrada.
- Pedidos extraviados o mal anotados.
- Falta de trazabilidad sobre el estado de cada orden.
- Errores al momento de cobrar o administrar las señas.

Esto afecta la experiencia del cliente y la operación del local.

### Necesidad identificada

Frente a esta problemática surge la necesidad de desarrollar un **sistema digital de gestión** que permita registrar usuarios, reservas, pedidos, señas, stock y estados de los materiales, mejorando la administración, organización y seguimiento de los recursos disponibles dentro del restaurante.

### Cuadro del mundo del problema

| Elemento | Descripción |
|----------|-------------|
| **Cliente** | Restaurante Green |
| **Mundo del Problema** | El restaurante presenta dificultades para organizar las reservas de mesas y mantener actualizada la información disponible para los clientes. |
| **Requerimientos Funcionales (RF)** | Gestionar menú, gestionar reservas, gestionar pedidos, gestionar usuarios, pedir reservas y pedidos. |
| **Requerimientos No Funcionales (RNF)** | Debe tener buen rendimiento, velocidad, seguridad, usabilidad y escalabilidad. |

---

## 4. MATRIZ FODA

### Fortalezas (interna, positiva)
- Centraliza toda la información del restaurante en una única plataforma.
- Permite conocer en tiempo real la disponibilidad de mesas.
- Reduce tiempo y esfuerzo de papeleos por reserva.
- Mantiene un historial completo de movimientos y operaciones.
- Facilita la identificación de responsables de cada reserva realizada.
- Mejora el control de stock disponible.
- Genera reportes automáticos sobre las reservas y ventas.

### Debilidades (interna, negativa)
- El tiempo inicial de carga de datos y organización es muy tardado.
- Algunas funciones avanzadas pueden requerir capacitación inicial.
- Necesita mantenimiento periódico de la información almacenada.
- Puede requerir actualización futura para adaptarse a nuevas necesidades institucionales.

### Oportunidades (externa, positiva)
- Digitalización de procesos administrativos dentro del restaurante.
- Avances tecnológicos que permiten incorporar nuevas funcionalidades y mejoras al sistema.
- Obtención de estadísticas para mejorar la administración del restaurante.
- Automatización de tareas que actualmente se realizan manualmente.
- Mejora continua basada en la experiencia de uso de encargados y clientes.

### Amenazas (externa, negativa)
- Falla o deterioro físico de los equipos tecnológicos de la institución.
- Pérdida de información por errores técnicos si no existen respaldos adecuados.
- Riesgo de intentos de acceso indebido o ataques informáticos a los sistemas institucionales.
- Problemas de conectividad que dificultan el acceso al sistema.

---

## 5. OBJETIVO GENERAL Y OBJETIVOS ESPECÍFICOS

### Objetivo general

Desarrollar un sistema web para el Restaurante Green que permita gestionar pedidos del restaurante, administrar las reservas, actualizar el menú por semana con sus precios y combos, administrar los permisos de encargados y gestionar las ventas del restaurante.

### Objetivos específicos

1. **Facilitar a los clientes** la realización de reservas de mesas y pedidos anticipados, ofreciendo opciones de retiro en el local o entrega a domicilio.

2. **Proporcionar un menú semanal actualizado** con información de los platos, precios y combos disponibles, permitiendo que los encargados administren su contenido.

3. **Optimizar la administración del restaurante** mediante el control de usuarios, permisos, reservas, pedidos y ventas.

---

## 6. REQUERIMIENTOS FUNCIONALES

El sistema contempla **53 requerimientos funcionales** organizados por área de gestión:

### Gestión de Usuarios
- **RF01:** Iniciar sesión / cerrar sesión / verificar sesión (Alta)
- **RF02:** Registrar usuario (Alta)
- **RF03:** Editar perfil de usuario (Alta)
- **RF04:** Cambiar contraseña (Alta)
- **RF05:** Gestionar usuarios (CRUD) (Alta)
- **RF06:** Asignar roles y permisos (Alta)
- **RF39:** Verificar sesión activa (Media)
- **RF40:** Cerrar sesión (Media)
- **RF43:** Redirigir rutas por rol (Alta)
- **RF44:** Ver mis datos de perfil (Media)

### Gestión de Reservas
- **RF07:** Hacer reserva de mesa (Alta)
- **RF08:** Ver y cancelar reservas propias (Alta)
- **RF09:** Consultar detalle de reserva (Media)
- **RF10:** Cambiar estado de reservas (Alta)
- **RF11:** Bloquear mesas (Alta)
- **RF12:** Abrir disponibilidad de mesas (Alta)
- **RF47:** Cambiar estado de reserva (detallado) (Alta)
- **RF49:** Consultar reservas del día (Alta)

### Gestión de Mesas y Áreas
- **RF13:** Listar áreas del restaurante (Media)
- **RF14:** Gestionar áreas (CRUD) (Media)
- **RF15:** Listar mesas por área (Media)
- **RF16:** Gestionar mesas (CRUD) (Media)
- **RF45:** Listar mesas disponibles para reserva (Alta)
- **RF46:** Consultar disponibilidad de mesas por fecha (Alta)

### Gestión de Pedidos
- **RF17:** Hacer pedido con seña 50% (Alta)
- **RF18:** Ver mis pedidos y estado de seña (Alta)
- **RF19:** Consultar detalle de pedido con ítems (Media)
- **RF20:** Cambiar estado de pedidos (Alta)
- **RF21:** Asignar mesero a un pedido (Media)
- **RF48:** Cambiar estado de pedido (detallado) (Alta)
- **RF50:** Consultar pedidos del día (Alta)

### Gestión de Señas
- **RF17:** Hacer pedido con seña 50% (Alta)
- **RF18:** Ver mis pedidos y estado de seña (Alta)
- **RF24:** Actualizar estado de seña (Alta)

### Gestión de Productos y Menú
- **RF25:** Ver carta de productos con filtros (Alta)
- **RF26:** Gestionar productos (CRUD) (Alta)
- **RF27:** Gestionar categorías (Media)
- **RF30:** Ver menú destacado (Media)
- **RF51:** Gestionar menú semanal (Media)

### Gestión de Stock
- **RF22:** Validar stock y límite de venta (Alta)
- **RF23:** Descontar stock automáticamente (Alta)
- **RF28:** Gestionar stock y límite de venta por producto (Alta)
- **RF29:** Consultar stock bajo / alertas (Alta)

### Gestión de Reportes
- **RF31:** Reportes de ventas (Alta)
- **RF32:** Productos más vendidos (Media)
- **RF33:** Reservas por día (Media)
- **RF34:** Exportar reportes a CSV (Alta)
- **RF53:** Exportar historial de movimientos (Media)

### Gestión de Historial
- **RF35:** Consultar historial de movimientos (Alta)
- **RF36:** Registro automático de movimientos (Alta)

---

## 7. ALCANCE DEL SISTEMA

### Alcance general

El sistema estará destinado al uso de administradores, encargados y clientes autorizados, proporcionando mecanismos para gestionar reservas, pedidos, stock, menú semanal, señas, historial de movimientos y generación de reportes.

### Funciones incluidas en el sistema

| Área | Funciones |
|------|-----------|
| Gestión de Usuarios | Registro, edición, desactivación, roles (cliente, encargado, administrador), permisos, cambio de contraseña, ver perfil |
| Gestión de Reservas | Crear, consultar, cancelar, cambiar estado (pendiente, confirmada, cancelada, vencida, completada), bloquear mesas, abrir disponibilidad |
| Gestión de Mesas y Áreas | CRUD de áreas y mesas, consultar disponibilidad por fecha, listar mesas por área |
| Gestión de Horarios y Disponibilidad | Crear, editar y desactivar horarios de apertura y cierre por área |
| Gestión de Pedidos | Registrar pedidos con seña 50%, cambiar estado (pendiente, en preparación, listo, entregado, cancelado), asignar mesero, consultar detalle |
| Gestión de Señas | Registrar seña del 50%, consultar estado (pendiente, pagada, devuelta), actualizar estado de seña |
| Gestión de Productos y Menú | CRUD de productos y categorías, menú semanal, menú destacado (3 más vendidos), filtros por categoría |
| Gestión de Stock | Stock actual, límite de venta por producto, descuento automático, alertas de stock bajo |
| Gestión de Reportes | Reportes de ventas por período, productos más vendidos, reservas por día, exportar a CSV |
| Gestión de Historial | Registro automático de movimientos, consulta de historial con filtros, exportar a CSV |

---

## 8. LIMITACIONES

### Limitaciones funcionales (lo que el sistema NO incluye)

- **Pagos en línea** o integración con pasarelas de pago reales (la seña se gestiona como estado pendiente/pagada/devuelta).
- **Facturación electrónica** vinculada a AFIP u otro ente fiscal.
- **Integración con impresoras de cocina** o comandas físicas automáticas.
- **Gestión de proveedores y compras** de insumos.
- **Módulo de delivery o envíos a domicilio propio** (el domicilio se gestiona como tipo de pedido con dirección).
- **Aplicación móvil nativa** para Android o iOS (el acceso será vía navegador web).
- **Control mediante códigos QR o RFID**.
- **Manual de usuario** para clientes, encargados y administradores.
- **Contratación de un hosting** en la nube.
- **Despliegue de la base de datos** a un hosting.
- **Publicación del frontend y backend** con dominio institucional o subdominio.

### Limitaciones técnicas y operativas

- El tiempo inicial de carga de datos y organización es muy tardado.
- Algunas funciones avanzadas pueden requerir capacitación inicial.
- Necesita mantenimiento periódico de la información almacenada.
- Puede requerir actualización futura para adaptarse a nuevas necesidades institucionales.
- Falla o deterioro físico de los equipos tecnológicos.
- Pérdida de información por errores técnicos si no existen respaldos adecuados.
- Riesgo de intentos de acceso indebido o ataques informáticos.
- Problemas de conectividad que dificultan el acceso al sistema.

---

## 9. ENTREGABLES

El proyecto deberá contar con los siguientes entregables:

1. **Documentación completa** del análisis, diseño y desarrollo del sistema.
2. **Diagramas:** Diagrama de Gantt, Diagrama de Casos de Uso, Diagrama Entidad-Relación, Diccionario de Datos, Diagrama de Clases.
3. **Sistema funcional** que permita demostrar las principales funcionalidades planteadas.
4. **Exposición y demostración del funcionamiento** del sistema.

---

## 10. DIAGRAMA DE GANTT

![Diagrama de Gantt](entregables/odt_extract/Pictures/Diagrama_Gantt.png)

*(Imagen del diagrama de Gantt — cronograma de actividades del cuatrimestre 2026)*

---

## 11. DIAGRAMA DE CASOS DE USO

![Diagrama de Casos de Uso](entregables/Diagrama%20de%20casos%20de%20uso%20GREEN.drawio)

*(Imagen del diagrama de casos de uso — actores y casos de uso del sistema)*

---

## 12. DIAGRAMA ENTIDAD-RELACIÓN (E-R)

![Diagrama Entidad-Relación](entregables/DER_Green_Casa_Blanca.drawio)

*(Imagen del diagrama E-R — tablas y relaciones de la base de datos)*

---

## 13. DICIONARIO DE DATOS

### roles
| Campo | Tamaño | Tipo | Descripción | Clave |
|-------|--------|------|-------------|-------|
| id_rol | 10 | Numérico | Identificador del rol | PK |
| nombre | 50 | Carácter | Nombre del rol | |
| descripcion | 200 | Carácter | Descripción del rol | |
| fecha_creacion | — | Fecha/Hora | Momento de creación del rol | |

### usuarios
| Campo | Tamaño | Tipo | Descripción | Clave |
|-------|--------|------|-------------|-------|
| id_usuario | 10 | Numérico | Identificador del usuario | PK |
| nombre | 100 | Carácter | Nombre completo del usuario | |
| email | 150 | Carácter | Correo electrónico del usuario | UK |
| telefono | 30 | Carácter | Teléfono de contacto | |
| contrasena_hash | 255 | Carácter | Hash bcrypt de la contraseña | |
| id_rol | 10 | Numérico | Rol asignado para los usuarios | FK |
| activo | 1 | Booleano | Indica si el usuario puede iniciar sesión | |
| fecha_creacion | — | Fecha/Hora | Momento de creación del usuario | |
| fecha_actualizacion | — | Fecha/Hora | Momento de última actualización | |

### mesas
| Campo | Tamaño | Tipo | Descripción | Clave |
|-------|--------|------|-------------|-------|
| id_mesa | 10 | Numérico | Identificador de la mesa | PK |
| numero_mesa | 20 | Carácter | Identificador legible de la mesa | |
| capacidad | 11 | Numérico | Cantidad de personas por mesa | |
| activo | 1 | Booleano | Indica si la mesa está habilitada | |
| fecha_creacion | — | Fecha/Hora | Momento de creación de la mesa | |

### categorías
| Campo | Tamaño | Tipo | Descripción | Clave |
|-------|--------|------|-------------|-------|
| id_categoria | 10 | Numérico | Identificador de la categoría | PK |
| nombre | 50 | Carácter | Nombre de la categoría (único) | |
| activo | 1 | Booleano | Indica si la categoría se muestra en la carta | |

### productos
| Campo | Tamaño | Tipo | Descripción | Clave |
|-------|--------|------|-------------|-------|
| id_producto | 10 | Numérico | Identificador del producto | PK |
| nombre | 150 | Carácter | Nombre del producto | |
| id_categoria | 10 | Numérico | Categoría a la que pertenece | FK |
| precio | 10,2 | Numérico | Precio de venta del producto | |
| imagen_url | 500 | Carácter | URL de la imagen del plato | |
| imagen_public_id | 200 | Carácter | Identificador de la imagen en el gestor | |
| activo | 1 | Booleano | Indica si el producto se muestra en la carta | |
| fecha_creacion | — | Fecha/Hora | Momento de creación del producto | |

### stock_producto
| Campo | Tamaño | Tipo | Descripción | Clave |
|-------|--------|------|-------------|-------|
| id_stock | 10 | Numérico | Identificador del registro de stock | PK |
| id_producto | 10 | Numérico | Producto al que corresponde el stock | FK (1:1) |
| stock_actual | 11 | Numérico | Unidades disponibles del producto | |
| límite_venta | 11 | Numérico | Máximo de unidades que se pueden vender | |
| activo | 1 | Booleano | Indica si el registro de stock está activo | |
| fecha_actualizacion | — | Fecha/Hora | Momento de última actualización del stock | |

### pedidos
| Campo | Tamaño | Tipo | Descripción | Clave |
|-------|--------|------|-------------|-------|
| id_pedido | 10 | Numérico | Identificador del pedido | PK |
| id_mesa | 10 | Numérico | Mesa donde se consumió el pedido | FK |
| id_mesero | 10 | Numérico | Mesero responsable del pedido | FK |
| id_usuario | 10 | Numérico | Cliente que realizó el pedido | FK |
| fecha_pedido | — | Fecha/Hora | Fecha y hora del pedido | |
| estado | 20 | Carácter | Estado del pedido | |
| total | 10,2 | Numérico | Total del pedido | |
| tipo_pedido | — | Carácter | Tipo de pedido | |
| seña | 10,2 | Numérico | Seña anticipada del pedido | |
| estado_senia | — | Carácter | Estado de la seña | |
| saldo_pendiente | 12,2 | Numérico | Saldo restante tras la seña | |
| numero_pedido | 30 | Carácter | Número del pedido | |
| fecha_creacion | — | Fecha/Hora | Momento de creación del pedido | |

### detalle_pedidos
| Campo | Tamaño | Tipo | Descripción | Clave |
|-------|--------|------|-------------|-------|
| id_detalle | 10 | Numérico | Identificador de la línea de detalle | PK |
| id_pedido | 10 | Numérico | Pedido al que pertenece la línea | FK |
| id_producto | 10 | Numérico | Producto vendido | FK |
| cantidad | 11 | Numérico | Unidades vendidas | |
| precio_unitario | 10,2 | Numérico | Precio unitario al momento de la venta | |

### reservas
| Campo | Tamaño | Tipo | Descripción | Clave |
|-------|--------|------|-------------|-------|
| id_reserva | 10 | Numérico | Identificador de la reserva | PK |
| id_mesa | 10 | Numérico | Mesa reservada | FK |
| id_usuario | 10 | Numérico | Cliente que reservó | FK |
| comensales | 10 | Numérico | Cantidad de personas | |
| telefono | 30 | Carácter | Teléfono de contacto de la reserva | |
| ocasión | 100 | Carácter | Ocasión de la reserva | |
| fecha_reserva | — | Fecha | Día de la reserva | |
| hora_inicio | — | Fecha/Hora | Hora de inicio de la reserva | |
| hora_fin | — | Fecha/Hora | Hora de fin de la reserva | |
| estado | 20 | Carácter | Estado: pendiente, confirmada, cancelada, vencida, completada | |
| observaciones | 500 | Carácter | Observaciones del cliente | |
| fecha_creacion | — | Fecha/Hora | Momento de creación de la reserva | |
| fecha_actualizacion | — | Fecha/Hora | Momento de última actualización | |

### historial_movimientos
| Campo | Tamaño | Tipo | Descripción | Clave |
|-------|--------|------|-------------|-------|
| id_historial | 10 | Numérico | Identificador del movimiento | PK |
| tabla_afectada | 50 | Carácter | Tabla sobre la que se actuó | |
| id_registro | 10 | Numérico | Identificador del registro afectado | |
| acción | 20 | Carácter | Acciones realizadas | |
| detalles | Texto largo | Carácter | Detalle de historial | |
| id_usuario | 10 | Numérico | Usuario que realizó el movimiento | FK |
| fecha_movimiento | — | Fecha/Hora | Fecha y hora del movimiento | |
| dirección_ip | 45 | Carácter | Dirección IP del usuario | |

---

## 14. DIAGRAMA DE CLASES

![Diagrama de Clases](entregables/Diagrama_Clases_Green.drawio)

*(Imagen del diagrama de clases — clases y asociaciones del sistema)*

---

## 15. PLAN DE MANTENIMIENTO

### Objetivo

Organizar las actividades necesarias para conservar el correcto funcionamiento del sistema, detectar errores a tiempo y aplicar las actualizaciones y mejoras que el restaurante necesite, de modo que las reservas, los pedidos, el stock y la administración de usuarios estén disponibles y sus datos protegidos.

### Responsables del mantenimiento

| Responsable | Quién es | Funciones |
|-------------|----------|-----------|
| Equipo de desarrollo | Equipo que construyó el sistema | Revisión de funcionamiento y errores, actualizaciones, correcciones y pruebas. |
| Responsable técnico | Persona designada para soporte técnico | Respaldos, restauración, control de base de datos, atención de incidencias, instalación de parches. |
| Administrador del sistema | Cuenta con rol administrador | Seguridad, usuarios y roles, contraseñas de administrador, revisión de historial, aprobación de cambios. |
| Encargado del restaurante | Responsable de la operación diaria | Control diario de reservas y pedidos, control de stock, reporte de incidencias. |

### Cuadro de actividades de mantenimiento

| Actividad | Descripción | Frecuencia | Duración | Responsable |
|-----------|-------------|------------|----------|-------------|
| Verificación del respaldo diario | Comprobar que la copia automática de la base de datos se generó sin errores | Diaria | 15 min | Responsable técnico |
| Control de reservas y pedidos | Revisar reservas pendientes o vencidas y pedidos detenidos | Diaria | 30 min | Encargado |
| Respaldo de información | Copia de seguridad completa de la base de datos | Semanal | 1 hora | Responsable técnico |
| Control de stock | Revisar productos con stock bajo o límite alcanzado | Semanal | 1 hora | Encargado |
| Control de usuarios y auditoría | Revisar historial de movimientos y usuarios activos | Semanal | 1 hora | Administrador |
| Control de información | Verificar coherencia entre pedidos, detalle, total, seña y saldo | Mensual | 1 hora | Responsable técnico |
| Cambio de contraseñas de administrador | Renovar contraseñas según política de seguridad | Mensual | 30 min | Administrador |
| Prueba de restauración | Restaurar copia completa en entorno de pruebas | Mensual | 2 horas | Responsable técnico |
| Revisión del funcionamiento | Comprobar que la aplicación funcione correctamente | Mensual | 2 horas | Equipo de desarrollo |
| Revisión de errores | Analizar incidencias, corregir errores, elaborar reporte | Mensual | 2 horas | Equipo de desarrollo |
| Actualización del software | Instalar parches y actualizaciones | Trimestral | 3 horas | Equipo de desarrollo |
| Pruebas de funcionamiento | Verificar funciones principales después de modificaciones | Después de cada actualización | 2 horas | Equipo de desarrollo |
| Atención de incidencias | Atender fallas reportadas por usuarios | Según demanda | 1-2 horas | Responsable técnico y Equipo |

### Atención de incidencias (por prioridad)

| Prioridad | Situación | Tiempo máximo de atención |
|-----------|-----------|---------------------------|
| Crítica | El sistema no está disponible o no se pueden tomar reservas ni pedidos | 4 horas hábiles |
| Alta | Falla en una función principal que tiene una alternativa temporal | 1 día hábil |
| Media | Error que no impide trabajar | 3 días hábiles |
| Baja | Mejora de interfaz o usabilidad | Se incluye en la próxima actualización trimestral |

---

## 16. PLAN DE CAPACITACIÓN

### Objetivo

Orientar a los usuarios en el manejo del sistema para que utilicen sus funciones de forma autónoma, correcta y segura, y reducir la dependencia del soporte técnico en las operaciones cotidianas.

### Destinatarios y funciones del sistema

| Destinatario | Descripción | Funciones que utilizan |
|-------------|-------------|------------------------|
| Clientes | Usuarios finales del restaurante | Reservar mesa, consultar carta, hacer pedidos, seguir estado, cancelar |
| Encargados | Responsables de operación diaria | Reservas del día, mesas, pedidos y estados, stock, carta, reportes |
| Administradores | Responsables de configuración global | Usuarios y roles, vista global de reservas y pedidos, respaldos, historial |

### Temas de capacitación

| Tema | Contenidos | Destinatarios | Duración | Responsable |
|------|-----------|---------------|----------|-------------|
| Acceso al sistema y uso general | Registro, ingreso con correo y contraseña, cierre de sesión, opciones por rol | Todos | 30 min | Equipo de desarrollo |
| Reservas y pedidos | Reservar mesa, consultar carta, hacer pedido, seña y saldo, seguir estado, cancelar | Clientes | 30 min | Equipo de desarrollo |
| Gestión de reservas y mesas | Consultar reservas del día, asignar mesas, reasignar reservas | Encargados, Admin | 1 hora | Equipo de desarrollo |
| Gestión de pedidos | Registro, cambio de estado, asignar mesero, detalle, control de seña | Encargados, Admin | 1 hora | Equipo de desarrollo |
| Gestión de stock y carta | Carga de stock, límite de venta, alta/edición de productos y categorías | Encargados | 1 hora | Equipo de desarrollo |
| Consultas y reportes | Búsqueda de reservas y pedidos, reportes de ventas y consumo diario | Encargados | 1 hora | Equipo de desarrollo |
| Gestión de usuarios y roles | Alta, edición, baja de usuarios, asignación de roles, contraseñas seguras | Administradores | 1 h 30 min | Equipo de desarrollo |
| Respaldo y auditoría | Copias de seguridad, restauración, historial, detectar actividad inusual | Administradores | 2 horas | Responsable técnico |
| Seguridad y buenas prácticas | Uso responsable de cuentas, protección de contraseñas, cierre en equipos compartidos | Todos | 30 min | Responsable técnico |

### Duración total inicial por destinatario

- **Clientes:** 1 hora 30 minutos
- **Encargados:** 5 horas
- **Administradores:** 6 horas 30 minutos

### Cronograma de capacitación

| Semana | Destinatario | Sesión | Temas | Duración | Responsable |
|--------|--------------|--------|-------|----------|-------------|
| Semana 1 | Administradores | 1 | Acceso y uso general; usuarios y roles | 2 horas | Equipo de desarrollo |
| Semana 1 | Administradores | 2 | Reservas y mesas; pedidos | 2 horas | Equipo de desarrollo |
| Semana 1 | Administradores | 3 | Respaldo y auditoría; seguridad | 2 h 30 min | Responsable técnico |
| Semana 2 | Encargados | 1 | Acceso y uso general; reservas y mesas; pedidos | 2 h 30 min | Equipo de desarrollo |
| Semana 2 | Encargados | 2 | Stock y carta; consultas y reportes; seguridad | 2 h 30 min | Equipo y Responsable técnico |
| Semana 3 | Clientes | Única | Acceso y uso general; reservas y pedidos; seguridad | 1 h 30 min | Equipo y Responsable técnico |
| Cada 6 meses | Todos | Refuerzo | Seguridad y buenas prácticas | 30 min | Responsable técnico |

### Materiales, evaluación y soporte

- **Materiales:** guía rápida por rol, videos tutoriales (hasta 5 min), ejercicios prácticos en entorno de pruebas.
- **Evaluación:** prueba práctica supervisada por rol + encuesta de satisfacción. Resultados en acta de capacitación.
- **Soporte posterior:** consultas por correo o grupo de mensajería, lunes a viernes 9:00-18:00, respuesta en máximo 24 horas hábiles.

---


