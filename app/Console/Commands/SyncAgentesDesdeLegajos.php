<?php

namespace App\Console\Commands;

use App\Models\Agente;
use App\Models\Area;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncAgentesDesdeLegajos extends Command
{
    protected $signature = 'app:sync-agentes-desde-legajos';

    protected $description = 'Sincroniza los agentes locales con los legajos de la base externa';

    public function handle(): int
    {
        try {
            $external = DB::connection('legajos');
            $external->getPdo();
        } catch (\Exception $e) {
            $this->error("No se pudo conectar a la base de datos de legajos: {$e->getMessage()}");
            return self::FAILURE;
        }

        $synced = 0;

        DB::transaction(function () use (&$synced) {
            $external = DB::connection('legajos');

            $external->table('legajos as l')
                ->join('personas as p', 'p.id', '=', 'l.persona_id')
                ->leftJoin('areas as a', 'a.id', '=', 'l.area_id')
                ->leftJoin('cargos as c', 'c.id', '=', 'l.cargo_id')
                ->select([
                    'l.num_legajo as legajo',
                    'p.nombre',
                    'p.apellido',
                    'p.dni',
                    'l.fecha_de_ingreso',
                    'l.tipo_contrato',
                    'a.nombre as area_nombre',
                    'c.nombre as cargo_nombre',
                ])
                ->orderBy('l.id')
                ->chunk(100, function ($records) use (&$synced) {
                    foreach ($records as $record) {
                        $area = Area::firstOrCreate([
                            'nombre' => $record->area_nombre ?: 'Sin área',
                        ]);

                        Agente::updateOrCreate(
                            ['dni' => (string) $record->dni],
                            [
                                'legajo' => $record->legajo,
                                'nombre' => $record->nombre,
                                'apellido' => $record->apellido,
                                'cargo' => $record->cargo_nombre,
                                'situacion_revista' => $this->contractTypeLabel($record->tipo_contrato),
                                'area_id' => $area->id,
                                'fecha_ingreso' => $record->fecha_de_ingreso,
                            ],
                        );

                        $synced++;
                    }
                });
        });

        $this->info("Agentes sincronizados: {$synced}");

        return self::SUCCESS;
    }

    private function contractTypeLabel(?int $type): ?string
    {
        return match ($type) {
            0 => 'Permanente',
            1 => 'Contratado',
            2 => 'Funcionario',
            default => $type === null ? null : "Tipo {$type}",
        };
    }
}
