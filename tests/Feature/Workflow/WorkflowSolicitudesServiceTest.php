<?php

declare(strict_types=1);

use App\Models\Agente;
use App\Models\Area;
use App\Models\Delegacion;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Services\WorkflowSolicitudesService;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['jefatura', 'secretaria_general', 'presidencia'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

it('permite aprobar por delegacion activa de etapa', function () {
    $area = Area::factory()->create();

    $titular = User::factory()->create();
    $titular->assignRole('jefatura');
    Agente::factory()->create(['user_id' => $titular->id, 'area_id' => $area->id]);

    $delegado = User::factory()->create();
    Agente::factory()->create(['user_id' => $delegado->id, 'area_id' => $area->id]);

    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Receso de Enero',
        'unidad_medida' => 'dias',
        'tipo_dias' => 'corridos',
    ]);

    $solicitudAgente = Agente::factory()->create(['area_id' => $area->id]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $solicitudAgente->id,
        'tipo_solicitud_id' => $tipo->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    Delegacion::factory()->create([
        'titular_user_id' => $titular->id,
        'delegado_user_id' => $delegado->id,
        'etapa' => Solicitud::ETAPA_JEFATURA,
        'vigente_desde' => now()->subHour(),
        'vigente_hasta' => now()->addHour(),
        'activa' => true,
    ]);

    $service = app(WorkflowSolicitudesService::class);
    $service->resolver($solicitud, $delegado, 'aprobado', 'Aprobada por delegación');

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_EN_REVISION)
        ->and($solicitud->aprobaciones()->count())->toBe(1)
        ->and($solicitud->workflowEventos()->count())->toBe(1)
        ->and((bool) $solicitud->workflowEventos()->first()?->es_delegacion)->toBeTrue();
});

it('rechaza aprobacion sin rol ni delegacion', function () {
    $area = Area::factory()->create();

    $actor = User::factory()->create();
    Agente::factory()->create(['user_id' => $actor->id, 'area_id' => $area->id]);

    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Receso de Enero',
        'unidad_medida' => 'dias',
        'tipo_dias' => 'corridos',
    ]);

    $solicitudAgente = Agente::factory()->create(['area_id' => $area->id]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $solicitudAgente->id,
        'tipo_solicitud_id' => $tipo->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    $service = app(WorkflowSolicitudesService::class);

    expect(fn () => $service->resolver($solicitud, $actor, 'aprobado'))
        ->toThrow(ValidationException::class);
});
