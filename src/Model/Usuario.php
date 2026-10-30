<?php

declare(strict_types=1);

namespace Restaurante\Model;

class Usuario
{
    public ?int $id_usuario = null;
    public string $nombre;
    public string $email;
    public ?string $contrasena_hash = null;
    public int $id_rol;
    public bool $activo = true;
    public ?string $fecha_creacion = null;
    public ?string $fecha_actualizacion = null;

    public function __construct(string $nombre, string $email, int $id_rol)
    {
        $this->nombre = $nombre;
        $this->email = $email;
        $this->id_rol = $id_rol;
    }

    public function esAdministrador(): bool
    {
        return $this->id_rol === 4;
    }

    public function esMesero(): bool
    {
        return $this->id_rol === 2;
    }

    public function esCocinero(): bool
    {
        return $this->id_rol === 3;
    }

    public function esCliente(): bool
    {
        return $this->id_rol === 1;
    }
}
