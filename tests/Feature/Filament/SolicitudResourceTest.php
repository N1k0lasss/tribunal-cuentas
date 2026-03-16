<?php

use App\Filament\Admin\Resources\Solicituds\SolicitudResource;
use App\Models\Solicitud;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render Solicituds list page', function () {
    $this->get(SolicitudResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Solicituds create page', function () {
    $this->get(SolicitudResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render Solicituds edit page', function () {
    $solicitud = Solicitud::factory()->create();

    $this->get(SolicitudResource::getUrl('edit', ['record' => $solicitud]))
        ->assertSuccessful();
});
