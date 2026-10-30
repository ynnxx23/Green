<?php

declare(strict_types=1);

namespace Restaurante\Service;

use Restaurante\Dao\AreaDao;
use Restaurante\Dao\MesaDao;
use Restaurante\Model\Area;
use Restaurante\Model\Mesa;

class ServicioDeMesas
{
    private AreaDao $areaDao;
    private MesaDao $mesaDao;

    public function __construct(AreaDao $areaDao, MesaDao $mesaDao)
    {
        $this->areaDao = $areaDao;
        $this->mesaDao = $mesaDao;
    }

    public function listarAreas(): array
    {
        return $this->areaDao->listarActivas();
    }

    public function obtenerArea(int $id_area): ?Area
    {
        return $this->areaDao->obtenerPorId($id_area);
    }

    public function listarMesas(int $id_area): array
    {
        return $this->mesaDao->listarPorArea($id_area, true);
    }

    public function obtenerMesa(int $id_mesa): ?Mesa
    {
        return $this->mesaDao->obtenerPorId($id_mesa);
    }
}
