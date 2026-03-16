<?php

use App\Filament\Admin\Resources\Agentes\AgenteResource;
use App\Models\Agente;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render Agentes list page', function () {
    $this->get(AgenteResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Agentes create page', function () {
    $this->get(AgenteResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render Agentes edit page', function () {
    $agente = Agente::factory()->create();

    $this->get(AgenteResource::getUrl('edit', ['record' => $agente]))
        ->assertSuccessful();
});
