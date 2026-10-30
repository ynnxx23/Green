# Estrategia de migraciones — Green en la Casa Blanca

> **Estado:** implementada (migraciones 001–013 aplicadas en desarrollo).  
> **Idioma:** español.  
> **Objetivo:** mantener el esquema de base de datos consistente en XAMPP, Docker Compose y hosting, de forma versionada y controlada, sin recrear tablas manualmente ni borrar datos existentes.

---

## 1. Principios

- El esquema se define mediante archivos SQL de migración versionados en `migrations/`.
- Cada migración tiene un número secuencial y una descripción clara.
- Se lleva una tabla de control `migraciones_aplicadas (version, fecha_aplicacion)` para saber qué migraciones ya se ejecutaron y evitar reejecutarlas.
- Las migraciones se aplican en orden numérico, una sola vez por entorno.
- **No se borra ni reinicializa automáticamente** la base de datos al iniciar la aplicación en un entorno.
- Las migraciones son compatibles con MariaDB 10.11 y MySQL 8.0 (SQL estándar, tipos comunes).
- Los datos existentes se conservan al aplicar migraciones (no son destructivas salvo que se documente explícitamente).

---

## 2. Estructura de la carpeta migrations/

```
migrations/
├── 001_create_schema.sql
├── 002_migraciones_control.sql
├── 003_datos_iniciales.sql
├── 004_stock_y_disponibilidad.sql
├── 005_pedidos_y_senas.sql
├── 006_historial_y_reportes.sql
├── 007_datos_prueba.sql
├── 008_recuperar_contrasena_y_perfil.sql
├── 009_configuracion_y_limites_pedidos.sql
├── 010_cupones_descuento.sql
├── 011_calificaciones_preferencias_fidelidad.sql
├── 012_delivery_repartidores.sql
├── 013_rf_pendientes.sql
├── MIGRACIONES_PENDIENTES.sql   (consolidado 009–012)
└── README.md
```

Cada archivo SQL contiene las sentencias `CREATE TABLE`, `ALTER TABLE`, `INSERT` de datos iniciales, etc., necesarias para esa versión del esquema.

---

## 3. Tabla de control de migraciones

```sql
CREATE TABLE IF NOT EXISTS migraciones_aplicadas (
    version VARCHAR(50) NOT NULL PRIMARY KEY,
    fecha_aplicacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Antes de aplicar una migración `NNN_xxx.sql`, el sistema (o el instalador) verifica si `NNN` ya existe en `migraciones_aplicadas`. Si existe, se salta. Si no, se ejecuta y se registra.

---

## 4. Flujo de aplicación de migraciones

1. El instalador o script de inicialización lee la carpeta `migrations/` ordenada por número.
2. Para cada archivo, consulta `migraciones_aplicadas`.
3. Si la versión no está registrada, ejecuta el SQL y registra la versión con fecha actual.
4. Si ocurre un error, se detiene y se informa (no se aplica la siguiente sin resolver el error).
5. Una vez aplicadas todas las pendientes, el esquema está al día.

Este flujo es igual en XAMPP, Docker y hosting (solo cambia cómo se ejecuta el SQL: phpMyAdmin, consola, panel de control, etc.).

---

## 5. Migraciones no destructivas

- No se permiten migraciones que borren tablas o columnas con datos sin un plan explícito de migración de datos y aprobación documentada.
- Se prefiere `ALTER TABLE ... ADD COLUMN` sobre recrear tablas.
- Se usa `CREATE TABLE IF NOT EXISTS` y `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` cuando el motor lo soporte, para mayor idempotencia.

---

## 6. Compatibilidad MariaDB / MySQL

- Usar tipos de datos comunes: `INT`, `VARCHAR`, `DATE`, `TIME`, `DATETIME`, `TINYINT`, `TEXT`, `DECIMAL`, `ENUM`, `JSON`.
- Evitar funciones propias de un solo motor.
- Versión de referencia: MariaDB 10.11 / MySQL 8.0.
- Documentar cualquier diferencia admitida entre ambos motores (si surge).

---

## 7. Datos iniciales (semillas)

Solo datos mínimos de arranque, identificados como datos de entorno de desarrollo/pruebas:

- Rol `cliente` (id_rol=1).
- Rol `encargado` (id_rol=2).
- Rol `administrador` (id_rol=4).
- Usuario administrador inicial (contraseña hasheada con bcrypt; valor de ejemplo, cambiar en producción).

No se crean áreas, mesas, productos, reservas ni pedidos como parte del esquema base.

---

## 8. Versionado y documentación de cambios de esquema

- Cada migración nueva se registra en `documentacion/desarrollo/` o en el `README.md` del proyecto.
- Cuando se modifique el modelo de datos, se debe:
  1. Crear un nuevo archivo de migración `NNN_descripcion.sql`.
  2. Actualizar `documentacion/desarrollo/03_base_de_datos.md` si el modelo cambia.
  3. Actualizar los DAO y servicios afectados.
  4. Documentar la decisión antes de aplicar (principio: sin cambios improvisados).

---

## 9. Listado de migraciones implementadas

| Archivo | Descripción | RFs cubiertos |
|---------|-------------|---------------|
| `001_create_schema.sql` | Esquema inicial: roles, usuarios, áreas, mesas, horarios, bloques, reservas, pedidos, productos, detalle, intentos_login | Base |
| `002_migraciones_control.sql` | Tabla `migraciones_aplicadas` | Control |
| `003_datos_iniciales.sql` | Roles base + usuario admin inicial | Base |
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

---

## 10. Ejemplo de flujo en cada entorno

| Entorno | Cómo se aplican las migraciones |
|---------|---------------------------------|
| XAMPP | Abrir phpMyAdmin, seleccionar BD `green_casa_blanca`, pestaña SQL, ejecutar cada archivo en orden (001 → 013). O consola MySQL de XAMPP. |
| Docker | `docker compose exec web mysql -u user -p db < migrations/001_create_schema.sql` (repetir para cada archivo). O script de inicialización automático. |
| Hosting | phpMyAdmin del proveedor, consola SSH si está disponible, o panel de control. Subir archivos SQL y ejecutar en orden 001 → 013. |

**Script consolidado:** Para aplicar todas las migraciones nuevas de una sola vez, usar `migrations/MIGRACIONES_PENDIENTES.sql` (contiene 009 a 012 combinadas). Luego aplicar 013 por separado.

---

## 11. Respaldo y recuperación vinculado a migraciones

- Antes de aplicar una migración en producción, hacer un dump de la base de datos (`mysqldump`).
- Si una migración falla, restaurar el dump y revisar el error antes de reintentar.
- Las migraciones no borran datos; en caso de problema, se restaura desde respaldo y se corrige el archivo SQL.
- Tabla `respaldos_bd` (migración 013) registra historial de respaldos realizados.
