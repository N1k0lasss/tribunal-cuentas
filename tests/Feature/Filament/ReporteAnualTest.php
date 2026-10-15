<?php

use App\Filament\Admin\Pages\ReporteAnual;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Livewire;

beforeEach(function () {
    // Asegurar que los roles existen
    foreach (['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

it('allows admin to access the page', function () {
    $user = User::factory()->create();
    $user->assignRole('administrador');

    $this->actingAs($user)
        ->get(ReporteAnual::getUrl())
        ->assertSuccessful();
});

it('blocks agent from accessing the page', function () {
    $user = User::factory()->create();
    $user->assignRole('agente');

    $this->actingAs($user)
        ->get(ReporteAnual::getUrl())
        ->assertForbidden();
});

it('can render the page statistics', function () {
    $user = User::factory()->create();
    $user->assignRole('administrador');

    $this->actingAs($user);

    Livewire::test(ReporteAnual::class)
        ->assertSet('anio', (string) date('Y'))
        ->assertSee('Solicitudes Totales')
        ->assertSee('Total de Días');
});

it('can download the annual report pdf', function () {
    $user = User::factory()->create();
    $user->assignRole('administrador');

    $this->actingAs($user);

    Livewire::test(ReporteAnual::class)
        ->call('exportarPdf')
        ->assertFileDownloaded();
});
