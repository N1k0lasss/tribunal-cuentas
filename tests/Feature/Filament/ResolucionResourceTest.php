<?php

use App\Filament\Resources\Resolucions\ResolucionResource;
use App\Models\Resolucion;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render Resolucions list page', function () {
    $this->get(ResolucionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Resolucions create page', function () {
    $this->get(ResolucionResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render Resolucions edit page', function () {
    $resolucion = Resolucion::factory()->create();

    $this->get(ResolucionResource::getUrl('edit', ['record' => $resolucion]))
        ->assertSuccessful();
});
