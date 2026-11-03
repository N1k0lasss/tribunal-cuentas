<?php

use App\Models\LegajoApiCredential;
use App\Services\LegajoApiService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('uses the panel token before the legacy environment token', function () {
    config([
        'services.legajos.url' => 'https://legajos.test',
        'services.legajos.token' => 'token-del-env',
    ]);

    LegajoApiCredential::query()->create([
        'token' => 'token-del-panel',
    ]);

    Http::fake([
        '*' => Http::response(['personas' => []]),
    ]);

    expect(app(LegajoApiService::class)->listarPersonas())->toBe([]);

    Http::assertSent(fn (Request $request): bool => $request->hasHeader(
        'Authorization',
        'Bearer token-del-panel',
    ));
});

it('continues to use the environment token until one is loaded in the panel', function () {
    config([
        'services.legajos.url' => 'https://legajos.test',
        'services.legajos.token' => 'token-del-env',
    ]);

    Http::fake([
        '*' => Http::response(['personas' => []]),
    ]);

    expect(app(LegajoApiService::class)->listarPersonas())->toBe([]);

    Http::assertSent(fn (Request $request): bool => $request->hasHeader(
        'Authorization',
        'Bearer token-del-env',
    ));
});
