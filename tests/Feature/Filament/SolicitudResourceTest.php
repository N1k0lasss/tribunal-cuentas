<?php

use App\Filament\Admin\Resources\Solicituds\Pages\ListSolicituds;
use App\Filament\Admin\Resources\Solicituds\SolicitudResource;
use App\Models\Agente;
use App\Models\Resolucion;
use App\Models\Solicitud;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Asegurar que los roles existen
    foreach (['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

it('can render Solicituds list page', function () {
    $user = User::factory()->create();
    $user->assignRole('agente');

    $this->actingAs($user)
        ->get(SolicitudResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Solicituds create page', function () {
    $user = User::factory()->create();
    $user->assignRole('agente');

    $this->actingAs($user)
        ->get(SolicitudResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render Solicituds edit page', function () {
    $user = User::factory()->create();
    $user->assignRole('agente');

    // Crear un agente vinculado al user
    $agente = Agente::factory()->create(['user_id' => $user->id]);

    // Crear una solicitud pendiente del agente
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'estado' => 'pendiente',
    ]);

    $this->actingAs($user)
        ->get(SolicitudResource::getUrl('edit', ['record' => $solicitud]))
        ->assertSuccessful();
});

it('blocks edit page for solicitud en flujo', function () {
    $user = User::factory()->create();
    $user->assignRole('agente');

    $agente = Agente::factory()->create(['user_id' => $user->id]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'estado' => 'en_revision',
    ]);

    $this->actingAs($user)
        ->get(SolicitudResource::getUrl('edit', ['record' => $solicitud]))
        ->assertForbidden();
});

it('blocks create page for non-agente role', function () {
    $user = User::factory()->create();
    $user->assignRole('jefatura');

    $this->actingAs($user)
        ->get(SolicitudResource::getUrl('create'))
        ->assertForbidden();
});

it('muestra la resolución del día en el detalle de la solicitud', function () {
    $user = User::factory()->create();
    $user->assignRole('administrador');

    $this->actingAs($user);

    $resolucion = Resolucion::factory()->daily()->create([
        'numero' => 'RES-DIA-001',
    ]);

    $solicitud = Solicitud::factory()->create([
        'estado' => Solicitud::ESTADO_APROBADO,
    ]);

    $resolucion->solicitudes()->attach($solicitud);

    Livewire::test(ListSolicituds::class)
        ->mountAction(TestAction::make('view')->table($solicitud))
        ->assertMountedActionModalSee('RES-DIA-001');
});
