<?php

use App\Models\Agente;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Services\CumplimientoLegalService;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * Solicitud cuyo tipo de licencia exige una antigüedad mínima en meses.
 */
function solicitudConAntiguedadRequerida(Agente $agente, int $mesesRequeridos): Solicitud
{
    $tipo = TipoSolicitud::factory()->create([
        'antiguedad_minima_meses' => $mesesRequeridos,
    ]);

    return Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
    ]);
}

function validarAntiguedadDe(Solicitud $solicitud): void
{
    app(CumplimientoLegalService::class)->validarAntiguedad($solicitud);
}

/**
 * Punto la API de Legajos a un host de prueba y responde por-dni con el
 * payload mínimo de la sección 3.1 de integracion-legajos-api.md
 * (fecha_de_ingreso con hora, como llega en la vida real).
 */
function fakeLegajosApi(string $fechaDeIngreso, int $status = 200, array $extra = []): void
{
    config([
        'services.legajos.url' => 'https://legajos.test',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    Http::preventStrayRequests();
    Http::fake([
        'legajos.test/api/legajos/por-dni/*' => Http::response(array_merge([
            'persona' => ['id' => 1, 'nombre' => 'Ana', 'dni' => 20987654, 'cuil' => 27209876540],
            'legajo' => [[
                'id' => 1,
                'num_legajo' => 1000,
                'estado' => 1,
                'fecha_de_ingreso' => $fechaDeIngreso.' 00:00:00',
                'tipo_contrato' => 0,
                'categoria' => ['id' => 1, 'nombre' => 'CAT 1', 'descripcion' => 'Prueba'],
                'cargo' => ['id' => 1, 'nombre' => 'Administrativo'],
                'area' => ['id' => 1, 'nombre' => 'Personal'],
            ]],
        ], $extra), $status),
    ]);
}

it('usa la fecha de ingreso local cuando la API de Legajos no está disponible', function () {
    // En testing LEGAJOS_API_URL queda vacío (phpunit.xml): el servicio lanza
    // LegajoApiException al instante y la validación debe caer al respaldo local.
    $agente = Agente::factory()->create([
        'fecha_ingreso' => now()->subMonths(3)->toDateString(),
    ]);
    $solicitud = solicitudConAntiguedadRequerida($agente, 6);

    expect(fn () => validarAntiguedadDe($solicitud))
        ->toThrow(ValidationException::class, 'antigüedad actual: 3 meses');
});

it('usa la fecha del legajo (API) como fuente de verdad, aunque la local diga lo contrario', function () {
    // Local: 20 años (pasaría el requisito). Legajo: 3 meses (no pasa).
    fakeLegajosApi(now()->subMonths(3)->toDateString());

    $agente = Agente::factory()->create([
        'dni' => '20987654',
        'fecha_ingreso' => now()->subYears(20)->toDateString(),
    ]);
    $solicitud = solicitudConAntiguedadRequerida($agente, 6);

    expect(fn () => validarAntiguedadDe($solicitud))
        ->toThrow(ValidationException::class, 'antigüedad actual: 3 meses');
});

it('aprueba con la antigüedad del legajo cuando la fecha local está desactualizada', function () {
    // Local: 3 meses (no pasaría). Legajo: 20 años (pasa).
    fakeLegajosApi(now()->subYears(20)->toDateString());

    $agente = Agente::factory()->create([
        'dni' => '24678901',
        'fecha_ingreso' => now()->subMonths(3)->toDateString(),
    ]);
    $solicitud = solicitudConAntiguedadRequerida($agente, 6);

    expect(fn () => validarAntiguedadDe($solicitud))
        ->not->toThrow(ValidationException::class);
});

it('usa la fecha local cuando la API responde 404 (persona inexistente)', function () {
    fakeLegajosApi(now()->subMonths(3)->toDateString(), 404, [
        'persona' => null,
        'legajo' => null,
        'message' => 'Persona no encontrado.',
    ]);

    $agente = Agente::factory()->create([
        'dni' => '99999999',
        'fecha_ingreso' => now()->subYears(20)->toDateString(),
    ]);
    $solicitud = solicitudConAntiguedadRequerida($agente, 6);

    expect(fn () => validarAntiguedadDe($solicitud))
        ->not->toThrow(ValidationException::class);
});

it('usa la fecha local cuando la persona existe pero no tiene legajo activo', function () {
    // Comportamiento real observado: 200 con "legajo": [].
    fakeLegajosApi(now()->subMonths(3)->toDateString(), 200, [
        'legajo' => [],
    ]);

    $agente = Agente::factory()->create([
        'dni' => '22456700',
        'fecha_ingreso' => now()->subYears(20)->toDateString(),
    ]);
    $solicitud = solicitudConAntiguedadRequerida($agente, 6);

    expect(fn () => validarAntiguedadDe($solicitud))
        ->not->toThrow(ValidationException::class);
});
