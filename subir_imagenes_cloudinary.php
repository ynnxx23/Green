<?php

declare(strict_types=1);

require_once __DIR__ . '/public/index.php';

use Restaurante\Service\ServicioDeCloudinary;
use Restaurante\Dao\ProductoDao;
use Restaurante\Database\Conexion;

echo "=== Subir imágenes a Cloudinary y actualizar productos ===\n\n";

// Verificar que Cloudinary esté configurado
$cloudinary = new ServicioDeCloudinary();
if (!$cloudinary->config->estaConfigurada()) {
    echo "ERROR: Cloudinary no está configurada. Revisa las variables de entorno CLOUDINARY_*\n";
    exit(1);
}
echo "✓ Cloudinary configurada correctamente\n";

// Obtener archivos de imagen de la carpeta /imagenes
$carpetaImagenes = __DIR__ . '/imagenes';
$archivos = scandir($carpetaImagenes);
$imagenes = array_filter($archivos, function($archivo) {
    $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
});

echo "Se encontraron " . count($imagenes) . " imágenes en la carpeta /imagenes\n\n";

// Mapeo de nombres de archivo a nombres de productos
$mapeoProductos = [
    'lomito_papas.jpeg' => 'Lomo saltado',
    'milanesanapolitanaypure.jpeg' => 'Milanesa napolitana',
    'arroz_con_mariscos.jpeg' => 'Arroz con mariscos',
    'lasaña_vegetal.jpeg' => 'Lasaña vegetal',
    'salmon_verduras.jpeg' => 'Salmón a la plancha con verduras',
    'pasta_bolognesa.jpeg' => 'Pasta a la bolognesa',
    'risotto_setas.jpeg' => 'Risotto de setas',
    'chuletas_cerdo.jpeg' => 'Chuletas de cerdo con puré',
    'pollo_curry.jpeg' => 'Pollo al curry con arroz basmati',
    'tiramisu.jpeg' => 'Tiramisú',
    'flan_cafe.jpeg' => 'Flan de café',
    'cheesecake_frutos_rojos.jpeg' => 'Cheesecake de frutos rojos',
    'helado_artesanal.jpeg' => 'Helado artesanal',
    'brownie_chocolate.jpeg' => 'Brownie de chocolate',
    'mousse_chocolate.jpeg' => 'Mousse de chocolate',
    'mojito_clasico.jpeg' => 'Mojito clásico',
    'pinacolada.jpeg' => 'Piña colada',
    'jugo_naranja.jpeg' => 'Jugo natural de naranja',
    'agua_mineral.jpeg' => 'Agua mineral',
    'vino_tinto.jpeg' => 'Vino tinto (copa)',
    'cerveza_artesanal.jpeg' => 'Cerveza artesanal',
    'batido_fresa.jpeg' => 'Batido de fresa',
    'te_helado_durazno.jpeg' => 'Té helado de durazno',
    'cafe_expreso.jpeg' => 'Café expreso',
    'bruschetta.jpeg' => 'Bruschetta de tomate y albahaca',
    'carpaccio_res.jpeg' => 'Carpaccio de res',
    'croquetas_jamon.jpeg' => 'Croquetas de jamón',
    'calamares_romana.jpeg' => 'Calamares a la romana',
    'nachos_queso.jpeg' => 'Nachos con queso y guacamole',
    'alitas_bufalo.jpeg' => 'Alitas al estilo bufalo',
];

echo "Iniciando subida de imágenes a Cloudinary...\n\n";

$subidasExitosas = 0;
$subidasFallidas = 0;

foreach ($imagenes as $archivo) {
    $nombreArchivo = pathinfo($archivo, PATHINFO_FILENAME);
    $nombreProducto = $mapeoProductos[$nombreArchivo] ?? null;
    
    if (!$nombreProducto) {
        echo "⚠️  Sin mapeo para: $archivo (saltando)\n";
        continue;
    }
    
    $rutaCompleta = $carpetaImagenes . '/' . $archivo;
    
    try {
        $resultado = $cloudinary->subirImagen($rutaCompleta, $nombreArchivo);
        
        if ($resultado['exito']) {
            echo "✓ Subido: $nombreProducto -> $nombreArchivo\n";
            
            // Actualizar en la base de datos
            $pdo = Conexion::obtener();
            $stmt = $pdo->prepare('UPDATE productos SET imagen_url = ?, imagen_public_id = ? WHERE nombre = ?');
            $stmt->execute([
                $resultado['url'],
                $resultado['public_id'],
                $nombreProducto
            ]);
            
            if ($stmt->rowCount() > 0) {
                echo "  → Base de datos actualizada: $nombreProducto\n";
                $subidasExitosas++;
            } else {
                echo "  ⚠️  Producto no encontrado en BD: $nombreProducto\n";
                $subidasFallidas++;
            }
        } else {
            echo "✗ Error subiendo $archivo: " . $resultado['mensaje'] . "\n";
            $subidasFallidas++;
        }
    } catch (Exception $e) {
        echo "✗ Error procesando $archivo: " . $e->getMessage() . "\n";
        $subidasFallidas++;
    }
}

echo "\n=== Resumen ===\n";
echo "✓ Subidas exitosas: $subidasExitosas\n";
echo "✗ Fallidas: $subidasFallidas\n";
echo "\nListo. Las imágenes están en Cloudinary y los productos actualizados en la BD.\n";
