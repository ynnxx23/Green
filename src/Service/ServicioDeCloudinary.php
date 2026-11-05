<?php

declare(strict_types=1);

namespace Restaurante\Service;

use Restaurante\Config\CloudinaryConfig;

class ServicioDeCloudinary
{
    private CloudinaryConfig $config;

    public function __construct(?CloudinaryConfig $config = null)
    {
        $this->config = $config ?? new CloudinaryConfig();
    }

    /**
     * Sube una imagen temporal a Cloudinary.
     *
     * @param string $rutaArchivoTemporal ruta del archivo en tmp (ej. $_FILES['imagen']['tmp_name'])
     * @param string $nombreOriginal      nombre original del archivo
     * @param string|null $publicId       id público opcional; si no se pasa, Cloudinary genera uno
     * @return array{'exito': bool, 'url'?: string, 'public_id'?: string, 'ancho'?: int, 'alto'?: int, 'mensaje'?: string}
     */
    public function subirImagen(string $rutaArchivoTemporal, string $nombreOriginal, ?string $publicId = null): array
    {
        if (!$this->config->estaConfigurada()) {
            return [
                'exito' => false,
                'mensaje' => 'Cloudinary no está configurada. Revisa CLOUDINARY_* en .env.',
            ];
        }

        if (!file_exists($rutaArchivoTemporal) || !is_uploaded_file($rutaArchivoTemporal)) {
            return [
                'exito' => false,
                'mensaje' => 'El archivo temporal no existe o no es una subida válida.',
            ];
        }

        $mime = $this->detectarMime($rutaArchivoTemporal);

        $post = [
            'file' => new \CURLFile($rutaArchivoTemporal, $mime, $nombreOriginal),
            'folder' => $this->config->folder,
        ];

        if ($publicId !== null && $publicId !== '') {
            $post['public_id'] = $publicId;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->config->urlBaseUpload(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_USERPWD => "{$this->config->apiKey}:{$this->config->apiSecret}",
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_TIMEOUT => 60,
        ]);

        $respuesta = curl_exec($ch);
        $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false) {
            return [
                'exito' => false,
                'mensaje' => 'Error de red al subir a Cloudinary: ' . $error,
            ];
        }

        $datos = json_decode($respuesta, true);

        if ($codigoHttp >= 400 || !is_array($datos) || !isset($datos['secure_url'])) {
            $mensaje = is_array($datos) && isset($datos['error']['message'])
                ? $datos['error']['message']
                : 'Error desconocido al subir a Cloudinary (HTTP ' . $codigoHttp . ').';

            return [
                'exito' => false,
                'mensaje' => $mensaje,
            ];
        }

        return [
            'exito' => true,
            'url' => $datos['secure_url'] ?? null,
            'public_id' => $datos['public_id'] ?? null,
            'ancho' => $datos['width'] ?? null,
            'alto' => $datos['height'] ?? null,
        ];
    }

    /**
     * Borra una imagen de Cloudinary por su public_id.
     *
     * @return bool true si se borró (o ya no existía), false si hubo error.
     */
    public function borrarImagen(string $publicId): bool
    {
        if (!$this->config->estaConfigurada()) {
            return false;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->config->urlBaseDestroy(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'public_id' => $publicId,
            ],
            CURLOPT_USERPWD => "{$this->config->apiKey}:{$this->config->apiSecret}",
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $respuesta = curl_exec($ch);
        $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($respuesta === false) {
            return false;
        }

        $datos = json_decode($respuesta, true);

        return is_array($datos)
            && $codigoHttp < 400
            && isset($datos['result'])
            && in_array($datos['result'], ['ok', 'not_found'], true);
    }

    /**
     * Construye una URL con transformación (recorte, ancho/alto) sin llamar a la API.
     */
    public function urlTransformada(string $publicId, ?int $ancho = null, ?int $alto = null): string
    {
        $transformaciones = [];

        if ($ancho !== null) {
            $transformaciones[] = "w_{$ancho}";
        }
        if ($alto !== null) {
            $transformaciones[] = "h_{$alto}";
        }
        if ($ancho !== null || $alto !== null) {
            $transformaciones[] = 'c_fill';
        }

        $prefijo = count($transformaciones) > 0
            ? 'image/upload/' . implode(',', $transformaciones) . '/'
            : 'image/upload/';

        return "https://res.cloudinary.com/{$this->config->cloudName}/{$prefijo}{$publicId}";
    }

    private function detectarMime(string $rutaArchivo): string
    {
        if (function_exists('finfo_file')) {
            $info = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($info, $rutaArchivo);
            finfo_close($info);

            if ($mime !== false) {
                return (string) $mime;
            }
        }

        $mime = mime_content_type($rutaArchivo);

        return $mime !== false ? (string) $mime : 'application/octet-stream';
    }
}
