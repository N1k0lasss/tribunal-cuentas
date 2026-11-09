<?php

use App\Filament\Admin\Resources\TipoSolicituds\TipoSolicitudResource;
use App\Models\TipoSolicitud;

beforeEach(function () {
    $this->actingAs(usuarioConRol('administrador'));
});

it('can render TipoSolicituds list page', function () {
    $this->get(TipoSolicitudResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render TipoSolicituds create page', function () {
    $this->get(TipoSolicitudResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render TipoSolicituds edit page', function () {
    $tipoSolicitud = TipoSolicitud::factory()->create();

    $this->get(TipoSolicitudResource::getUrl('edit', ['record' => $tipoSolicitud]))
        ->assertSuccessful();
});

it('permite listar regímenes legales a un usuario sin rol administrador', function (string $rol) {
    $this->actingAs(usuarioConRol($rol));

    $this->get(TipoSolicitudResource::getUrl('index'))
        ->assertSuccessful();
})->with(['agente', 'jefatura', 'secretaria_general']);

it('niega crear y editar regímenes legales a un usuario sin rol administrador', function (string $rol) {
    $this->actingAs(usuarioConRol($rol));

    $tipoSolicitud = TipoSolicitud::factory()->create();

    $this->get(TipoSolicitudResource::getUrl('create'))
        ->assertForbidden();

    $this->get(TipoSolicitudResource::getUrl('edit', ['record' => $tipoSolicitud]))
        ->assertForbidden();
})->with(['agente', 'jefatura', 'secretaria_general']);
