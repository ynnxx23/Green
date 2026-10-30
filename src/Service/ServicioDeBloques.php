<?php

declare(strict_types=1);

namespace Restaurante\Service;

use Restaurante\Dao\BloqueMesaDao;
use Restaurante\Model\BloqueMesa;

class ServicioDeBloques
{
    private BloqueMesaDao $bloqueDao;

    public function __construct(BloqueMesaDao $bloqueDao)
    {
        $this->bloqueDao = $bloqueDao;
    }

    public function listarPorMesa(int $id_mesa): array
    {
        return $this->bloqueDao->listarPorMesa($id_mesa);
    }

    public function obtenerPorId(int $id_bloque): ?BloqueMesa
    {
        return $this->bloqueDao->obtenerPorId($id_bloque);
    }

    public function crearBloque(
        int $id_mesa,
        string $fecha_inicio,
        string $fecha_fin,
        ?string $motivo,
        int $creado_por
    ): int {
        $inicio = new \DateTime($fecha_inicio);
        $fin = new \DateTime($fecha_fin);

        if ($inicio === false || $fin === false) {
            throw new \InvalidArgumentException('Fechas inválidas.');
        }

        if ($fin <= $inicio) {
            throw new \InvalidArgumentException('La fecha de fin debe ser posterior a la fecha de inicio.');
        }

        if ($this->bloqueDao->existeSolapamiento($id_mesa, $fecha_inicio, $fecha_fin)) {
            throw new \InvalidArgumentException('Ya existe un bloque que se solapa con ese intervalo.');
        }

        $bloque = new BloqueMesa($id_mesa, $fecha_inicio, $fecha_fin, $motivo);
        return $this->bloqueDao->crear($bloque, $creado_por);
    }

    public function desactivarBloque(int $id_bloque): bool
    {
        $bloque = $this->bloqueDao->obtenerPorId($id_bloque);
        if ($bloque === null) {
            throw new \InvalidArgumentException('El bloque no existe.');
        }

        return $this->bloqueDao->desactivar($id_bloque);
    }
}
