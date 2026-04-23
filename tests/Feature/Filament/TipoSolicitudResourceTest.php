<?php

use App\Filament\Admin\Resources\TipoSolicituds\TipoSolicitudResource;
use App\Models\TipoSolicitud;

beforeEach(function () {
    loginAsRole('administrador');
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
