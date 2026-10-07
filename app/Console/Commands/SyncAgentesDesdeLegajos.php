<?php

namespace App\Console\Commands;

use App\Exceptions\LegajosApiException;
use App\Models\Agente;
use App\Models\Area;
use App\Services\LegajosApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncAgentesDesdeLegajos extends Command
{
    protected $signature = 'app:sync-agentes-desde-legajos';

    protected $description = 'Sincroniza los agentes locales con los legajos de la API externa';

    public function handle(LegajosApiClient $api): int
    {
        try {
            $personas = $api->getPersonas();
        } catch (LegajosApiException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($personas === []) {
            $this->warn('La API de legajos no devolvió ninguna persona.');

            return self::FAILURE;
        }

        $sincronizados = 0;
        $sinLegajo = 0;
        $noEncontrados = 0;
        $fallos = 0;

        foreach ($personas as $persona) {
            $dni = (string) ($persona['dni'] ?? '');

            if ($dni === '') {
                $fallos++;

                continue;
            }

            try {
                $payload = $api->getLegajosPorDni($dni);
            } catch (LegajosApiException $e) {
                $fallos++;
                $this->warn("DNI {$dni}: {$e->getMessage()}");

                continue;
            }

            if ($payload === null) {
                $noEncontrados++;

                continue;
            }

            $legajos = $payload['legajo'] ?? [];

            if (! is_array($legajos) || $legajos === []) {
                $sinLegajo++;

                continue;
            }

            $legajo = end($legajos);

            if (! is_array($legajo)) {
                $fallos++;

                continue;
            }

            try {
                DB::transaction(fn () => $this->persistir($persona, $legajo));
            } catch (\Throwable $e) {
                $fallos++;
                $this->warn("DNI {$dni}: no se pudo guardar el agente ({$e->getMessage()}).");

                continue;
            }

            $sincronizados++;
        }

        $this->info("Agentes sincronizados: {$sincronizados}");
        $this->line("Sin legajo (ignorados): {$sinLegajo}");
        $this->line("No encontrados en la API: {$noEncontrados}");

        if ($fallos > 0) {
            $this->warn("Errores: {$fallos}");
        }

        return $fallos > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $persona  Persona devuelta por /personas (incluye apellido).
     * @param  array<string, mixed>  $legajo
     */
    private function persistir(array $persona, array $legajo): void
    {
        $dni = (string) ($persona['dni'] ?? '');

        if ($dni === '') {
            return;
        }

        $areaNombre = $this->nombreAnidado($legajo, 'area') ?: 'Sin área';
        $cargoNombre = $this->nombreAnidado($legajo, 'cargo');

        $area = Area::firstOrCreate(['nombre' => $areaNombre]);

        Agente::updateOrCreate(
            ['dni' => $dni],
            [
                'legajo' => $legajo['num_legajo'] ?? null,
                'nombre' => (string) ($persona['nombre'] ?? ''),
                'apellido' => (string) ($persona['apellido'] ?? ''),
                'cargo' => $cargoNombre,
                'situacion_revista' => $this->contractTypeLabel($legajo['tipo_contrato'] ?? null),
                'area_id' => $area->id,
                'fecha_ingreso' => $legajo['fecha_de_ingreso'] ?? null,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $legajo
     */
    private function nombreAnidado(array $legajo, string $clave): ?string
    {
        $nodo = $legajo[$clave] ?? null;

        if (! is_array($nodo)) {
            return null;
        }

        $nombre = $nodo['nombre'] ?? null;

        return is_string($nombre) && $nombre !== '' ? $nombre : null;
    }

    private function contractTypeLabel(mixed $type): ?string
    {
        $type = is_numeric($type) ? (int) $type : null;

        return match ($type) {
            0 => 'Permanente',
            1 => 'Contratado',
            2 => 'Funcionario',
            default => $type === null ? null : "Tipo {$type}",
        };
    }
}
