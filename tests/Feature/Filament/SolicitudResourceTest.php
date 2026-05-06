<?php

use App\Filament\Admin\Resources\Solicituds\SolicitudResource;
use App\Models\Agente;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\WorkflowSolicitudesService;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Asegurar que los roles existen
    foreach (['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

it('can render Solicituds list page', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('agente');

    $this->actingAs($user)
        ->get(SolicitudResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Solicituds create page', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('agente');

    $this->actingAs($user)
        ->get(SolicitudResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render Solicituds edit page', function () {
    /** @var User $user */
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

it('agent can send pending solicitud to revision', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('agente');

    $agente = Agente::factory()->create(['user_id' => $user->id]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    app(WorkflowSolicitudesService::class)->enviarParaRevision($solicitud, $user);

    expect($solicitud->fresh()->estado)->toBe(Solicitud::ESTADO_EN_REVISION);
});

it('blocks edit page for solicitud en flujo', function () {
    /** @var User $user */
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
    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('jefatura');

    $this->actingAs($user)
        ->get(SolicitudResource::getUrl('create'))
        ->assertForbidden();
});
