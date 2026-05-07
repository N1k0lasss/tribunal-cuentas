<?php

declare(strict_types=1);

use App\Models\Agente;
use App\Models\Area;
use App\Models\Saldo;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Services\CumplimientoLegalService;
use Illuminate\Validation\ValidationException;

it('valida saldo con regla de acumulacion de 3 anios', function () {
    $area = Area::factory()->create();
    $agente = Agente::factory()->create(['area_id' => $area->id]);

    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Receso de Enero',
        'unidad_medida' => 'dias',
        'tipo_dias' => 'corridos',
        'duracion_maxima_dias' => 40,
    ]);

    $anio = now()->year;

    Saldo::factory()->create([
        'agente_id' => $agente->id,
        'anio' => $anio,
        'tipo_saldo' => $tipo->nombre,
        'dias_otorgados' => 10,
        'dias_usados' => 9,
    ]);

    Saldo::factory()->create([
        'agente_id' => $agente->id,
        'anio' => $anio - 1,
        'tipo_saldo' => $tipo->nombre,
        'dias_otorgados' => 7,
        'dias_usados' => 5,
    ]);

    Saldo::factory()->create([
        'agente_id' => $agente->id,
        'anio' => $anio - 2,
        'tipo_saldo' => $tipo->nombre,
        'dias_otorgados' => 6,
        'dias_usados' => 5,
    ]);

    $solicitud = new Solicitud([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'fecha_solicitud' => now()->toDateString(),
        'fecha_inicio' => now()->addDays(3)->toDateString(),
        'fecha_fin' => now()->addDays(7)->toDateString(),
        'dias_solicitados' => 6,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    $service = app(CumplimientoLegalService::class);

    expect(fn () => $service->validarCreacion($solicitud))
        ->toThrow(ValidationException::class);
});

it('rechaza prorroga fuera del plazo legal de 48 horas', function () {
    $area = Area::factory()->create();
    $agente = Agente::factory()->create(['area_id' => $area->id]);
    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Enfermedad Común',
        'unidad_medida' => 'dias',
        'tipo_dias' => 'corridos',
        'requiere_intervencion_medica' => true,
    ]);

    $origen = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'fecha_inicio' => now()->subDays(6)->toDateString(),
        'fecha_fin' => now()->subDays(4)->toDateString(),
        'dias_solicitados' => 2,
        'estado' => Solicitud::ESTADO_APROBADO,
    ]);

    $prorroga = new Solicitud([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'solicitud_origen_id' => $origen->id,
        'es_prorroga' => true,
        'fecha_solicitud' => now()->toDateString(),
        'fecha_inicio' => now()->addDays(5)->toDateString(),
        'fecha_fin' => now()->addDays(6)->toDateString(),
        'dias_solicitados' => 1,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    $service = app(CumplimientoLegalService::class);

    expect(fn () => $service->validarCreacion($prorroga))
        ->toThrow(ValidationException::class);
});
