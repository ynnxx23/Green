# 06 - Plan de Mantenimiento

**Sistema:** Green en la Casa Blanca - Sistema de Gestión de Restaurante  
**Documento:** 06_plan_mantenimiento.md  
**Versión:** 1.0  
**Estado:** Creado y confirmado

---

## 1. Objetivo del plan de mantenimiento

Garantizar la disponibilidad, integridad, seguridad y buen funcionamiento continuo del sistema de gestión del restaurante "Green en la Casa Blanca", minimizando interrupciones en el servicio de reservas, pedidos y administración, y asegurando que la plataforma se mantenga alineada con las necesidades operativas del negocio y la normativa vigente.

---

## 2. Tipos de mantenimiento

### 2.1 Mantenimiento correctivo

Corregir errores y fallas reportadas en el funcionamiento del sistema.

- Corrección de errores en el módulo de reservas (conflictos de horarios, duplicados, cancelaciones).
- Corrección de errores en el módulo de pedidos (cálculo de totales, estados de pedido, asignación a mesa).
- Corrección de errores en el módulo de login (autenticación, recuperación de contraseña, bloqueo de cuentas).
- Corrección de fallas de interfaz y usabilidad reportadas por usuarios.

### 2.2 Mantenimiento adaptativo

Adaptar el sistema a cambios del entorno operativo y normativo.

- Ajustes por cambios de normativa sanitaria, fiscal o de protección de datos.
- Inclusión de nuevas categorías de producto (menú, bebidas, opciones dietéticas).
- Actualización de horarios de funcionamiento del restaurante (temporada alta/baja, días festivos).
- Adaptación a cambios en proveedores o métodos de pago.

### 2.3 Mantenimiento preventivo

Evitar fallas antes de que ocurran mediante revisiones y acciones programadas.

- Copias de seguridad (backup) de la base de datos.
- Revisión de logs de intentos de login (detectar accesos no autorizados o intentos repetidos).
- Actualización de contraseñas de administrador según política de seguridad.
- Revisión de integridad de la base de datos y rendimiento de consultas.

### 2.4 Mantenimiento evolutivo

Evolucionar el sistema con nuevas funcionalidades y mejoras.

- Creación y gestión de nuevos roles de usuario.
- Integración con pasarelas de pagos en línea.
- Implementación de notificaciones por WhatsApp (confirmación de reservas, estado de pedidos).
- Mejoras en reportes y estadísticas de ventas.

---

## 3. Tareas periódicas

### 3.1 Tareas diarias

- Revisión de reservas pendientes vencidas (reservas no confirmadas o no presentadas pasado el horario).
- Revisión de pedidos pendientes en cocina y salón.
- Verificación de backups diarios de la base de datos.

### 3.2 Tareas semanales

- Revisión de stock bajo o límite alcanzado de ingredientes y productos.
- Revisión de logs de intentos de login fallidos.
- Actualización de horarios de funcionamiento si hubo cambios operativos.
- Revisión de usuarios inactivos durante más de 30 días.

### 3.3 Tareas mensuales

- Copia de seguridad completa y verificación de restauración de la base de datos.
- Actualización de contraseñas de administrador según política de seguridad.
- Revisión de desempeño del sistema y tiempos de respuesta.
- Análisis de incidencias del mes y reporte de mejoras.

---

## 4. Roles responsables

| Rol | Responsabilidades principales |
|-----|------------------------------|
| **Administrador del sistema** | Gestión de usuarios, roles, permisos, backups, seguridad, logs, actualizaciones del sistema y control de versiones. |
| **Encargado del restaurante** | Gestión de stock, disponibilidad de mesas, horarios, categorías de producto, supervisión de pedidos y reportes operativos. |
| **Soporte técnico** | Atención de incidencias, mantenimiento correctivo y preventivo, instalación de parches, soporte a usuarios finales. |

---

## 5. Procedimiento para reportar y corregir incidencias

1. **Detección:** Cualquier usuario detecta una incidencia y la comunica al soporte técnico o al encargado del restaurante.
2. **Registro:** La incidencia se registra en el sistema de seguimiento (ticket) con: fecha, reportero, descripción, módulo afectado, prioridad (baja, media, alta, crítica) y pasos para reproducir.
3. **Clasificación:** El soporte técnico clasifica la incidencia según tipo (correctivo, adaptativo, preventivo, evolutivo) y la asigna al rol responsable.
4. **Diagnóstico:** El responsable analiza la causa raíz y propone una solución.
5. **Corrección:** Se implementa la corrección en un entorno de pruebas y se valida.
6. **Despliegue:** Se aplica la corrección en producción siguiendo el procedimiento de control de cambios.
7. **Cierre:** El reportero valida que la incidencia quedó resuelta y el ticket se cierra con registro de la solución aplicada.

---

## 6. Versiones y control de cambios

| Versión | Fecha | Autor | Descripción de cambios |
|---------|-------|-------|------------------------|
| 1.0 | 29/10/2026 | Equipo de integración | Versión inicial del plan de mantenimiento. |

**Control de cambios:** Cualquier modificación a este documento debe ser registrada en la tabla anterior, indicando número de versión, fecha, autor y descripción del cambio. El administrador del sistema es responsable de aprobar y versionar este documento.

---

**Confirmación:** Este archivo `06_plan_mantenimiento.md` ha sido creado exitosamente en la carpeta `documentacion_integrador/`.
