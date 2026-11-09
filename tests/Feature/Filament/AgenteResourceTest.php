<?php

use App\Filament\Admin\Resources\Agentes\AgenteResource;
use App\Models\Agente;

beforeEach(function () {
    $this->actingAs(usuarioConRol('administrador'));
});

it('can render Agentes list page', function () {
    $this->get(AgenteResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Agentes view page', function () {
    $agente = Agente::factory()->create();

    $this->get(AgenteResource::getUrl('view', ['record' => $agente]))
        ->assertSuccessful();
});

it('permite ver agentes a secretaría general y presidencia', function (string $rol) {
    $this->actingAs(usuarioConRol($rol));

    $this->get(AgenteResource::getUrl('index'))
        ->assertSuccessful();
})->with(['secretaria_general', 'presidencia']);

it('niega el listado de agentes a roles sin permiso', function (string $rol) {
    $this->actingAs(usuarioConRol($rol));

    $this->get(AgenteResource::getUrl('index'))
        ->assertForbidden();
})->with(['agente', 'jefatura', 'personal']);
