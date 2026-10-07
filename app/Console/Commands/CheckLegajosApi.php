<?php

namespace App\Console\Commands;

use App\Exceptions\LegajosApiException;
use App\Services\LegajosApiClient;
use Illuminate\Console\Command;

class CheckLegajosApi extends Command
{
    protected $signature = 'app:legajos-api-check {--dni= : DNI a consultar en /legajos/por-dni}';

    protected $description = 'Verifica la conexión con la API externa de legajos';

    public function handle(LegajosApiClient $api): int
    {
        $config = config('services.legajos_api');

        $this->info('API: '.(string) ($config['base_url'] ?? ''));
        $this->line('TLS verify_ssl: '.((bool) ($config['verify_ssl'] ?? false) ? 'activado' : 'desactivado'));
        $this->line('Token: '.$this->tokenEnmascarado($config['token'] ?? null));
        $this->newLine();

        $resultado = $api->verificarConexion();

        if (! $resultado['ok']) {
            $this->error('GET /personas -> FALLO: '.$resultado['error']);

            return self::FAILURE;
        }

        $this->line(sprintf(
            'GET /personas -> OK: %d personas (%d ms)',
            $resultado['personas'],
            $resultado['latencia_ms'],
        ));

        $dni = $this->option('dni');

        if (! is_string($dni) || $dni === '') {
            $this->line('Usá --dni=20987654 para probar /legajos/por-dni/{dni}.');

            return self::SUCCESS;
        }

        $inicio = microtime(true);

        try {
            $payload = $api->getLegajosPorDni($dni);
        } catch (LegajosApiException $e) {
            $this->error("GET /legajos/por-dni/{$dni} -> FALLO: {$e->getMessage()}");

            return self::FAILURE;
        }

        $latencia = (int) round((microtime(true) - $inicio) * 1000);

        if ($payload === null) {
            $this->warn("GET /legajos/por-dni/{$dni} -> 404 (persona no encontrada, {$latencia} ms)");

            return self::SUCCESS;
        }

        $legajos = is_array($payload['legajo'] ?? null) ? $payload['legajo'] : [];
        $nombre = is_array($payload['persona'] ?? null) ? ($payload['persona']['nombre'] ?? '') : '';

        $this->line(sprintf(
            'GET /legajos/por-dni/%s -> OK: %s, %d legajo(s) (%d ms)',
            $dni,
            $nombre,
            count($legajos),
            $latencia,
        ));

        return self::SUCCESS;
    }

    private function tokenEnmascarado(mixed $token): string
    {
        if (! is_string($token) || $token === '') {
            return 'NO CONFIGURADO';
        }

        return substr($token, 0, 6).'…'.str_repeat('*', 6);
    }
}
