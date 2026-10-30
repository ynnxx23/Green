<?php

declare(strict_types=1);

namespace Restaurante\Service;

use Restaurante\Dao\PedidoDao;
use Restaurante\Dao\ProductoDao;
use Restaurante\Dao\MesaDao;

class ServicioDePedidos
{
    private PedidoDao $pedidoDao;
    private ProductoDao $productoDao;
    private MesaDao $mesaDao;

    public function __construct(PedidoDao $pedidoDao, ProductoDao $productoDao, MesaDao $mesaDao)
    {
        $this->pedidoDao = $pedidoDao;
        $this->productoDao = $productoDao;
        $this->mesaDao = $mesaDao;
    }

    public function registrarPedido(int $id_mesa, int $id_mesero, array $detalles): int
    {
        $mesa = $this->mesaDao->obtenerPorId($id_mesa);
        if ($mesa === null) {
            throw new \InvalidArgumentException('La mesa no existe.');
        }
        if (!$mesa->activo) {
            throw new \InvalidArgumentException('La mesa no está disponible.');
        }

        if (empty($detalles)) {
            throw new \InvalidArgumentException('El pedido debe tener al menos un producto.');
        }

        $detallesConPrecio = [];
        foreach ($detalles as $detalle) {
            $id_producto = (int) ($detalle['id_producto'] ?? 0);
            $cantidad = (int) ($detalle['cantidad'] ?? 1);

            if ($id_producto <= 0) {
                throw new \InvalidArgumentException('ID de producto inválido.');
            }
            if ($cantidad < 1) {
                throw new \InvalidArgumentException('La cantidad debe ser al menos 1.');
            }

            $producto = $this->productoDao->obtenerPorId($id_producto);
            if ($producto === null) {
                throw new \InvalidArgumentException("El producto ID {$id_producto} no existe.");
            }
            if (!$producto->activo) {
                throw new \InvalidArgumentException("El producto '{$producto->nombre}' no está disponible.");
            }

            $detallesConPrecio[] = [
                'id_producto' => $id_producto,
                'cantidad' => $cantidad,
                'precio_unitario' => (float) $producto->precio,
            ];
        }

        return $this->pedidoDao->crearConDetalles($id_mesa, $id_mesero, $detallesConPrecio);
    }

    public function obtenerPedido(int $id_pedido): ?array
    {
        return $this->pedidoDao->obtenerPorId($id_pedido);
    }

    public function listarPedidosMesero(int $id_mesero): array
    {
        return $this->pedidoDao->listarPorMesero($id_mesero);
    }

    public function listarTodosLosPedidos(): array
    {
        return $this->pedidoDao->listarTodos();
    }

    public function listarPedidosPorEstado(string $estado): array
    {
        return $this->pedidoDao->listarPorEstado($estado);
    }

    public function cambiarEstadoPedido(int $id_pedido, string $estado_nuevo): bool
    {
        $validos = ['pendiente', 'en_preparacion', 'listo', 'entregado', 'cancelado'];
        if (!in_array($estado_nuevo, $validos, true)) {
            throw new \InvalidArgumentException('Estado de pedido no válido.');
        }

        $pedido = $this->pedidoDao->obtenerPorId($id_pedido);
        if ($pedido === null) {
            throw new \InvalidArgumentException('El pedido no existe.');
        }

        $estado_actual = $pedido['estado'];

        $transicionesValidas = [
            'pendiente' => ['en_preparacion', 'cancelado'],
            'en_preparacion' => ['listo', 'cancelado'],
            'listo' => ['entregado', 'cancelado'],
            'entregado' => [],
            'cancelado' => [],
        ];

        if ($estado_nuevo === $estado_actual) {
            return true;
        }

        $permitidos = $transicionesValidas[$estado_actual] ?? [];
        if (!in_array($estado_nuevo, $permitidos, true)) {
            throw new \InvalidArgumentException(
                "No se puede pasar de '{$estado_actual}' a '{$estado_nuevo}'."
            );
        }

        return $this->pedidoDao->cambiarEstado($id_pedido, $estado_nuevo);
    }
}
