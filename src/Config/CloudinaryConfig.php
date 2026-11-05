<?php

declare(strict_types=1);

namespace Restaurante\Config;

class CloudinaryConfig
{
    public string $cloudName;
    public string $apiKey;
    public string $apiSecret;
    public string $folder;

    public function __construct()
    {
        $this->cloudName = (string) ($_ENV['CLOUDINARY_CLOUD_NAME'] ?? getenv('CLOUDINARY_CLOUD_NAME') ?? '');
        $this->apiKey    = (string) ($_ENV['CLOUDINARY_API_KEY'] ?? getenv('CLOUDINARY_API_KEY') ?? '');
        $this->apiSecret = (string) ($_ENV['CLOUDINARY_API_SECRET'] ?? getenv('CLOUDINARY_API_SECRET') ?? '');
        $this->folder    = (string) ($_ENV['CLOUDINARY_FOLDER'] ?? getenv('CLOUDINARY_FOLDER') ?? 'productos');
    }

    public function estaConfigurada(): bool
    {
        return $this->cloudName !== '' && $this->apiKey !== '' && $this->apiSecret !== '';
    }

    public function urlBaseUpload(): string
    {
        return "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";
    }

    public function urlBaseDestroy(): string
    {
        return "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy";
    }
}
