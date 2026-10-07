<?php

namespace App\Services;

use App\Exceptions\LegajosApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class LegajosApiClient
{
    /**
     * Devuelve todas las personas expuestas por la API.
     *
     * @return list<array<string, mixed>>
     *
     * @throws LegajosApiException
     */
    public function getPersonas(): array
    {
        $personas = $this->request('personas')->json('personas');

        return is_array($personas) ? $personas : [];
    }

    /**
     * Devuelve la persona con sus legajos, o null si la API responde 404.
     *
     * @return array<string, mixed>|null
     *
     * @throws LegajosApiException
     */
    public function getLegajosPorDni(string $dni): ?array
    {
        $response = $this->request('legajos/por-dni/'.$dni, permitir404: true);

        if ($response->status() === 404) {
            return null;
        }

        $payload = $response->json();

        return is_array($payload) ? $payload : null;
    }

    /**
     * Diagnóstico de conectividad contra la API.
     *
     * @return array{ok: bool, personas: int, latencia_ms: int, error: string|null}
     */
    public function verificarConexion(): array
    {
        $inicio = microtime(true);

        try {
            $personas = $this->getPersonas();

            return [
                'ok' => true,
                'personas' => count($personas),
                'latencia_ms' => $this->latenciaDesde($inicio),
                'error' => null,
            ];
        } catch (LegajosApiException $e) {
            return [
                'ok' => false,
                'personas' => 0,
                'latencia_ms' => $this->latenciaDesde($inicio),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @throws LegajosApiException
     */
    private function request(string $uri, bool $permitir404 = false): Response
    {
        $config = config('services.legajos_api');

        $token = $config['token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new LegajosApiException('Falta configurar la variable LEGAJOS_API_TOKEN.');
        }

        try {
            $response = Http::baseUrl(rtrim((string) $config['base_url'], '/'))
                ->withToken($token)
                ->acceptJson()
                ->timeout((int) ($config['timeout'] ?? 15))
                ->withOptions(['verify' => (bool) ($config['verify_ssl'] ?? false)])
                ->retry(
                    max(1, (int) ($config['retries'] ?? 2)),
                    200,
                    fn ($exception) => $this->debeReintentar($exception),
                    throw: false,
                )
                ->get($uri);
        } catch (ConnectionException $e) {
            throw new LegajosApiException(
                'No se pudo conectar con la API de legajos: '.$e->getMessage(),
                null,
                $e,
            );
        }

        if ($response->status() === 404 && $permitir404) {
            return $response;
        }

        if ($response->failed()) {
            throw new LegajosApiException($this->mensajeDeError($response), $response->status());
        }

        return $response;
    }

    /**
     * Reintenta solo ante errores de red o 5xx: un 404 es una respuesta válida.
     */
    private function debeReintentar(mixed $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        if ($exception instanceof RequestException) {
            return $exception->response->serverError();
        }

        return false;
    }

    private function mensajeDeError(Response $response): string
    {
        return match (true) {
            $response->status() === 401 => 'Token inválido o expirado en la API de legajos (HTTP 401).',
            $response->status() === 403 => 'Acceso denegado por la API de legajos (HTTP 403).',
            default => 'La API de legajos respondió HTTP '.$response->status().'.',
        };
    }

    private function latenciaDesde(float $inicio): int
    {
        return (int) round((microtime(true) - $inicio) * 1000);
    }
}
