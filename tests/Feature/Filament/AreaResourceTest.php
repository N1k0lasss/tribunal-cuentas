<?php

use App\Filament\Admin\Resources\Areas\AreaResource;

beforeEach(function () {
    $this->actingAs(usuarioConRol('administrador'));
});

it('can render Areas list page', function () {
    $this->get(AreaResource::getUrl('index'))
        ->assertSuccessful();
});

it('permite ver áreas a secretaría general y presidencia', function (string $rol) {
    $this->actingAs(usuarioConRol($rol));

    $this->get(AreaResource::getUrl('index'))
        ->assertSuccessful();
})->with(['secretaria_general', 'presidencia']);

it('niega el listado de áreas a roles sin permiso', function (string $rol) {
    $this->actingAs(usuarioConRol($rol));

    $this->get(AreaResource::getUrl('index'))
        ->assertForbidden();
})->with(['agente', 'jefatura']);
