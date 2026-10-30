# 01. Análisis de la problemática

## Datos del proyecto

**Nombre del proyecto:** Sistema de gestión de un restaurante — Green en la Casa Blanca.

**Institución:** EPET Nº 7 "Vicente Arcadio Salemi", curso 6 IV CST, ciclo lectivo 2026, Laboratorio de Desarrollo de Aplicaciones III y Prácticas Pre Profesionalizantes.

## Descripción de la problemática

El restaurante "Green en la Casa Blanca" presenta dificultades para organizar las reservas de mesas, registrar los pedidos y controlar su estado de forma ordenada. Actualmente el control se hace de manera manual o desordenada, lo que genera demoras en la atención, mesas ocupadas sin reserva registrada, pedidos extraviados o mal anotados, falta de trazabilidad sobre el estado de cada orden y errores al momento de facturar o cobrar. Esto afecta la experiencia del cliente y la operación del local.

## Identificación del mundo del problema

### Quiénes intervienen

- **Cliente:** realiza reservas, consulta la carta y efectúa pedidos.
- **Mesero/a:** toma pedidos en sala, anota productos y cantidades, lleva el control de las mesas.
- **Cocina:** recibe los pedidos, prepara los platos y actualiza el estado de cada orden.
- **Caja / Administrador:** gestiona usuarios, cobros, stock, disponibilidad de mesas y horarios de atención.
- **Dueño / Gerente:** supervisa reservas, pedidos, estados y reportes del local.

### Qué se gestiona

- Reservas de mesas (fecha, horario, cantidad de comensales, estado).
- Carta de productos (platos, bebidas, precios, disponibilidad).
- Pedidos (productos, cantidades, estado, mesa asociada, cliente).
- Stock de insumos y límite de ventas por plato.
- Usuarios y roles del sistema.
- Mesas del restaurante (disponibles, ocupadas, bloqueadas).

### Qué falla hoy

- No hay un calendario unificado de reservas; se anotan en cuadernos o agendas propias.
- No se controlan días disponibles o no disponibles de forma sistemática.
- Los pedidos se toman a mano y pueden perderse o confundirse entre mesas.
- No existe estado visible del pedido (pendiente, en preparación, listo, entregado, cancelado).
- No se controla el stock ni se limita la venta de un plato cuando se agotan los insumos.
- No hay gestión de usuarios con roles diferenciados (cliente, mesero, administrador).
- No se pueden bloquear mesas por mantenimiento o eventos especiales.

## Objetivo general

Desarrollar un sistema de gestión que permita organizar reservas de mesas, registrar pedidos y controlar su estado en tiempo real, mejorando la atención y reduciendo errores en el restaurante "Green en la Casa Blanca".

## Objetivos específicos

1. Implementar módulo de registro e inicio de sesión con tres roles: cliente, mesero y administrador.
2. Permitir a los clientes ver la carta de productos disponible.
3. Gestionar reservas de mesas con calendario por fecha y días disponibles o no disponibles.
4. Registrar pedidos con productos y cantidades, asociados a una mesa y un cliente.
5. Consultar reservas y pedidos propios desde el perfil de cliente.
6. Cancelar reservas desde el perfil del cliente.
7. Gestionar el stock de productos y establecer un límite de ventas por plato.
8. Abrir disponibilidad de mesas por rango de fechas y horario de atención.
9. Cambiar los estados de pedidos y reservas (pendiente, confirmado, en preparación, listo, entregado, cancelado, etc.).
10. Gestionar usuarios del sistema (alta, modificación, baja, asignación de roles).
11. Bloquear mesas por mantenimiento, eventos o disponibilidad.

## Alcance del sistema

El sistema cubre la gestión operativa del restaurante en lo referente a:

- Autenticación y control de acceso por roles.
- Consulta de carta de productos para clientes.
- Reserva de mesas con calendario y validación de disponibilidad.
- Registro y seguimiento de pedidos con cambio de estados.
- Gestión de stock y límite de ventas por plato.
- Administración de usuarios, mesas y horarios de atención.
- Consulta de mis reservas y mis pedidos por parte del cliente.

Quedan fuera del alcance de esta etapa:

- Pagos en línea o integración con pasarelas de pago.
- Facturación electrónica vinculada a AFIP u otro ente fiscal.
- Integración con impresoras de cocina o comandas físicas automáticas.
- Gestión de proveedores y compras de insumos.
- Módulo de delivery o envíos a domicilio.

## Limitaciones del sistema

- Requiere conexión a internet para funcionar (aplicación web).
- El stock se controla dentro del sistema; no se sincroniza con proveedores externos.
- Las reservas no implican pago por adelantado ni garantía con tarjeta de crédito.
- El sistema depende de que el personal cargue correctamente los pedidos y estados en tiempo real.
- No se contemplan múltiples sucursales en esta versión; opera para un solo local.
- La disponibilidad de mesas se define por el administrador; el sistema no predice demanda automáticamente.

---

*Archivo 02_analisis_problematica.md creado correctamente dentro de entregables/.*