<?php

declare(strict_types=1);

namespace Restaurante\Controller;

use Restaurante\Middleware\AuthMiddleware;
use Restaurante\Service\ServicioDeBloques;

class BloqueAdminController
{
    private ServicioDeBloques $servicioDeBloques;
    private AuthMiddleware $authMiddleware;

    public function __construct(ServicioDeBloques $servicioDeBloques, AuthMiddleware $authMiddleware)
    {
        $this->servicioDeBloques = $servicioDeBloques;
        $this->authMiddleware = $authMiddleware;
    }

    public function manejar(string $ruta_interna, string $metodo, array $query_params): array
    {
        $this->authMiddleware->requiereMeseroOAdministrador();

        if ($ruta_interna === '' || $ruta_interna === 'listar') {
            if ($metodo !== 'GET') {
                return $this->error('Método no permitido.', 405);
            }
            return $this->listar($query_params);
        }

        if ($metodo === 'POST' && $ruta_interna === '') {
            return $this->crear();
        }

        if (preg_match('/^(\d+)$/', $ruta_interna, $coincidencias)) {
            $id = (int) $coincidencias[1];

            if ($metodo === 'DELETE') {
                return $this->desactivar($id);
            }
        }

        return $this->error('Endpoint no encontrado.', 404);
    }

    private function listar(array $query_params): array
    {
        $id_mesa = isset($query_params['mesa_id']) ? (int) $query_params['mesa_id'] : 0;

        if ($id_mesa <= 0) {
            return $this->error('Se requiere el parámetro mesa_id.', 400);
        }

        $bloques = $this->servicioDeBloques->listarPorMesa($id_mesa);

        $datos = [];
        foreach ($bloques as $bloque) {
            $datos[] = [
                'id_bloque' => $bloque->id_bloque,
                'id_mesa' => $bloque->id_mesa,
                'fecha_inicio' => $bloque->fecha_inicio,
                'fecha_fin' => $bloque->fecha_fin,
                'motivo' => $bloque->motivo,
                'creado_por' => $bloque->creado_por,
                'fecha_creacion' => $bloque->fecha_creacion,
            ];
        }

        return [
            'exito' => true,
            'datos' => $datos,
            'mensaje' => 'Bloques obtenidos correctamente.',
        ];
    }

    private function crear(): array
    {
        $entrada = $this->leerCuerpoJson();

        $id_mesa = (int) ($entrada['mesa_id'] ?? 0);
        $fecha_inicio = $entrada['fecha_inicio'] ?? '';
        $fecha_fin = $entrada['fecha_fin'] ?? '';
        $motivo = $entrada['motivo'] ?? null;
        $creado_por = \Restaurante\Service\ServicioDeAutenticacion::idUsuarioAutenticado();

        if ($id_mesa <= 0 || $fecha_inicio === '' || $fecha_fin === '') {
            return $this->error('Faltan datos obligatorios: mesa_id, fecha_inicio, fecha_fin.', 400);
        }

        try {
            $id = $this->servicioDeBloques->crearBloque($id_mesa, $fecha_inicio, $fecha_fin, $motivo, $creado_por);

            return [
                'exito' => true,
                'datos' => ['id_bloque' => $id],
                'mensaje' => 'Bloque creado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al crear el bloque.', 500);
        }
    }

    private function desactivar(int $id_bloque): array
    {
        try {
            $resultado = $this->servicioDeBloques->desactivarBloque($id_bloque);

            if (!$resultado) {
                return $this->error('El bloque no existe.', 404);
            }

            return [
                'exito' => true,
                'mensaje' => 'Bloque eliminado correctamente.',
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            return $this->error('Error al eliminar el bloque.', 500);
        }
    }

    private function leerCuerpoJson(): array
    {
        $cuerpo = file_get_contents('php://input');
        $datos = json_decode($cuerpo, true);

        return is_array($datos) ? $datos : [];
    }

    private function error(string $mensaje, int $codigo_http): array
    {
        http_response_code($codigo_http);

        return [
            'exito' => false,
            'datos' => null,
            'mensaje' => $mensaje,
            'codigo' => $codigo_http,
        ];
    }
}
