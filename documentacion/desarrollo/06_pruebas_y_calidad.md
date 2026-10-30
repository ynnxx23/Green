# 06. Pruebas y calidad — Sistema de gestion de un restaurante

> **Estado:** borrador de planificacion (etapa 1 - sin programar).  
> **Idioma:** espanol.  
> **Principio:** una funcionalidad no se da por terminada solo porque funciona en el caso más sencillo. Cada prueba tiene un resultado esperado verificable.

---

## 1. Estrategia de pruebas

| Tipo de prueba | Alcance | Herramienta propuesta | Entorno |
|----------------|---------|----------------------|---------|
| Pruebas unitarias | Lógica de negocio (servicios, validadores): disponibilidad de mesas, solapamiento de reservas, cálculo de hora_fin, estados de reserva, estados de pedido, hash de contraseña | PHPUnit (o tests nativos de PHP si no se usa Composer) | Desarrollo / pruebas |
| Pruebas de integración con la base de datos | Consultas preparadas, transacciones, restricciones FK, integridad referencial, bloqueo de filas para reservas concurrentes | PHP + PDO contra MariaDB/MySQL de pruebas | Base de datos de pruebas (no producción) |
| Pruebas de endpoints | Endpoints API: códigos HTTP, formato JSON, autenticación, autorización, validación de entrada | Peticiones HTTP automatizadas (curl / cliente HTTP de PHP / JS) | Servidor de pruebas |
| Pruebas de autenticación y autorización | Login, logout, registro, recuperación, roles, acceso a recursos ajenos, sesión expirada | Pruebas funcionales automatizadas + manuales | Entorno de pruebas |
| Pruebas de validación de formularios | Campos obligatorios, tipos, longitudes, formatos, rangos, conservación de datos ante error | Pruebas funcionales | Navegador real |
| Pruebas de accesibilidad | Teclado, foco, etiquetas, contraste, semántica, mensajes de estado, ARIA | Navegador + herramientas de auditoría (axe / Lighthouse) + revisión manual | Navegadores reales |
| Pruebas responsive | Diseño en móvil, tablet, escritorio; puntos de quiebre; áreas de interacción ≥ 44px | Cambio de tamaño de ventana / herramientas de desarrollador | Navegadores reales |
| Pruebas de concurrencia en reservas | Dos clientes intentando reservar la misma mesa y mismo horario al mismo tiempo; solo una debe confirmarse | Scripts de peticiones simultáneas contra el servidor | Entorno de pruebas con BD |
| Pruebas de limitación de solicitudes (rate limiting) | Superar el límite de un endpoint → HTTP 429; `Retry-After`; endpoints sensibles más restrictivos | Scripts de peticiones masivas | Entorno de pruebas |
| Pruebas de regresión | Después de cada cambio, reejecutar pruebas críticas para verificar que no se rompió lo existente | Ejecución del conjunto de pruebas | Entorno de pruebas |
| Pruebas de manejo de errores | Entradas inválidas, SQL injection, sesiones vencidas, BD caída, archivo de configuración faltante | Pruebas funcionales + unitarias | Entorno de pruebas |

**Datos de prueba:** solo en entornos de pruebas, identificados expresamente como tales. Nunca datos ficticios presentados como reales. Nunca credenciales reales en pruebas.

---

## 2. Casos críticos (obligatorios)

Cada caso crítico tiene un resultado esperado verificable.

### C-01. Dos clientes reservan la misma mesa y horario al mismo tiempo (concurrencia)
- **Escenario:** Cliente A y Cliente B envían una petición de reserva para la misma mesa, misma fecha y mismo horario, de forma simultánea.
- **Resultado esperado:** Solo una reserva se crea y se confirma. La segunda recibe error (conflicto HTTP 409 o mensaje "La mesa ya está reservada en ese horario"). La base de datos termina con exactamente una reserva confirmada para ese intervalo. Ninguna reserva duplicada ni superpuesta.
- **Verificación:** contar filas en `reservas` para esa mesa/fecha/intervalo y estados = 1.

### C-02. Usuario intenta acceder a reserva ajena mediante ID manipulado
- **Escenario:** Usuario autenticado A consulta o cancela `/api/reservas/{id_reserva_de_B}`.
- **Resultado esperado:** HTTP 403 Prohibido o 404 (sin revelar existencia), y la reserva de B no se modifica ni elimina. Solo el dueño o un administrador puede acceder.
- **Verificación:** respuesta 403/404; reserva de B intacta en BD.

### C-03. Intento de inyección SQL
- **Escenario:** Enviar `' OR 1=1 --` en campos de entrada (login, búsqueda de mesas, ID de reserva).
- **Resultado esperado:** La consulta falla de forma controlada o devuelve sin resultados; no se expone información de la base de datos; no se autentica sin contraseña. HTTP 400 o error genérico, nunca consulta SQL en la respuesta.
- **Verificación:** uso de consultas preparadas con PDO; respuesta no revela SQL.

### C-04. Sesión vencida
- **Escenario:** Usuario con sesión expirada intenta acceder a un endpoint protegido.
- **Resultado esperado:** HTTP 401 No autorizado; redirección al login (si es navegador) o JSON con error de autenticación. No se ejecuta la operación protegida.
- **Verificación:** respuesta 401; no se accede al recurso.

### C-05. Solicitudes que superan los límites establecidos (rate limiting)
- **Escenario:** Enviar más peticiones de las permitidas a `/api/login` o `/api/reservas` en el intervalo de reposición.
- **Resultado esperado:** HTTP 429 Demasiadas solicitudes con cuerpo JSON y cabecera `Retry-After`. La operación protegida no se ejecuta.
- **Verificación:** código 429; `Retry-After` presente.

### C-06. Reserva fuera del horario de funcionamiento
- **Escenario:** Intentar reservar una mesa fuera de su horario de apertura/cierre.
- **Resultado esperado:** Error de validación en servidor; no se crea la reserva.
- **Verificación:** respuesta de error; reserva no insertada.

### C-07. Creación de reserva con hora fin menor o igual a hora inicio
- **Escenario:** Enviar `hora_fin <= hora_inicio`.
- **Resultado esperado:** Error de validación; no se crea la reserva.
- **Verificación:** respuesta de error.

### C-08. Cancelación de reserva libera la mesa
- **Escenario:** Usuario cancela una reserva confirmada futura; otro usuario consulta disponibilidad para ese mismo horario.
- **Resultado esperado:** La mesa vuelve a estar disponible y puede ser reservada por otro usuario.
- **Verificación:** reserva pasa a `cancelada`; consulta de disponibilidad muestra el horario libre.

### C-09. Contraseña almacenada segura
- **Escenario:** Registrar un usuario y consultar el valor almacenado en `usuarios.contrasena_hash`.
- **Resultado esperado:** El valor no es texto plano ni SHA-256; es un hash bcrypt (`$2y$...`). `password_verify` devuelve true con la contraseña correcta y false con una incorrecta.
- **Verificación:** inspección de BD + `password_verify`.

### C-10. Acceso administrativo restringido
- **Escenario:** Usuario con rol `cliente` o `mesero` intenta acceder a endpoints de administración (gestionar usuarios, areas, mesas, productos).
- **Resultado esperado:** HTTP 403 Prohibido.
- **Verificación:** respuesta 403.

### C-11. Formulario con datos inválidos conserva lo ingresado
- **Escenario:** Enviar formulario de reserva con error de servidor; se muestra el formulario nuevamente.
- **Resultado esperado:** Los datos previamente ingresados se conservan (no se borran); mensajes de error junto a los campos correspondientes.
- **Verificación:** campos rellenados con los valores originales; mensajes de error visibles.

### C-12. Estado de reserva cumple transiciones válidas
- **Escenario:** Intentar pasar una reserva `cancelada` a `confirmada` o eliminar una reserva `completada`.
- **Resultado esperado:** Error de regla de negocio; solo transiciones permitidas.
- **Verificación:** respuesta de error; estado no cambiado.

### C-13. Estado de pedido cumple transiciones válidas
- **Escenario:** Intentar pasar un pedido `entregado` a `pendiente` o un pedido `cancelado` a `en_preparacion`.
- **Resultado esperado:** Error de regla de negocio; solo transiciones permitidas (`pendiente → en_preparación → listo → entregado`).
- **Verificación:** respuesta de error; estado no cambiado.

### C-14. Pedido asociado a mesa y mesero válidos
- **Escenario:** Intentar crear un pedido con id_mesa o id_mesero inexistentes.
- **Resultado esperado:** Error de validación en servidor; no se crea el pedido.
- **Verificación:** respuesta de error; pedido no insertado.

### C-15. Mesero no puede ver pedidos de otro mesero
- **Escenario:** Mesero autenticado A consulta pedidos de mesero B.
- **Resultado esperado:** HTTP 403 Prohibido o solo ve sus propios pedidos.
- **Verificación:** respuesta 403 o lista filtrada por id_mesero.

---

## 3. Criterios de aceptación verificables (por módulo)

| Módulo | Criterios de aceptación |
|--------|-------------------------|
| Registro / login / logout | El usuario se registra con contraseña hasheada (bcrypt). El login con credenciales correctas crea sesión; con incorrectas, falla genéricamente. El logout invalida la sesión y la cookie. |
| Recuperación de contraseña | Se genera token de un solo uso con expiración; tras usarse, se invalida. La contraseña nueva se hashea con bcrypt. |
| Gestión de usuarios y roles | El administrador crea/edita/desactiva usuarios. Un cliente/mesero/cocinero no puede acceder a estas operaciones (403). |
| Consulta de areas y mesas | Se listan correctamente; las inactivas no se muestran para reserva. |
| Consulta de disponibilidad de mesas | Devuelve solo horarios libres considerando reservas, bloqueos y horario de funcionamiento. No muestra horarios ocupados. |
| Creación de reserva | Crea reserva dentro de horario válido, estado inicial correcto, hora_fin > hora_inicio. |
| Prevención de superposiciones | No permite dos reservas confirmadas superpuestas en la misma mesa (ver C-01). |
| Historial de reservas | El cliente ve sus propias reservas; no ve las de otros. |
| Gestión de estados de reserva | Las transiciones de estado respetan las reglas definidas; un administrador puede cambiar estados de reservas ajenas. |
| Registro de pedidos | Se crea pedido asociado a mesa y mesero válidos, con productos y cantidades. |
| Gestión de estados de pedido | Las transiciones respetan `pendiente → en_preparación → listo → entregado`; solo mesero/cocinero/administrador según corresponda. |
| Rate limiting | Endpoints sensibles devuelven 429 al superar el límite (ver C-05). |
| Seguridad | No hay SQL injection, XSS (escape de salida), contraseñas en texto plano, sesión sin HttpOnly/Secure. |
| Accesibilidad | Navegación por teclado completa, foco visible, etiquetas en campos, contraste suficiente, semántica correcta. |
| Responsive | Funciona en móvil, tablet y escritorio; áreas de interacción ≥ 44px. |

---

## 4. Verificaciones de seguridad, accesibilidad y concurrencia

- **Seguridad:** revisión de que todas las consultas usan PDO preparado; revisión de cookies (`HttpOnly`, `Secure`, `SameSite`); prueba de fuerza bruta en login; prueba de acceso a recurso ajeno; revisión de que ningún error revela stack traces ni consultas SQL.
- **Accesibilidad:** auditoría con teclado únicamente (sin mouse); verificador de contraste; validador HTML semántico; lectores de pantalla (NVDA / VoiceOver) para mensajes de estado y errores.
- **Concurrencia:** prueba C-01 con peticiones simultáneas reales; revisar nivel de aislamiento y bloqueo de filas (`FOR UPDATE`); verificar que no haya reservas duplicadas bajo carga.

---

## 5. Definición de "terminado"

Una etapa o funcionalidad se considera terminada cuando:
1. El código ejecuta sin errores críticos.
2. Se cumplen todos sus criterios de aceptación.
3. Se ejecutaron las pruebas unitarias, de integración y de endpoints asociadas.
4. Se verificaron los casos críticos aplicables.
5. Se documentó cualquier decisión o cambio (ningún cambio improvisado).
