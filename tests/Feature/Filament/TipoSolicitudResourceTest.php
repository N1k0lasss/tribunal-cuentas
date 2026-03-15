<?php

use App\Filament\Resources\TipoSolicituds\TipoSolicitudResource;
use App\Models\TipoSolicitud;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
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
