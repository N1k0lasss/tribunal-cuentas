<?php

use App\Exceptions\LegajoApiException;
use App\Models\Agente;
use App\Models\Solicitud;
use App\Services\CumplimientoLegalService;
use App\Services\LegajoApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * La API de Legajos responde por-dni con el estado y la baja del legajo.
 */
function fakeLegajoConEstado(int $estado, ?array $baja = null): void
{
    config([
        'services.legajos.url' => 'https://legajos.test',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    Http::preventStrayRequests();
    Http::fake([
        'legajos.test/api/legajos/por-dni/*' => Http::response([
            'persona' => ['id' => 1, 'nombre' => 'Ana', 'dni' => 20987654],
            'legajo' => [[
                'id' => 1,
                'num_legajo' => 1000,
                'estado' => $estado,
                'fecha_de_ingreso' => '2010-01-01 00:00:00',
                'tipo_contrato' => 'Permanente',
                'baja' => $baja,
                'categoria' => ['id' => 1, 'nombre' => 'CAT 1', 'descripcion' => 'Prueba'],
                'cargo' => ['id' => 1, 'nombre' => 'Administrativo'],
                'area' => ['id' => 1, 'nombre' => 'Personal'],
            ]],
        ]),
    ]);
}

function validarEstadoDe(Solicitud $solicitud): void
{
    app(CumplimientoLegalService::class)->validarEstadoLegajo($solicitud);
}

it('bloquea la solicitud cuando la API informa que el legajo está dado de baja', function () {
    fakeLegajoConEstado(0, ['motivo' => 'Jubilación', 'fecha_baja' => '2025-12-31 00:00:00']);

    $agente = Agente::factory()->create(['dni' => '20987654']);
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);

    expect(fn () => validarEstadoDe($solicitud))
        ->toThrow(ValidationException::class, 'figura dado de baja');
});

it('no bloquea la solicitud cuando la API informa que el legajo está activo', function () {
    fakeLegajoConEstado(1);

    $agente = Agente::factory()->create(['dni' => '20987654']);
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);

    expect(fn () => validarEstadoDe($solicitud))
        ->not->toThrow(ValidationException::class);
});

it('usa el estado local como respaldo cuando la API no está disponible', function () {
    // En testing LEGAJOS_API_URL queda vacío (phpunit.xml): el servicio lanza
    // LegajoApiException y la validación debe caer al estado importado local.
    $agente = Agente::factory()->create([
        'dni' => '20987654',
        'estado_legajo' => 0,
        'motivo_baja' => 'Renuncia',
    ]);
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);

    expect(fn () => validarEstadoDe($solicitud))
        ->toThrow(ValidationException::class, 'figura dado de baja');
});

it('no bloquea cuando no se puede determinar el estado (API caída y sin dato local)', function () {
    $agente = Agente::factory()->create([
        'dni' => '20987654',
        'estado_legajo' => null,
    ]);
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);

    expect(fn () => validarEstadoDe($solicitud))
        ->not->toThrow(ValidationException::class);
});

it('no repite la consulta a la API tras un fallo (memoria de errores)', function () {
    config([
        'services.legajos.url' => 'https://legajos.test',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    Http::preventStrayRequests();
    Http::fake([
        'legajos.test/api/legajos/por-dni/*' => Http::response('', 500),
    ]);

    $servicio = app(LegajoApiService::class);

    // Primer intento: golpea la API (con sus reintentos) y falla.
    try {
        $servicio->obtenerPorDni('20987654');
        $this->fail('Se esperaba una LegajoApiException.');
    } catch (LegajoApiException) {
        // esperado
    }

    $trasElPrimerIntento = Http::recorded()->count();

    // Los intentos siguientes leen el error memorizado: no vuelven a la red.
    foreach (range(1, 3) as $intento) {
        try {
            $servicio->obtenerPorDni('20987654');
        } catch (LegajoApiException) {
            // esperado
        }
    }

    expect($trasElPrimerIntento)->toBeGreaterThan(0)
        ->and(Http::recorded()->count())->toBe($trasElPrimerIntento);
});
