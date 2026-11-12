-- ---------------------------------------------------------------------------
-- Migración 023: Corregir doble codificación UTF-8 en datos de texto
-- Corrige strings como "CafÃ© expreso" -> "Café expreso"
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Corregir productos (nombre, categoria)
UPDATE productos 
SET nombre = CONVERT(CAST(CONVERT(nombre USING latin1) AS BINARY) USING utf8mb4)
WHERE nombre LIKE '%Ã©%' OR nombre LIKE '%Ã¡%' OR nombre LIKE '%Ã³%' OR nombre LIKE '%Ãº%' OR nombre LIKE '%Ã±%' OR nombre LIKE '%Ã¢%' OR nombre LIKE '%Ãª%' OR nombre LIKE '%Ã­%' OR nombre LIKE '%Ã¿%' OR nombre LIKE '%Ã¶%' OR nombre LIKE '%Ã¼%';

UPDATE productos 
SET categoria = CONVERT(CAST(CONVERT(categoria USING latin1) AS BINARY) USING utf8mb4)
WHERE categoria LIKE '%Ã©%' OR categoria LIKE '%Ã¡%' OR categoria LIKE '%Ã³%' OR categoria LIKE '%Ãº%' OR categoria LIKE '%Ã±%' OR categoria LIKE '%Ã¢%' OR categoria LIKE '%Ãª%' OR categoria LIKE '%Ã­%' OR categoria LIKE '%Ã¿%' OR categoria LIKE '%Ã¶%' OR categoria LIKE '%Ã¼%';

-- Corregir usuarios (nombre)
UPDATE usuarios 
SET nombre = CONVERT(CAST(CONVERT(nombre USING latin1) AS BINARY) USING utf8mb4)
WHERE nombre LIKE '%Ã©%' OR nombre LIKE '%Ã¡%' OR nombre LIKE '%Ã³%' OR nombre LIKE '%Ãº%' OR nombre LIKE '%Ã±%' OR nombre LIKE '%Ã¢%' OR nombre LIKE '%Ãª%' OR nombre LIKE '%Ã­%' OR nombre LIKE '%Ã¿%' OR nombre LIKE '%Ã¶%' OR nombre LIKE '%Ã¼%';

-- Corregir zonas_delivery (nombre, descripcion)
UPDATE zonas_delivery 
SET nombre = CONVERT(CAST(CONVERT(nombre USING latin1) AS BINARY) USING utf8mb4)
WHERE nombre LIKE '%Ã©%' OR nombre LIKE '%Ã¡%' OR nombre LIKE '%Ã³%' OR nombre LIKE '%Ãº%' OR nombre LIKE '%Ã±%' OR nombre LIKE '%Ã¢%' OR nombre LIKE '%Ãª%' OR nombre LIKE '%Ã­%' OR nombre LIKE '%Ã¿%' OR nombre LIKE '%Ã¶%' OR nombre LIKE '%Ã¼%';

UPDATE zonas_delivery 
SET descripcion = CONVERT(CAST(CONVERT(descripcion USING latin1) AS BINARY) USING utf8mb4)
WHERE descripcion LIKE '%Ã©%' OR descripcion LIKE '%Ã¡%' OR descripcion LIKE '%Ã³%' OR descripcion LIKE '%Ãº%' OR descripcion LIKE '%Ã±%' OR descripcion LIKE '%Ã¢%' OR descripcion LIKE '%Ãª%' OR descripcion LIKE '%Ã­%' OR descripcion LIKE '%Ã¿%' OR descripcion LIKE '%Ã¶%' OR descripcion LIKE '%Ã¼%';

-- Corregir cupones_descuento (codigo)
UPDATE cupones_descuento 
SET codigo = CONVERT(CAST(CONVERT(codigo USING latin1) AS BINARY) USING utf8mb4)
WHERE codigo LIKE '%Ã©%' OR codigo LIKE '%Ã¡%' OR codigo LIKE '%Ã³%' OR codigo LIKE '%Ãº%' OR codigo LIKE '%Ã±%' OR codigo LIKE '%Ã¢%' OR codigo LIKE '%Ãª%' OR codigo LIKE '%Ã­%' OR codigo LIKE '%Ã¿%' OR codigo LIKE '%Ã¶%' OR codigo LIKE '%Ã¼%';

SELECT 'Encoding fix applied successfully' AS mensaje;
