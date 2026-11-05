<?php

declare(strict_types=1);

require_once __DIR__ . '/index.php';

use Restaurante\Database\Conexion;
use Restaurante\Service\ServicioDeAutenticacion;

// Solo para administrador
$authMiddleware = new AuthMiddleware(new ServicioDeAutenticacion(new \Restaurante\Dao\UsuarioDao(), new \Restaurante\Dao\IntentosLoginDao(), new \Restaurante\Dao\RecuperarContrasenaDao()));
$authMiddleware->requiereMeseroOAdministrador();

$pdo = Conexion::obtener();

// Carpeta de imágenes (ya están en el repositorio)
$carpetaImagenes = __DIR__ . '/assets/img/platos/';

if (!is_dir($carpetaImagenes)) {
    echo json_encode(['exito' => false, 'mensaje' => 'No se encontró la carpeta de imágenes.']);
    exit;
}

// Obtener archivos de imagen
$archivos = scandir($carpetaImagenes);
$imagenes = array_filter($archivos, function($archivo) {
    $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif']);
});

// Mapeo de nombre de archivo a nombre de producto
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
    'cheesecake_frutilla.jpeg' => 'Cheesecake de frutos rojos',
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

$exitosos = 0;
$fallidos = 0;
$mensajes = [];

foreach ($imagenes as $archivo) {
    $nombreArchivo = pathinfo($archivo, PATHINFO_FILENAME);
    $nombreProducto = $mapeoProductos[$nombreArchivo] ?? null;

    if (!$nombreProducto) {
        $fallidos++;
        continue;
    }

    // Buscar el producto por nombre
    $stmt = $pdo->prepare('SELECT id_producto FROM productos WHERE nombre = ? LIMIT 1');
    $stmt->execute([$nombreProducto]);
    $producto = $stmt->fetch();

    if (!$producto) {
        $fallidos++;
        continue;
    }

    // Actualizar el producto con la URL de la imagen
    $urlImagen = '/assets/img/platos/' . $archivo;
    $stmt = $pdo->prepare('UPDATE productos SET imagen_url = ? WHERE id_producto = ?');
    $stmt->execute([$urlImagen, $producto['id_producto']]);

    if ($stmt->rowCount() > 0) {
        $exitosos++;
        $mensajes[] = "✓ $nombreProducto → $archivo";
    } else {
        $fallidos++;
    }
}

echo "<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<title>Subir Imágenes - Completado</title>
<link rel='stylesheet' href='assets/css/main.css'>
<link rel='stylesheet' href='assets/css/componentes.css'>
</head>
<body>
<nav>
  <div class='nav-wrap'>
    <a href='index.html' class='logo nav-brand'><img src='assets/icons/logo.png' width='40' height='40'><span class='logo-text'>GREEN</span></a>
    <ul class='nav-links'><li><a href='index.html'>Inicio</a></li><li><a href='carta.html'>Carta</a></li><li><a href='gestion-productos.html'>Gestión Productos</a></li></ul>
    <div class='nav-acciones'><div id='nav-sesion'></div></div>
  </div>
</nav>
<div class='panel-card' style='max-width: 800px; margin: 3rem auto; padding: 2rem;'>
  <h2>✅ Imágenes subidas correctamente</h2>
  <p><strong>Exitosas:</strong> $exitosos</p>
  <p><strong>Fallidas:</strong> $fallidos</p>
  <h3>Detalle:</h3>
  <ul>";
foreach ($mensajes as $msg) {
    echo "      <li>$msg</li>\n";
}
echo "</ul>
  <p><a href='index.html' class='btn btn-primary'>Volver al Inicio</a> <a href='gestion-productos.html' class='btn btn-secondary'>Gestión Productos</a></p>
</div>
</body>
</html>";
