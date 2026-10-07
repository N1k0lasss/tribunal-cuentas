<?php

use App\Models\Agente;
use App\Models\Area;
use Illuminate\Support\Facades\Http;

function fakeApiLegajos(array $personas, array $consultasPorDni): void
{
    $stubs = [
        'https://legajos.test/api/personas' => Http::response([
            'is_active' => false,
            'personas' => $personas,
        ]),
    ];

    foreach ($consultasPorDni as $dni => $payload) {
        $stubs['https://legajos.test/api/legajos/por-dni/'.$dni] = is_array($payload)
            ? Http::response($payload, isset($payload['status']) ? (int) $payload['status'] : 200)
            : Http::response(['message' => 'Persona no encontrado.'], 404);
    }

    Http::fake($stubs);
}

it('sincroniza agentes, areas y legajos desde la API', function () {
    fakeApiLegajos(
        personas: [
            ['dni' => 20987654, 'nombre' => 'Roberto Carlos', 'apellido' => 'Juárez'],
            ['dni' => 27654321, 'nombre' => 'Silvia Beatriz', 'apellido' => 'Paz'],
        ],
        consultasPorDni: [
            20987654 => [
                'persona' => ['dni' => 20987654, 'nombre' => 'Roberto Carlos'],
                'legajo' => [[
                    'num_legajo' => 1001,
                    'fecha_de_ingreso' => '2015-03-01 00:00:00',
                    'tipo_contrato' => 0,
                    'cargo' => ['nombre' => 'Fiscal Jefe/a'],
                    'area' => ['nombre' => 'Fiscal General 1'],
                ]],
            ],
            27654321 => [
                'persona' => ['dni' => 27654321, 'nombre' => 'Silvia Beatriz'],
                'legajo' => [[
                    'num_legajo' => 1002,
                    'fecha_de_ingreso' => '2015-03-01 00:00:00',
                    'tipo_contrato' => 2,
                    'cargo' => ['nombre' => 'Oficial Principal de Despacho'],
                    'area' => ['nombre' => 'Personal'],
                ]],
            ],
        ],
    );

    $this->artisan('app:sync-agentes-desde-legajos')->assertSuccessful();

    $agente = Agente::where('dni', '20987654')->first();

    expect($agente)->not->toBeNull()
        ->and((int) $agente->legajo)->toBe(1001)
        ->and($agente->nombre)->toBe('Roberto Carlos')
        ->and($agente->apellido)->toBe('Juárez')
        ->and($agente->cargo)->toBe('Fiscal Jefe/a')
        ->and($agente->situacion_revista)->toBe('Permanente')
        ->and($agente->area->nombre)->toBe('Fiscal General 1')
        ->and($agente->fecha_ingreso->format('Y-m-d'))->toBe('2015-03-01');

    expect(Agente::count())->toBe(2)
        ->and(Area::pluck('nombre')->all())->toBe(['Fiscal General 1', 'Personal']);

    Http::assertSentCount(3);
});

it('ignora a las personas que no tienen legajo', function () {
    fakeApiLegajos(
        personas: [
            ['dni' => 22456700, 'nombre' => 'María marta', 'apellido' => 'Ledesma'],
        ],
        consultasPorDni: [
            22456700 => [
                'persona' => ['dni' => 22456700, 'nombre' => 'María marta'],
                'legajo' => [],
            ],
        ],
    );

    $this->artisan('app:sync-agentes-desde-legajos')
        ->expectsOutputToContain('Sin legajo (ignorados): 1')
        ->assertSuccessful();

    expect(Agente::count())->toBe(0)
        ->and(Area::count())->toBe(0);
});

it('continua cuando la API responde 404 para un DNI', function () {
    fakeApiLegajos(
        personas: [
            ['dni' => 11111111, 'nombre' => 'No', 'apellido' => 'Existe'],
            ['dni' => 20987654, 'nombre' => 'Roberto Carlos', 'apellido' => 'Juárez'],
        ],
        consultasPorDni: [
            11111111 => null,
            20987654 => [
                'persona' => ['dni' => 20987654, 'nombre' => 'Roberto Carlos'],
                'legajo' => [[
                    'num_legajo' => 1001,
                    'fecha_de_ingreso' => '2015-03-01 00:00:00',
                    'tipo_contrato' => 1,
                    'cargo' => ['nombre' => 'Auditor'],
                    'area' => ['nombre' => 'Auditoría General'],
                ]],
            ],
        ],
    );

    $this->artisan('app:sync-agentes-desde-legajos')
        ->expectsOutputToContain('No encontrados en la API: 1')
        ->expectsOutputToContain('Agentes sincronizados: 1')
        ->assertSuccessful();

    $agente = Agente::where('dni', '20987654')->first();

    expect($agente->situacion_revista)->toBe('Contratado')
        ->and(Agente::where('dni', '11111111')->exists())->toBeFalse();
});

it('no actualiza agentes existentes fuera del alcance de la API', function () {
    $existente = Agente::factory()->create([
        'dni' => '99999999',
        'nombre' => 'Carlos',
        'apellido' => 'Admin',
    ]);

    fakeApiLegajos(
        personas: [
            ['dni' => 20987654, 'nombre' => 'Roberto Carlos', 'apellido' => 'Juárez'],
        ],
        consultasPorDni: [
            20987654 => [
                'persona' => ['dni' => 20987654, 'nombre' => 'Roberto Carlos'],
                'legajo' => [[
                    'num_legajo' => 1001,
                    'fecha_de_ingreso' => '2015-03-01 00:00:00',
                    'tipo_contrato' => 0,
                    'cargo' => ['nombre' => 'Fiscal Jefe/a'],
                    'area' => ['nombre' => 'Fiscal General 1'],
                ]],
            ],
        ],
    );

    $this->artisan('app:sync-agentes-desde-legajos')->assertSuccessful();

    expect(Agente::count())->toBe(2)
        ->and(Agente::find($existente->id)->nombre)->toBe('Carlos');
});

it('devuelve error cuando la API no responde', function () {
    config(['services.legajos_api.base_url' => 'https://legajos.test/api']);

    Http::fake([
        'https://legajos.test/api/personas' => Http::response('sin servicio', 503),
    ]);

    $this->artisan('app:sync-agentes-desde-legajos')->assertFailed();
});

it('verifica la conexion con el comando de diagnostico', function () {
    fakeApiLegajos(
        personas: [
            ['dni' => 20987654, 'nombre' => 'Roberto Carlos', 'apellido' => 'Juárez'],
        ],
        consultasPorDni: [
            20987654 => [
                'persona' => ['dni' => 20987654, 'nombre' => 'Roberto Carlos'],
                'legajo' => [['num_legajo' => 1001]],
            ],
        ],
    );

    $this->artisan('app:legajos-api-check', ['--dni' => '20987654'])
        ->expectsOutputToContain('GET /personas -> OK: 1 personas')
        ->expectsOutputToContain('GET /legajos/por-dni/20987654 -> OK:')
        ->assertSuccessful();
});
