<?php

declare(strict_types=1);

use App\Models\Agente;
use App\Models\Area;
use App\Models\Documento;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Services\JustificacionService;
use Illuminate\Validation\ValidationException;

it('registra presentacion de justificacion medica dentro de plazo', function () {
    $area = Area::factory()->create();
    $agente = Agente::factory()->create(['area_id' => $area->id]);

    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Enfermedad Común',
        'requiere_intervencion_medica' => true,
        'unidad_medida' => 'dias',
        'tipo_dias' => 'corridos',
    ]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'fecha_inicio' => now()->toDateString(),
        'justificacion_vence_en' => now()->addDay(),
        'justificacion_presentada_en' => null,
    ]);

    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
        'tipo_documento' => 'Certificado Médico',
    ]);

    app(JustificacionService::class)->registrarDocumento($documento);

    expect($solicitud->fresh()->justificacion_presentada_en)->not()->toBeNull();
});

it('rechaza presentacion de justificacion medica fuera de plazo', function () {
    $area = Area::factory()->create();
    $agente = Agente::factory()->create(['area_id' => $area->id]);

    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Enfermedad Común',
        'requiere_intervencion_medica' => true,
        'unidad_medida' => 'dias',
        'tipo_dias' => 'corridos',
    ]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'fecha_inicio' => now()->subDays(3)->toDateString(),
        'justificacion_vence_en' => now()->subHour(),
        'justificacion_presentada_en' => null,
    ]);

    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
        'tipo_documento' => 'Certificado Médico',
    ]);

    expect(fn () => app(JustificacionService::class)->registrarDocumento($documento))
        ->toThrow(ValidationException::class);
});
