<?php

use App\Models\Agente;
use App\Models\Area;

/**
 * Listado mínimo de /api/personas + respuestas de por-dni (una con legajo
 * activo y otra sin legajo, como ocurre en la API real).
 */
function fakeApiLegajosParaSync(): void
{
    config([
        'services.legajos.url' => 'https://legajos.test',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    Illuminate\Support\Facades\Http::preventStrayRequests();
    Illuminate\Support\Facades\Http::fake([
        'legajos.test/api/personas' => Illuminate\Support\Facades\Http::response([
            'is_active' => false,
            'personas' => [
                ['id' => 1, 'nombre' => 'María marta Eugenia', 'apellido' => 'Ledesma', 'dni' => 22456700],
                ['id' => 2, 'nombre' => 'Roberto Carlos', 'apellido' => 'Juárez', 'dni' => 20987654],
            ],
        ]),
        'legajos.test/api/legajos/por-dni/20987654' => Illuminate\Support\Facades\Http::response([
            'persona' => ['id' => 2, 'nombre' => 'Roberto Carlos', 'dni' => 20987654, 'cuil' => 20209876540],
            'legajo' => [[
                'id' => 2,
                'num_legajo' => 1001,
                'estado' => 1,
                'fecha_de_ingreso' => '2015-03-01 00:00:00',
                'tipo_contrato' => 'Funcionario',
                'baja' => null,
                'categoria' => ['id' => 1, 'nombre' => 'CAT 1', 'descripcion' => 'Secretario General'],
                'cargo' => ['id' => 6, 'nombre' => 'Fiscal Jefe/a'],
                'area' => ['id' => 6, 'nombre' => 'Fiscal General 1'],
            ]],
        ]),
        'legajos.test/api/legajos/por-dni/22456700' => Illuminate\Support\Facades\Http::response([
            'persona' => ['id' => 1, 'nombre' => 'María marta Eugenia', 'dni' => 22456700],
            'legajo' => [],
        ]),
    ]);
}

it('importa los agentes de la API con y sin legajo activo', function () {
    fakeApiLegajosParaSync();

    $this->artisan('legajos:sync')->assertSuccessful();

    expect(Agente::count())->toBe(2);

    $conLegajo = Agente::where('dni', '20987654')->firstOrFail();
    expect($conLegajo)
        ->nombre->toBe('Roberto Carlos')
        ->apellido->toBe('Juárez')
        ->cargo->toBe('Fiscal Jefe/a')
        ->situacion_revista->toBe('Funcionario') // tipo_contrato "Funcionario"
        ->fecha_ingreso->toDateString()->toBe('2015-03-01')
        ->estado_legajo->toBe(1)
        ->fecha_baja->toBeNull()
        ->motivo_baja->toBeNull()
        ->area->nombre->toBe('Fiscal General 1');

    // Sin legajo activo: queda como referencia de identidad, sin datos del
    // legajo y con área placeholder (fecha_ingreso nullable).
    $sinLegajo = Agente::where('dni', '22456700')->firstOrFail();
    expect($sinLegajo)
        ->nombre->toBe('María marta Eugenia')
        ->apellido->toBe('Ledesma')
        ->cargo->toBeNull()
        ->situacion_revista->toBeNull()
        ->fecha_ingreso->toBeNull()
        ->area->nombre->toBe('Sin asignar');

    expect(Area::pluck('nombre')->all())
        ->toContain('Fiscal General 1', 'Sin asignar');
});

it('es idempotente: la segunda corrida no duplica ni toca los registros', function () {
    fakeApiLegajosParaSync();

    $this->artisan('legajos:sync')->assertSuccessful();
    $ids = Agente::orderBy('dni')->pluck('id', 'dni');

    $this->artisan('legajos:sync')->assertSuccessful();

    expect(Agente::count())->toBe(2)
        ->and(Agente::orderBy('dni')->pluck('id', 'dni'))->toEqual($ids);
});

it('permite sincronizar un único DNI con la opción --dni', function () {
    fakeApiLegajosParaSync();

    $this->artisan('legajos:sync', ['--dni' => '20987654'])->assertSuccessful();

    expect(Agente::count())->toBe(1)
        ->and(Agente::first()->dni)->toBe('20987654');
});

it('falla sin crear registros cuando la API de Legajos no está disponible', function () {
    // En testing LEGAJOS_API_URL queda vacío (phpunit.xml): el servicio lanza
    // LegajoApiException antes de hacer cualquier llamada de red.
    $this->artisan('legajos:sync')
        ->expectsOutputToContain('No se pudo consultar la API de Legajos')
        ->assertFailed();

    expect(Agente::count())->toBe(0);
});

it('falla si el DNI pedido no existe en la API', function () {
    fakeApiLegajosParaSync();

    $this->artisan('legajos:sync', ['--dni' => '99999999'])->assertFailed();

    expect(Agente::count())->toBe(0);
});

it('traduce el tipo_contrato de la API (texto actual y código viejo) a situacion_revista', function () {
    config([
        'services.legajos.url' => 'https://legajos.test',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    // La API envía el tipo como texto ("Funcionario"/"Locación"/"Permanente");
    // se admite también el código numérico viejo (0/1/2). "Raro" no existe:
    // debe quedar vacío y avisarse.
    $casos = [
        '10000001' => ['tipo' => 'Funcionario', 'esperado' => 'Funcionario'],
        '10000002' => ['tipo' => 'Locación', 'esperado' => 'Locación'],
        '10000003' => ['tipo' => 'Permanente', 'esperado' => 'Permanente'],
        '10000004' => ['tipo' => 2, 'esperado' => 'Permanente'],
        '10000005' => ['tipo' => 'Raro', 'esperado' => null],
    ];

    $fake = [
        'legajos.test/api/personas' => Illuminate\Support\Facades\Http::response([
            'personas' => array_map(
                fn (string $dni): array => ['dni' => (int) $dni, 'nombre' => 'Agente', 'apellido' => 'De Prueba'],
                array_keys($casos),
            ),
        ]),
    ];

    foreach ($casos as $dni => $caso) {
        $fake["legajos.test/api/legajos/por-dni/{$dni}"] = Illuminate\Support\Facades\Http::response([
            'persona' => ['dni' => (int) $dni, 'nombre' => 'Agente'],
            'legajo' => [[
                'num_legajo' => 1,
                'estado' => 1,
                'fecha_de_ingreso' => '2020-01-01 00:00:00',
                'tipo_contrato' => $caso['tipo'],
                'categoria' => ['nombre' => 'CAT 1', 'descripcion' => 'Prueba'],
                'cargo' => ['nombre' => 'Administrativo'],
                'area' => ['nombre' => 'Personal'],
            ]],
        ]);
    }

    Illuminate\Support\Facades\Http::preventStrayRequests();
    Illuminate\Support\Facades\Http::fake($fake);

    $this->artisan('legajos:sync')
        ->expectsOutputToContain('Valores de tipo_contrato sin mapear')
        ->assertSuccessful();

    $situaciones = Agente::pluck('situacion_revista', 'dni');

    expect($situaciones['10000001'])->toBe('Funcionario')
        ->and($situaciones['10000002'])->toBe('Locación')
        ->and($situaciones['10000003'])->toBe('Permanente')
        ->and($situaciones['10000004'])->toBe('Permanente')
        ->and($situaciones['10000005'])->toBeNull();
});

it('importa los legajos dados de baja marcándolos con estado, motivo y fecha', function () {
    config([
        'services.legajos.url' => 'https://legajos.test',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    Illuminate\Support\Facades\Http::preventStrayRequests();
    Illuminate\Support\Facades\Http::fake([
        'legajos.test/api/personas' => Illuminate\Support\Facades\Http::response([
            'personas' => [
                ['dni' => 20000000, 'nombre' => 'Baja', 'apellido' => 'Prueba'],
            ],
        ]),
        'legajos.test/api/legajos/por-dni/20000000' => Illuminate\Support\Facades\Http::response([
            'persona' => ['dni' => 20000000, 'nombre' => 'Baja'],
            'legajo' => [[
                'num_legajo' => 5,
                'estado' => 0,
                'fecha_de_ingreso' => '2010-05-01 00:00:00',
                'tipo_contrato' => 'Locación',
                'baja' => ['motivo' => 'Jubilación', 'fecha_baja' => '2025-12-31 00:00:00'],
                'categoria' => ['nombre' => 'CAT 2', 'descripcion' => 'Prueba'],
                'cargo' => ['nombre' => 'Administrativo'],
                'area' => ['nombre' => 'Personal'],
            ]],
        ]),
    ]);

    $this->artisan('legajos:sync')
        ->expectsOutputToContain('Legajos dados de baja')
        ->assertSuccessful();

    $agente = Agente::where('dni', '20000000')->firstOrFail();

    expect($agente)
        ->estado_legajo->toBe(0)
        ->motivo_baja->toBe('Jubilación')
        ->fecha_baja->toDateString()->toBe('2025-12-31')
        ->situacion_revista->toBe('Locación')
        ->and($agente->estaDeBaja())->toBeTrue();
});
