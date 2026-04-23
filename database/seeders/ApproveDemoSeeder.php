<?php

namespace Database\Seeders;

use App\Models\Agente;
use App\Models\Aprobacion;
use App\Models\Area;
use App\Models\Resolucion;
use App\Models\Saldo;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Services\WorkflowSolicitudesService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ApproveDemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $area = Area::first() ?? Area::factory()->create();

        // Presidente (actor que aprobará en la última etapa)
        $presidenteUser = User::factory()->create(['email' => 'presidente@example.test', 'name' => 'Presidente Demo']);
        $presidenteUser->assignRole('presidencia');
        $presidenteAgent = Agente::factory()->create(['user_id' => $presidenteUser->id, 'area_id' => $area->id]);

        // Agente solicitante
        $agenteUser = User::factory()->create(['email' => 'agente-demo@example.test', 'name' => 'Agente Demo']);
        $agenteUser->assignRole('agente');
        $agente = Agente::factory()->create(['user_id' => $agenteUser->id, 'area_id' => $area->id]);

        // Tipo de solicitud
        $tipo = TipoSolicitud::factory()->create([
            'nombre' => 'Licencia Anual Ordinaria - Demo',
            'unidad_medida' => 'dias',
            'tipo_dias' => 'corridos',
            'duracion_maxima_dias' => 30,
        ]);

        // Crear saldos suficientes para cubrir la solicitud (ej. 10 días)
        Saldo::factory()->create([
            'agente_id' => $agente->id,
            'anio' => now()->year,
            'tipo_saldo' => $tipo->nombre,
            'dias_otorgados' => 15,
            'dias_usados' => 0,
        ]);

        // Crear solicitud que ya pasó Jefatura y Secretaría y está lista para Presidencia
        $solicitud = Solicitud::factory()->create([
            'agente_id' => $agente->id,
            'tipo_solicitud_id' => $tipo->id,
            'dias_solicitados' => 10,
            'estado' => Solicitud::ESTADO_EN_REVISION,
        ]);

        // Marcar aprobaciones previas (Jefatura y Secretaría)
        Aprobacion::create([
            'solicitud_id' => $solicitud->id,
            'aprobado_por' => $presidenteAgent->id,
            'etapa' => Solicitud::ETAPA_JEFATURA,
            'estado' => 'aprobado',
            'fecha' => now()->subDays(2),
        ]);

        Aprobacion::create([
            'solicitud_id' => $solicitud->id,
            'aprobado_por' => $presidenteAgent->id,
            'etapa' => Solicitud::ETAPA_SECRETARIA,
            'estado' => 'aprobado',
            'fecha' => now()->subDay(),
        ]);

        // Crear Resolución final exigida para aprobación en Presidencia
        Resolucion::create([
            'numero' => 'RES-DEMO-'.now()->format('YmdHis'),
            'gde_expediente' => 'EX-'.now()->year.'-00000001-GDESDE-TCSE',
            'gde_documento' => 'IF-'.now()->year.'-00000001-GDESDE-TCSE',
            'fecha_emision' => now()->toDateString(),
            'detalle' => 'Resolución demo para pruebas',
            'solicitud_id' => $solicitud->id,
        ]);

        // Ejecutar la aprobación final usando el servicio
        $service = app(WorkflowSolicitudesService::class);

        $this->command->info('Intentando aprobar la solicitud demo (#'.$solicitud->id.') con el usuario presidencia: '.$presidenteUser->email);

        try {
            $service->resolver($solicitud->fresh('aprobaciones'), $presidenteUser, 'aprobado', 'Aprobación demo automática');
            $this->command->info('Aprobación demo completada. Estado actual: '.$solicitud->fresh()->estado);
        } catch (\Throwable $e) {
            $this->command->error('Falló la aprobación demo: '.$e->getMessage());
        }
    }
}
