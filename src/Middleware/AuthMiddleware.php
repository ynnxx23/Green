<?php

declare(strict_types=1);

namespace Restaurante\Middleware;

use Restaurante\Service\ServicioDeAutenticacion;

class AuthMiddleware
{
    private ServicioDeAutenticacion $auth;

    public function __construct(ServicioDeAutenticacion $auth)
    {
        $this->auth = $auth;
    }

    public function requiereAutenticacion(): void
    {
        if (!ServicioDeAutenticacion::estaAutenticado()) {
            throw new \RuntimeException('Autenticación requerida.', 401);
        }
    }

    public function requiereAdministrador(): void
    {
        $this->requiereAutenticacion();

        if (!ServicioDeAutenticacion::esAdministrador()) {
            throw new \RuntimeException('Acceso denegado: se requiere rol de administrador.', 403);
        }
    }

    public function requiereMeseroOAdministrador(): void
    {
        $this->requiereAutenticacion();

        $rol = ServicioDeAutenticacion::rolUsuarioAutenticado();
        if ($rol !== 2 && $rol !== 4) {
            throw new \RuntimeException('Acceso denegado: se requiere rol de encargado o administrador.', 403);
        }
    }

    public function requierePropietarioOAdministrador(int $id_usuario_recurso): void
    {
        $this->requiereAutenticacion();

        $idAutenticado = ServicioDeAutenticacion::idUsuarioAutenticado();
        $esAdmin = ServicioDeAutenticacion::esAdministrador();

        if ($idAutenticado !== $id_usuario_recurso && !$esAdmin) {
            throw new \RuntimeException('Acceso denegado: no puede acceder a recursos de otro usuario.', 403);
        }
    }
}
