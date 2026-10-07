<?php

use App\Exceptions\LegajosApiException;
use App\Services\LegajosApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->api = app(LegajosApiClient::class);
});

it('consulta /personas con bearer token y accept json', function () {
    Http::fake([
        'https://legajos.test/api/personas' => Http::response([
            'is_active' => false,
            'personas' => [
                ['id' => 1, 'nombre' => 'Roberto Carlos', 'apellido' => 'Juárez', 'dni' => 20987654],
            ],
        ]),
    ]);

    $personas = $this->api->getPersonas();

    expect($personas)->toHaveCount(1)
        ->and($personas[0]['dni'])->toBe(20987654);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://legajos.test/api/personas'
            && $request->hasHeader('Authorization', 'Bearer token-de-prueba')
            && $request->hasHeader('Accept', 'application/json');
    });
});

it('devuelve null cuando /legajos/por-dni responde 404', function () {
    Http::fake([
        'https://legajos.test/api/legajos/por-dni/11111111' => Http::response([
            'message' => 'Persona no encontrado.',
            'status' => 404,
        ], 404),
    ]);

    expect($this->api->getLegajosPorDni('11111111'))->toBeNull();
});

it('devuelve el payload cuando /legajos/por-dni responde 200', function () {
    Http::fake([
        'https://legajos.test/api/legajos/por-dni/20987654' => Http::response([
            'persona' => ['id' => 2, 'nombre' => 'Roberto Carlos', 'dni' => 20987654],
            'legajo' => [
                ['id' => 2, 'num_legajo' => 1001, 'tipo_contrato' => 0],
            ],
        ]),
    ]);

    $payload = $this->api->getLegajosPorDni('20987654');

    expect($payload)->not->toBeNull()
        ->and($payload['legajo'][0]['num_legajo'])->toBe(1001);
});

it('lanza excepción cuando la API responde 401', function () {
    Http::fake([
        'https://legajos.test/api/personas' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    $this->api->getPersonas();
})->throws(LegajosApiException::class, 'Token inválido o expirado en la API de legajos (HTTP 401).');

it('lanza excepción cuando la API responde un error de servidor', function () {
    Http::fake([
        'https://legajos.test/api/personas' => Http::response('error interno', 500),
    ]);

    $this->api->getPersonas();
})->throws(LegajosApiException::class, 'La API de legajos respondió HTTP 500.');

it('envuelve los errores de red en una excepción de dominio', function () {
    Http::fake(fn () => throw new ConnectionException('Connection timed out'));

    $this->api->getPersonas();
})->throws(LegajosApiException::class, 'No se pudo conectar con la API de legajos: Connection timed out');

it('falla de forma explícita si no hay token configurado', function () {
    config(['services.legajos_api.token' => null]);

    Http::fake();

    $this->api->getPersonas();
})->throws(LegajosApiException::class, 'Falta configurar la variable LEGAJOS_API_TOKEN.');

it('reporta el estado de la API en el diagnóstico', function () {
    Http::fake([
        'https://legajos.test/api/personas' => Http::response([
            'is_active' => false,
            'personas' => [['dni' => 1]],
        ]),
    ]);

    $resultado = $this->api->verificarConexion();

    expect($resultado['ok'])->toBeTrue()
        ->and($resultado['personas'])->toBe(1)
        ->and($resultado['error'])->toBeNull();

    config(['services.legajos_api.token' => null]);

    $fallido = $this->api->verificarConexion();

    expect($fallido['ok'])->toBeFalse()
        ->and($fallido['error'])->toContain('LEGAJOS_API_TOKEN');
});
