-- ============================================================================
-- Migracion 025 - Simplificacion y normalizacion de la base de datos
-- ============================================================================
-- Objetivo: reducir el esquema a lo que el sistema REALMENTE usa, eliminar
--           funcionalidades sin interfaz y normalizar las redundancias.
--
-- ELIMINADAS (14 tablas: funcionalidad sin interfaz o nunca invocada):
--   recuperar_contrasena  - recuperacion de contrasena sin UI
--   cierre_caja           - cierre de caja sin UI
--   respaldos_bd          - respaldos de BD sin UI
--   notificaciones        - notificaciones sin UI
--   calificaciones_platos - calificaciones de platos sin UI
--   preferencias_cliente  - preferencias y alergias sin UI
--   platos_favoritos    - favoritos sin UI
--   puntos_fidelidad      - puntos de fidelidad sin UI
--   reservas_recurrentes  - reservas periodicas sin UI
--   sucursales            - multi-sucursal sin UI (todas las FK en NULL)
--   permisos              - permisos granulares sin UI (0 filas)
--   roles_permisos        - asignacion rol-permiso sin UI (0 filas)
--   cupones_descuento     - cupones nunca aplicados a un pedido
--   uso_cupon_cliente     - uso de cupones nunca registrado
--
-- NORMALIZACIONES:
--   usuarios.id_sucursal   - eliminada (FK siempre NULL, tabla sucursales fuera)
--   pedidos.id_sucursal    - eliminada
--   reservas.id_sucursal   - eliminada
--   areas.id_sucursal      - eliminada
--   productos.categoria    - pasa a FK id_categoria sobre la nueva tabla
--                             categorias (evita duplicados e inconsistencias
--                             entre 'Principales' y 'Platos principales')
--
-- COLUMNAS MUERTAS (nunca leidas ni escritas por el codigo):
--   configuracion_sistema.id_config
--   contador_pedidos_sin_reserva.id_contador
--   intentos_login.id_intento
--
-- SE CONSERVAN (por uso real):
--   pedidos.id_mesero      - mesero responsable != autor del pedido
--                             (endpoint PUT /api/pedidos/{id}/mesero)
--   detalle_pedidos.precio_unitario - snapshot historico del precio
--   roles                  - JOIN para obtener el nombre del rol
--
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- 1. Eliminar tablas de funcionalidades sin interfaz
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS recuperar_contrasena;
DROP TABLE IF EXISTS cierre_caja;
DROP TABLE IF EXISTS respaldos_bd;
DROP TABLE IF EXISTS notificaciones;
DROP TABLE IF EXISTS calificaciones_platos;
DROP TABLE IF EXISTS preferencias_cliente;
DROP TABLE IF EXISTS platos_favoritos;
DROP TABLE IF EXISTS puntos_fidelidad;
DROP TABLE IF EXISTS reservas_recurrentes;
DROP TABLE IF EXISTS sucursales;
DROP TABLE IF EXISTS roles_permisos;
DROP TABLE IF EXISTS permisos;
DROP TABLE IF EXISTS uso_cupon_cliente;
DROP TABLE IF EXISTS cupones_descuento;

-- ---------------------------------------------------------------------------
-- 2. Eliminar columnas de sucursal (la tabla sucursales ya no existe)
--    Primero se retiran las claves foraneas que las referencian.
-- ---------------------------------------------------------------------------
ALTER TABLE usuarios DROP FOREIGN KEY fk_usuario_sucursal;
ALTER TABLE pedidos  DROP FOREIGN KEY fk_pedido_sucursal;
ALTER TABLE reservas DROP FOREIGN KEY fk_reserva_sucursal;

ALTER TABLE usuarios DROP COLUMN IF EXISTS id_sucursal;
ALTER TABLE pedidos  DROP COLUMN IF EXISTS id_sucursal;
ALTER TABLE reservas DROP COLUMN IF EXISTS id_sucursal;
ALTER TABLE areas    DROP COLUMN IF EXISTS id_sucursal;

-- ---------------------------------------------------------------------------
-- 3. Eliminar columnas muertas (nunca usadas por el codigo)
-- ---------------------------------------------------------------------------
ALTER TABLE configuracion_sistema        DROP COLUMN IF EXISTS id_config;
ALTER TABLE contador_pedidos_sin_reserva DROP COLUMN IF EXISTS id_contador;
ALTER TABLE intentos_login               DROP COLUMN IF EXISTS id_intento;

-- ---------------------------------------------------------------------------
-- 4. Normalizar productos.categoria -> tabla categorias
--    La tabla se crea con el mismo collation del resto del esquema
--    (utf8mb4_unicode_ci) para evitar mezclas en los JOIN.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre       VARCHAR(50) NOT NULL,
    activo       TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_categoria),
    UNIQUE KEY uk_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrar las categorias existentes de productos ('Principales' -> 'Platos principales')
UPDATE productos SET categoria = 'Platos principales' WHERE TRIM(categoria) = 'Principales';

INSERT INTO categorias (nombre)
SELECT DISTINCT TRIM(p.categoria)
FROM productos p
WHERE p.categoria IS NOT NULL AND TRIM(p.categoria) <> ''
  AND TRIM(p.categoria) COLLATE utf8mb4_unicode_ci NOT IN (SELECT nombre FROM categorias);

-- Categorias base que usa la interfaz
INSERT IGNORE INTO categorias (nombre) VALUES
    ('Entradas'),
    ('Platos principales'),
    ('Postres'),
    ('Bebidas'),
    ('Coctelería');

-- Columna foranea y reasignacion (antes de eliminar la columna textual)
ALTER TABLE productos
    ADD COLUMN id_categoria INT UNSIGNED NULL DEFAULT NULL AFTER nombre;

UPDATE productos p
JOIN categorias c ON c.nombre = TRIM(p.categoria) COLLATE utf8mb4_unicode_ci
SET p.id_categoria = c.id_categoria;

-- La columna categorica textual ya es redundante
ALTER TABLE productos DROP COLUMN IF EXISTS categoria;

ALTER TABLE productos
    ADD CONSTRAINT fk_productos_categoria FOREIGN KEY (id_categoria)
        REFERENCES categorias (id_categoria) ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
