<?php

declare(strict_types=1);

namespace Restaurante\Middleware;

use Restaurante\Config\Config;

class RateLimiterMiddleware
{
    private int $capacidad;
    private float $reposicion_por_segundo;

    public function __construct()
    {
        $this->capacidad = Config::obtenerEntero('RATE_LIMIT_CAPACITY', 60);
        $this->reposicion_por_segundo = (float) Config::obtenerEntero('RATE_LIMIT_REFILL_RATE', 1);
    }

    public function consumir(string $cliente_id, string $endpoint): void
    {
        $ahora = microtime(true);
        $clave = $this->claveDeposito($cliente_id, $endpoint);

        $pdo = \Restaurante\Database\Conexion::obtener();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('
                SELECT tokens, ultima_actualizacion
                FROM rate_limit_state
                WHERE clave = :clave
                FOR UPDATE
            ');
            $stmt->execute([':clave' => $clave]);
            $fila = $stmt->fetch();

            if ($fila === false) {
                $tokens = (float) $this->capacidad;
                $ultima = $ahora;

                $pdo->prepare('
                    INSERT INTO rate_limit_state (clave, tokens, ultima_actualizacion)
                    VALUES (:clave, :tokens, :ultima)
                ')->execute([
                    ':clave' => $clave,
                    ':tokens' => $tokens,
                    ':ultima' => date('Y-m-d H:i:s', (int) $ultima),
                ]);
            } else {
                $tokens = (float) $fila['tokens'];
                $ultima = strtotime($fila['ultima_actualizacion']) ?? $ahora;

                $transcurrido = $ahora - $ultima;
                $tokens = min($this->capacidad, $tokens + ($transcurrido * $this->reposicion_por_segundo));
                $ultima = $ahora;
            }

            if ($tokens < 1.0) {
                $pdo->rollBack();

                $retry_after = (int) ceil((1.0 - $tokens) / $this->reposicion_por_segundo);
                throw new \RuntimeException('Demasiadas solicitudes. Intente de nuevo más tarde.', 429, null, ['Retry-After' => $retry_after]);
            }

            $tokens -= 1.0;

            $pdo->prepare('
                UPDATE rate_limit_state
                SET tokens = :tokens, ultima_actualizacion = :ultima
                WHERE clave = :clave
            ')->execute([
                ':tokens' => $tokens,
                ':ultima' => date('Y-m-d H:i:s', (int) $ultima),
                ':clave' => $clave,
            ]);

            $pdo->commit();
        } catch (\RuntimeException $e) {
            $pdo->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw new \RuntimeException('Error interno del limitador de solicitudes.', 500);
        }
    }

    private function claveDeposito(string $cliente_id, string $endpoint): string
    {
        return hash('sha256', $cliente_id . '|' . $endpoint);
    }
}
