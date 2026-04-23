<?php

use App\Filament\Admin\Resources\Aprobacions\AprobacionResource;
use App\Models\Aprobacion;

use function Pest\Laravel\get;

beforeEach(function () {
    loginAsRole('agente');
});

it('cannot render Aprobacions list page for agente role', function () {
    get(AprobacionResource::getUrl('index'))
        ->assertForbidden();
});

it('cannot access Aprobacions create page', function () {
    get(AprobacionResource::getUrl('create'))
        ->assertForbidden();
});

it('cannot access Aprobacions edit page for unowned record', function () {
    $aprobacion = Aprobacion::factory()->create();

    get(AprobacionResource::getUrl('edit', ['record' => $aprobacion]))
        ->assertNotFound();
});
