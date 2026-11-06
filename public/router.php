<?php
declare(strict_types=1);

/**
 * Router para el servidor incorporado de PHP.
 * Redirige todas las peticiones al front controller index.php,
 * excepto para archivos estáticos existentes.
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$rutaFisica = __DIR__ . $uri;

// Leer el cuerpo de la petición antes de incluir index.php
// (el servidor incorporado de PHP puede truncar php://input al usar router)
$rawInput = @file_get_contents('php://input');
if ($rawInput !== false && $rawInput !== '') {
    $_SERVER['RAW_INPUT'] = $rawInput;
}

// Si el archivo existe físicamente, servirlo directamente
if ($uri !== '/' && is_file($rutaFisica)) {
    return false;
}

// Para directorios existentes, servir index.html/index.php si existe
if ($uri !== '/' && is_dir($rutaFisica)) {
    $index = $rutaFisica . '/index.php';
    if (is_file($index)) {
        require $index;
        return true;
    }
}

// Todo lo demás va al front controller
require __DIR__ . '/index.php';
return true;
