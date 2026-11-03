<?php

namespace Database\Seeders;

use App\Models\Agente;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SolicitudEjemploSeeder extends Seeder
{
    /**
     * Crea un conjunto de solicitudes de licencia de ejemplo usando los
     * agentes y tipos de licencia ya existentes en la base de datos.
     */
    public function run(): void
    {
        $yaExisten = Solicitud::query()
            ->where('motivo', 'like', 'Ejemplo:%')
            ->count();

        if ($yaExisten > 0) {
            $this->command?->warn("Ya existen {$yaExisten} solicitudes de ejemplo. No se crearon nuevas.");

            return;
        }

        $agentes = Agente::query()
            ->whereNotNull('area_id')
            ->orderBy('id')
            ->skip(6)
            ->take(10)
            ->get()
            ->values();

        if ($agentes->count() < 10) {
            $this->command?->error('No hay suficientes agentes para crear las solicitudes de ejemplo.');

            return;
        }

        $hoy = Carbon::today(config('app.business_timezone'));

        $definiciones = [
            [
                'tipo' => 'Receso de Enero',
                'progreso' => 'completo',
                'inicio' => $hoy->copy()->addDays(15),
                'dias' => 10,
                'motivo' => 'Ejemplo: receso anual planificado',
            ],
            [
                'tipo' => 'Receso de Julio',
                'progreso' => 2,
                'inicio' => $hoy->copy()->addDays(20),
                'dias' => 5,
                'motivo' => 'Ejemplo: receso de invierno',
            ],
            [
                'tipo' => 'Enfermedad Común (Etapa 1 - 100%)',
                'progreso' => 'completo',
                'inicio' => $hoy->copy()->addDay(),
                'dias' => 3,
                'motivo' => 'Ejemplo: certificado médico por gripe',
            ],
            [
                'tipo' => 'Enfermedad de Familiar',
                'progreso' => 0,
                'inicio' => $hoy->copy()->addDays(2),
                'dias' => 2,
                'motivo' => 'Ejemplo: acompañamiento de familiar enfermo',
            ],
            [
                'tipo' => 'Maternidad',
                'progreso' => 0,
                'inicio' => $hoy->copy()->addDays(30),
                'dias' => 90,
                'motivo' => 'Ejemplo: licencia por maternidad',
            ],
            [
                'tipo' => 'Rendir Examen',
                'progreso' => 'rechazado',
                'inicio' => $hoy->copy()->addDays(5),
                'dias' => 2,
                'motivo' => 'Ejemplo: examen final de carrera',
            ],
            [
                'tipo' => 'Matrimonio del Agente',
                'progreso' => 1,
                'inicio' => $hoy->copy()->addDays(7),
                'dias' => 10,
                'motivo' => 'Ejemplo: matrimonio del agente',
            ],
            [
                'tipo' => 'Nacimiento de Hijo',
                'progreso' => 'completo',
                'inicio' => $hoy->copy()->addDays(12),
                'dias' => 5,
                'motivo' => 'Ejemplo: nacimiento de hijo',
            ],
            [
                'tipo' => 'Donación de Sangre / Extracción Dentaria',
                'progreso' => 'completo',
                'inicio' => $hoy->copy()->addDays(3),
                'dias' => 1,
                'motivo' => 'Ejemplo: donación de sangre',
            ],
            [
                'tipo' => 'Razones Particulares',
                'progreso' => 2,
                'inicio' => $hoy->copy()->addDays(40),
                'dias' => 30,
                'motivo' => 'Ejemplo: motivos personales',
            ],
        ];

        $creadas = 0;

        foreach ($definiciones as $indice => $definicion) {
            $tipo = TipoSolicitud::query()
                ->where('nombre', $definicion['tipo'])
                ->first();

            if ($tipo === null) {
                $this->command?->warn("Tipo de licencia no encontrado: {$definicion['tipo']}. Se omite.");

                continue;
            }

            $agente = $agentes[$indice];

            $solicitud = Solicitud::create([
                'agente_id' => $agente->id,
                'tipo_solicitud_id' => $tipo->id,
                'fecha_solicitud' => $hoy,
                'fecha_inicio' => $definicion['inicio'],
                'fecha_fin' => $definicion['inicio']->copy()->addDays($definicion['dias'] - 1),
                'dias_solicitados' => $definicion['dias'],
                'motivo' => $definicion['motivo'],
                'estado' => match ($definicion['progreso']) {
                    'completo' => Solicitud::ESTADO_APROBADO,
                    'rechazado' => Solicitud::ESTADO_RECHAZADO,
                    0 => Solicitud::ESTADO_PENDIENTE,
                    default => Solicitud::ESTADO_EN_REVISION,
                },
            ]);

            $this->crearAprobaciones($solicitud, $definicion['progreso']);

            $creadas++;
        }

        $this->command?->info("Se crearon {$creadas} solicitudes de licencia de ejemplo para el día de hoy.");
    }

    /**
     * Genera el historial de aprobaciones según el progreso indicado.
     */
    private function crearAprobaciones(Solicitud $solicitud, int|string $progreso): void
    {
        $etapas = $solicitud->flujoEtapas();

        $aprobadores = [
            Solicitud::ETAPA_JEFATURA => User::role('jefatura')->first()?->agente?->id,
            Solicitud::ETAPA_SECRETARIA => User::role('secretaria_general')->first()?->agente?->id,
            Solicitud::ETAPA_DIRECCION => User::role('presidencia')->first()?->agente?->id,
        ];

        $aprobadorPorDefecto = Agente::query()->value('id');
        $fecha = Carbon::today(config('app.business_timezone'));

        foreach ($etapas as $indice => $etapa) {
            $aprobadoPor = $aprobadores[$etapa] ?? $aprobadorPorDefecto;

            if ($aprobadoPor === null) {
                continue;
            }

            if ($progreso === 'rechazado') {
                $solicitud->aprobaciones()->create([
                    'aprobado_por' => $aprobadoPor,
                    'etapa' => $etapa,
                    'estado' => 'rechazado',
                    'observacion' => 'Solicitud de ejemplo rechazada.',
                    'fecha' => $fecha,
                ]);

                return;
            }

            $aprobada = is_int($progreso)
                ? $indice < $progreso
                : $progreso === 'completo';

            $solicitud->aprobaciones()->create([
                'aprobado_por' => $aprobadoPor,
                'etapa' => $etapa,
                'estado' => $aprobada ? 'aprobado' : 'pendiente',
                'fecha' => $fecha,
            ]);

            if (! $aprobada) {
                return;
            }
        }
    }
}
