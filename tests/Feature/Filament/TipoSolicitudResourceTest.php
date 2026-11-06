<?php

use App\Filament\Admin\Resources\TipoSolicituds\TipoSolicitudResource;
use App\Models\TipoSolicitud;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('administrador', 'web');

    $user = User::factory()->create();
    $user->assignRole('administrador');

    $this->actingAs($user);
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
