<?php

declare(strict_types=1);

use App\Models\Agente;
use App\Models\Area;
use App\Models\Delegacion;
use App\Models\Resolucion;
use App\Models\Saldo;
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
        'estado' => Solicitud::ESTADO_EN_REVISION,
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
        'estado' => Solicitud::ESTADO_EN_REVISION,
    ]);

    $service = app(WorkflowSolicitudesService::class);

    expect(fn () => $service->resolver($solicitud, $actor, 'aprobado'))
        ->toThrow(ValidationException::class);
});

it('requiere resolucion final en presidencia para aprobar solicitud', function () {
    $area = Area::factory()->create();

    $presidente = User::factory()->create();
    $presidente->assignRole('presidencia');
    Agente::factory()->create(['user_id' => $presidente->id, 'area_id' => $area->id]);

    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Receso de Enero',
        'unidad_medida' => 'dias',
        'tipo_dias' => 'corridos',
    ]);

    $solicitudAgente = Agente::factory()->create(['area_id' => $area->id]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $solicitudAgente->id,
        'tipo_solicitud_id' => $tipo->id,
        'estado' => Solicitud::ESTADO_EN_REVISION,
    ]);

    $solicitud->aprobaciones()->create([
        'aprobado_por' => $presidente->agente->id,
        'etapa' => Solicitud::ETAPA_JEFATURA,
        'estado' => 'aprobado',
        'fecha' => now()->subDay(),
    ]);

    $solicitud->aprobaciones()->create([
        'aprobado_por' => $presidente->agente->id,
        'etapa' => Solicitud::ETAPA_SECRETARIA,
        'estado' => 'aprobado',
        'fecha' => now()->subHours(12),
    ]);

    $service = app(WorkflowSolicitudesService::class);

    expect(fn () => $service->resolver($solicitud->fresh('aprobaciones'), $presidente, 'aprobado'))
        ->toThrow(ValidationException::class);

    Resolucion::factory()->create([
        'solicitud_id' => $solicitud->id,
    ]);

    Saldo::factory()->create([
        'agente_id' => $solicitudAgente->id,
        'anio' => now()->year,
        'tipo_saldo' => $tipo->nombre,
        'dias_otorgados' => 30,
        'dias_usados' => 0,
    ]);

    $service->resolver($solicitud->fresh('aprobaciones'), $presidente, 'aprobado');

    expect($solicitud->fresh()->estado)->toBe(Solicitud::ESTADO_APROBADO);
});

it('revierte aprobacion final si no hay saldo suficiente', function () {
    $area = Area::factory()->create();

    $presidente = User::factory()->create();
    $presidente->assignRole('presidencia');
    Agente::factory()->create(['user_id' => $presidente->id, 'area_id' => $area->id]);

    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Receso de Enero',
        'unidad_medida' => 'dias',
        'tipo_dias' => 'corridos',
    ]);

    $solicitudAgente = Agente::factory()->create(['area_id' => $area->id]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $solicitudAgente->id,
        'tipo_solicitud_id' => $tipo->id,
        'estado' => Solicitud::ESTADO_EN_REVISION,
        'dias_solicitados' => 10,
    ]);

    $solicitud->aprobaciones()->create([
        'aprobado_por' => $presidente->agente->id,
        'etapa' => Solicitud::ETAPA_JEFATURA,
        'estado' => 'aprobado',
        'fecha' => now()->subDay(),
    ]);

    $solicitud->aprobaciones()->create([
        'aprobado_por' => $presidente->agente->id,
        'etapa' => Solicitud::ETAPA_SECRETARIA,
        'estado' => 'aprobado',
        'fecha' => now()->subHours(12),
    ]);

    Resolucion::factory()->create([
        'solicitud_id' => $solicitud->id,
    ]);

    Saldo::factory()->create([
        'agente_id' => $solicitudAgente->id,
        'anio' => now()->year,
        'tipo_saldo' => $tipo->nombre,
        'dias_otorgados' => 5,
        'dias_usados' => 0,
    ]);

    $service = app(WorkflowSolicitudesService::class);

    expect(fn () => $service->resolver($solicitud->fresh('aprobaciones'), $presidente, 'aprobado'))
        ->toThrow(ValidationException::class);

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_EN_REVISION)
        ->and($solicitud->aprobaciones()->where('etapa', Solicitud::ETAPA_DIRECCION)->count())->toBe(0);
});
