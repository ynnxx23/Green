# 02. Etapa 1 — Requerimientos

## Objetivos del sistema

El objetivo principal del sistema es contar con una plataforma **centralizada para la gestión de reservas y la consulta del menú semanal** del restaurante "Green en la Casa Blanca", permitiendo a los clientes consultar la oferta semanal, reservar mesas y realizar pedidos (con o sin reserva), y al encargado del restaurante y al administrador supervisar y controlar toda la operación desde un único punto.

Objetivos específicos:

- Centralizar la toma de reservas y la consulta del menú semanal en un solo sistema.
- Facilitar la realización de pedidos con o sin reserva, bajo los tipos: con reserva, para llevar y domicilio.
- Establecer una regla de seña del 50 % para pedidos anticipados, con o sin reserva.
- Brindar al encargado del restaurante herramientas para confirmar, rechazar, modificar y cancelar reservas, así como controlar la disponibilidad de mesas y gestionar el menú semanal.
- Permitir al administrador supervisar el sistema, administrar usuarios, roles y permisos, y controlar encargados del restaurante.
- Garantizar trazabilidad de pedidos, reservas y señas para el control operativo y financiero.

## Matriz FODA

### Sistema actual

| Fortalezas                                                                  | Debilidades                                                                                   |
| --------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| Atención personalizada y directa del personal con el cliente.               | No hay registro centralizado de reservas ni del menú semanal.                                 |
| Flexibilidad para anotar pedidos y señas a mano sin depender de tecnología. | Pedidos y señas propensos a errores, tachones y extravíos.                                    |
| Bajo costo operativo inicial (no requiere software ni equipos).             | No hay visibilidad del estado del pedido o la reserva en tiempo real.                         |
| Conocimiento empírico del personal sobre mesas y clientes habituales.       | No se controla stock ni límites de venta por plato del menú semanal.                          |
|                                                                             | No existe gestión de usuarios con roles y permisos.                                           |
|                                                                             | No se pueden bloquear mesas de forma planificada.                                             |
|                                                                             | No hay registro sistemático de señas ni de tipos de pedido (reserva, para llevar, domicilio). |

| Oportunidades                                                              | Amenazas                                                                |
| -------------------------------------------------------------------------- | ----------------------------------------------------------------------- |
| Digitalizar reservas y reducir tiempos de espera en el restaurante.        | Pérdida de clientes por demoras y errores en la atención.               |
| Mejorar el control del menú semanal y evitar ventas de platos sin insumos. | Competencia con restaurantes que ya usan sistemas de gestión.           |
| Generar historial de pedidos, reservas y señas para análisis futuro.       | Dependencia de memoria individual del personal (rotación de empleados). |
| Ofrecer consulta de menú semanal y pedidos desde el celular del cliente.   | Fallos operativos si no hay respaldo ante caídas del sistema.           |

### Sistema propuesto

| Fortalezas                                                                                             | Debilidades                                                         |
| ------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------- |
| Tres roles claros: cliente, encargado del restaurante y administrador, con menús y permisos distintos. | Requiere conexión a internet y dispositivos para operar.            |
| Reservas organizadas por calendario con días disponibles y no disponibles.                             | Curva de aprendizaje para el personal no acostumbrado a tecnología. |
| Pedidos registrados con productos, cantidades, tipo de pedido y estado trazable.                       | Costo de mantenimiento del software y hosting.                      |
| Regla de seña del 50 % aplicada a pedidos anticipados con o sin reserva.                               | Posibles errores de carga manual si el personal no es constante.    |
| Control del menú semanal y límite de ventas por plato según disponibilidad.                            |                                                                     |
| Mesas bloqueables por mantenimiento o eventos.                                                         |                                                                     |
| Gestión de tipos de pedido: con reserva, para llevar y domicilio.                                      |                                                                     |

| Oportunidades                                                                | Amenazas                                                                |
| ---------------------------------------------------------------------------- | ----------------------------------------------------------------------- |
| Escalabilidad a más módulos (facturación, delivery, múltiples sucursales).   | Riesgo de seguridad si no se protegen credenciales y datos de usuarios. |
| Mejora continua basada en uso real y reportes del sistema.                   | Dependencia de proveedor de hosting o dominio.                          |
| Diferenciación competitiva por atención ordenada y rápida en el restaurante. | Cambios en normativas locales que afecten gestión de datos.             |
| Control centralizado de señas, pedidos y reservas del menú semanal.          |                                                                         |

## Roles del sistema

| Rol                       | Descripción                                                                                                                                                                                                                                                                    |
| ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Cliente                   | Consulta el menú semanal, verifica disponibilidad de mesas, realiza reservas, hace pedidos (con o sin reserva), consulta y cancela sus propias reservas y pedidos.                                                                                                             |
| Encargado del restaurante | Consulta, confirma, rechaza, modifica y cancela reservas; controla disponibilidad de mesas; gestiona el menú semanal; consulta pedidos de clientes y señas; administra delivery y retiro; registra, modifica y desactiva encargados; gestiona permisos y supervisa el sistema. |
| Administrador             | Supervisa el sistema en su totalidad, administra usuarios, roles y permisos, y da de alta/modifica/desactiva encargados del restaurante.                                                                                                                                       |

## Requerimientos funcionales

### Clientes

1. Registro de nuevos usuarios con rol cliente.
2. Inicio de sesión con autenticación por correo electrónico y contraseña.
3. Consulta del menú semanal del restaurante (platos, bebidas, precios y disponibilidad).
4. Visualización de platos del menú semanal con detalle y disponibilidad.
5. Consulta de disponibilidad de mesas seleccionando fecha, horario y cantidad de comensales.
6. Realización de reservas de mesas seleccionando fecha, horario y cantidad de comensales.
7. Consulta de mis reservas desde el perfil del cliente.
8. Cancelación de mis reservas por parte del cliente.
9. Realización de pedidos con o sin reserva, seleccionando productos y cantidades.
10. Tipos de pedido soportados: con reserva, para llevar y domicilio.
11. Consulta de mis pedidos desde el perfil del cliente.
12. Pago de seña del 50 % para pedidos anticipados, con o sin reserva.

### Encargado del restaurante

13. Consulta de todas las reservas del restaurante.
14. Confirmación de reservas pendientes.
15. Rechazo de reservas pendientes.
16. Modificación de reservas (fecha, horario, cantidad de comensales, estado).
17. Cancelación de reservas.
18. Control de disponibilidad de mesas por rango de fechas y rango de horas de atención.
19. Bloqueo y desbloqueo de mesas por mantenimiento, eventos o disponibilidad.
20. Gestión del menú semanal (alta, modificación y desactivación de platos, precios y disponibilidad).
21. Consulta de pedidos de clientes con trazabilidad de estado.
22. Consulta de señas registradas (pedidos anticipados con o sin reserva).
23. Administración de delivery y retiro de pedidos.
24. Registro de nuevos encargados del restaurante.
25. Modificación de datos de encargados del restaurante.
26. Desactivación de encargados del restaurante.
27. Gestión de permisos de usuarios y roles del sistema.
28. Supervisión general del sistema de reservas, pedidos y menú semanal.

### Administrador

29. Gestión de usuarios: alta, modificación, baja y asignación de roles (cliente, encargado, administrador).
30. Registro, modificación y desactivación de encargados del restaurante.
31. Gestión de permisos y roles del sistema.
32. Supervisión del sistema en su totalidad.

### Requerimientos compartidos

33. Cambio de estado de pedidos (pendiente, en preparación, listo, entregado, cancelado).
34. Cambio de estado de reservas (pendiente, confirmada, atendida, cancelada, no se presentó).
35. Establecimiento de un límite de ventas por plato del menú semanal según disponibilidad de insumos.
36. Visualización de calendario con días disponibles y días no disponibles para reservas.

## Regla de seña (50 %)

- Los pedidos anticipados, ya sea con reserva o sin reserva (para llevar o domicilio), requieren el pago de una seña equivalente al **50 % del total del pedido**.
- La seña se registra en el sistema al momento de confirmar el pedido y se consulta desde el perfil del cliente y desde el módulo del encargado.
- El encargado del restaurante puede consultar todas las señas registradas para control operativo y financiero.

## Tipos de pedido

| Tipo de pedido | Descripción                                                    | Requiere reserva | Seña 50 %       |
| -------------- | -------------------------------------------------------------- | ---------------- | --------------- |
| Con reserva    | Pedido asociado a una mesa reservada del restaurante.          | Sí               | Sí (anticipado) |
| Para llevar    | Pedido para recoger en el restaurante sin ocupar mesa.         | No               | Sí (anticipado) |
| Domicilio      | Pedido con delivery a domicilio gestionado por el restaurante. | No               | Sí (anticipado) |

## Requerimientos no funcionales

### Usabilidad

- Interfaz clara e intuitiva, navegable desde computadora y celular.
- Menús y permisos distintos según rol (cliente, encargado, administrador).
- Mensajes de error y confirmación en español, fáciles de entender.
- Acceso rápido a las funciones principales desde el menú de navegación.

### Disponibilidad

- El sistema debe estar disponible durante el horario de atención del restaurante.
- Copias de seguridad periódicas para evitar pérdida de reservas, pedidos y señas.

### Seguridad

- Acceso restringido por usuario y contraseña.
- Cada rol solo puede ejecutar las acciones que le corresponden (cliente no puede gestionar usuarios, stock ni señas).
- Protección de datos personales de clientes y usuarios del sistema.

### Rendimiento

- Tiempo de respuesta inferior a 3 segundos para cargar el menú semanal, calendario y pedidos.
- Soporte para uso simultáneo de varios usuarios sin lentitud notable.

### Compatibilidad

- Compatible con navegadores web modernos (Chrome, Firefox, Edge, Safari).
- Adaptable a distintos tamaños de pantalla (diseño responsivo).

### Mantenibilidad

- Código organizado y documentado para facilitar futuras modificaciones.
- Separación clara entre lógica de negocio, presentación y acceso a datos.

## Entregables del proyecto

1. Informe de análisis de la problemática (este documento y la etapa 1 de requerimientos).
2. Modelo entidad-relación y diagrama de clases del sistema.
3. Prototipo de interfaces (maquetas de pantallas).
4. Código fuente del sistema de gestión desarrollado.
5. Manual de usuario para clientes, encargados y administradores.
6. Manual técnico de instalación y mantenimiento del sistema.
7. Informe final del proyecto integrador.

## Formato de citas y referencias

Se seguirá el estilo APA 7.ª edición para citas textuales, parafraseadas y referencias bibliográficas utilizadas en el desarrollo del proyecto y sus informes.

---

*Archivo 03_etapa1_requerimientos.md actualizado correctamente dentro de entregables/ para el restaurante "Green en la Casa Blanca".*
