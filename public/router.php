<?php
// Router simple para el servidor integrado de PHP
// Prioriza archivos HTML estáticos del frontend sobre el front controller del backend

$root = __DIR__;
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalizar URI raíz
if ($uri === '/' || $uri === '') {
    $archivoIndex = $root . '/index.html';
    if (file_exists($archivoIndex)) {
        $_SERVER['SCRIPT_NAME'] = '/index.html';
        $_SERVER['SCRIPT_FILENAME'] = $archivoIndex;
        require $archivoIndex;
        return true;
    }
}

// Si el URI empieza con /api/, enviar al front controller del backend
if (str_starts_with($uri, '/api/')) {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
    require $root . '/index.php';
    return true;
}

// Si el archivo existe físicamente, servirlo directamente (HTML, CSS, JS, imágenes, etc.)
$archivo = $root . $uri;
if (file_exists($archivo) && !is_dir($archivo)) {
    return false;
}

// Si es un directorio, buscar index.html primero, luego index.php
if (is_dir($archivo)) {
    if (file_exists($archivo . '/index.html')) {
        $_SERVER['SCRIPT_NAME'] = rtrim($uri, '/') . '/index.html';
        $_SERVER['SCRIPT_FILENAME'] = $archivo . '/index.html';
        require $archivo . '/index.html';
        return true;
    }
    if (file_exists($archivo . '/index.php')) {
        $_SERVER['SCRIPT_NAME'] = rtrim($uri, '/') . '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = $archivo . '/index.php';
        require $archivo . '/index.php';
        return true;
    }
}

// Ruta no encontrada
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'exito' => false,
    'mensaje' => 'Recurso no encontrado.',
    'codigo' => 404,
], JSON_UNESCAPED_UNICODE);
return true;
