<?php

use App\Exceptions\LegajoApiException;
use App\Services\LegajoApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Buenas prácticas al fallar la API: `getMessage()` es el texto apto para
 * mostrar al usuario (sin URL interna, sin DNI) y `detalle()` guarda el
 * detalle técnico para el log y el comando `legajos:sync`.
 */
it('no expone la URL interna ni el DNI en el mensaje de error', function () {
    config([
        'services.legajos.url' => 'https://legajos.test',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    Log::spy();

    Http::preventStrayRequests();
    Http::fake(function () {
        throw new ConnectionException(
            'cURL error 28: Connection timed out for https://legajos.test/api/legajos/por-dni/20987654'
        );
    });

    try {
        app(LegajoApiService::class)->obtenerPorDni('20987654');
        $this->fail('Se esperaba una LegajoApiException.');
    } catch (LegajoApiException $e) {
        expect($e->getMessage())
            ->not->toContain('https://')
            ->not->toContain('legajos.test')
            ->not->toContain('20987654')
            ->and($e->detalle())
            ->toContain('https://legajos.test/api/legajos/por-dni/20987654');
    }

    // El detalle técnico no se pierde: queda registrado en el log.
    Log::shouldHaveReceived('warning');
});

it('tampoco expone la URL cuando falla el listado de personas', function () {
    config([
        'services.legajos.url' => 'https://legajos.test',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    Log::spy();

    Http::preventStrayRequests();
    Http::fake(function () {
        throw new ConnectionException('cURL error 7 for https://legajos.test/api/personas');
    });

    try {
        app(LegajoApiService::class)->listarPersonas();
        $this->fail('Se esperaba una LegajoApiException.');
    } catch (LegajoApiException $e) {
        expect($e->getMessage())
            ->not->toContain('https://')
            ->and($e->detalle())
            ->toContain('https://legajos.test/api/personas');
    }

    Log::shouldHaveReceived('warning');
});
