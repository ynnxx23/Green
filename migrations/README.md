# Migraciones de base de datos - Sistema de gestión de un restaurante

Este directorio contiene los archivos SQL de migración versionados.
Se aplican en orden numérico (001, 002, 003, ...) y una sola vez por entorno.

Ver documentacion/estrategia_migraciones.md para el procedimiento completo.

---

## Listado de migraciones

| Archivo | Descripción | RFs cubiertos |
|---------|-------------|---------------|
| `001_create_schema.sql` | Esquema inicial: roles, usuarios, áreas, mesas, horarios, bloques, reservas, pedidos, productos, detalle, intentos_login | Base |
| `002_migraciones_control.sql` | Tabla `migraciones_aplicadas` | Control |
| `003_datos_iniciales.sql` | Roles base (cliente=1, encargado=2, administrador=4) + usuario admin inicial | Base |
| `004_stock_y_disponibilidad.sql` | `stock_producto`, `disponibilidad_mesa` | RF-72 a RF-77 |
| `005_pedidos_y_senas.sql` | Campos en `pedidos` (tipo, seña, estado_seña, domicilio), `menu_semanal`, `menu_semanal_items`, `observaciones` en reservas | RF-78 a RF-80, RF-86 |
| `006_historial_y_reportes.sql` | Normalización, `historial_movimientos`, índices reportes | RF-97 a RF-100 |
| `007_datos_prueba.sql` | Datos de prueba (productos, mesas, usuarios) | Desarrollo |
| `008_recuperar_contrasena_y_perfil.sql` | `tokens_recuperacion`, campos perfil en `usuarios` | RF-85 (parcial) |
| `009_configuracion_y_limites_pedidos.sql` | `configuracion_sistema`, `contador_pedidos_sin_reserva` | RF-68 a RF-71, RF-101 |
| `010_cupones_descuento.sql` | `cupones_descuento`, `uso_cupon_cliente` | RF-88 |
| `011_calificaciones_preferencias_fidelidad.sql` | `calificaciones_platos`, `preferencias_cliente`, `reservas_recurrentes`, `puntos_fidelidad` | RF-90 a RF-93 |
| `012_delivery_repartidores.sql` | `zonas_delivery`, `repartidores`, campos delivery en `pedidos` | RF-81, RF-83 |
| `013_rf_pendientes.sql` | `estado_entrega`, `cierre_caja`, `platos_favoritos`, `notificaciones`, `sucursales`, `respaldos_bd`, `permisos`, `roles_permisos` | RF-84, 87, 89, 94–100, 102, 103, 105 |
| `MIGRACIONES_PENDIENTES.sql` | Script consolidado 009 → 012 | Consolidado |

---

## Orden de aplicación

```bash
# Aplicar en orden (ejemplo MySQL/MariaDB):
mysql -u usuario -p green_casa_blanca < migrations/001_create_schema.sql
mysql -u usuario -p green_casa_blanca < migrations/002_migraciones_control.sql
mysql -u usuario -p green_casa_blanca < migrations/003_datos_iniciales.sql
mysql -u usuario -p green_casa_blanca < migrations/004_stock_y_disponibilidad.sql
mysql -u usuario -p green_casa_blanca < migrations/005_pedidos_y_senas.sql
mysql -u usuario -p green_casa_blanca < migrations/006_historial_y_reportes.sql
mysql -u usuario -p green_casa_blanca < migrations/007_datos_prueba.sql
mysql -u usuario -p green_casa_blanca < migrations/008_recuperar_contrasena_y_perfil.sql
mysql -u usuario -p green_casa_blanca < migrations/009_configuracion_y_limites_pedidos.sql
mysql -u usuario -p green_casa_blanca < migrations/010_cupones_descuento.sql
mysql -u usuario -p green_casa_blanca < migrations/011_calificaciones_preferencias_fidelidad.sql
mysql -u usuario -p green_casa_blanca < migrations/012_delivery_repartidores.sql
mysql -u usuario -p green_casa_blanca < migrations/013_rf_pendientes.sql

# O usar el script consolidado para 009-012:
mysql -u usuario -p green_casa_blanca < migrations/MIGRACIONES_PENDIENTES.sql
```
