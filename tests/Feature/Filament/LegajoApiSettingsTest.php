<?php

use App\Filament\Admin\Pages\LegajoApiSettings;
use App\Models\LegajoApiCredential;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('administrador', 'web');
    Role::findOrCreate('agente', 'web');
});

it('allows administrators to view the Legajos API settings', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $this->actingAs($admin)
        ->get(LegajoApiSettings::getUrl())
        ->assertSuccessful()
        ->assertSee('Estado de conexión')
        ->assertSee('Token de acceso');
});

it('denies access to non-administrators', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agente');

    $this->actingAs($agent)
        ->get(LegajoApiSettings::getUrl())
        ->assertForbidden();
});

it('stores the submitted token encrypted and clears the form value', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $this->actingAs($admin);

    Livewire::test(LegajoApiSettings::class)
        ->set('token', 'token-secreto-de-prueba')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSet('token', '')
        ->assertDontSee('token-secreto-de-prueba');

    $credential = LegajoApiCredential::query()->findOrFail(LegajoApiCredential::SINGLETON_ID);

    expect($credential->token)->toBe('token-secreto-de-prueba')
        ->and($credential->updated_by)->toBe($admin->id)
        ->and(DB::table('legajo_api_credentials')->where('id', $credential->id)->value('token'))
        ->not->toBe('token-secreto-de-prueba');
});

it('reports when the estimated 24-hour validity period has expired', function () {
    $credential = LegajoApiCredential::query()->create([
        'token' => 'token-de-prueba',
    ]);
    DB::table('legajo_api_credentials')
        ->where('id', $credential->id)
        ->update(['updated_at' => now()->subHours(25)]);

    $admin = User::factory()->create();
    $admin->assignRole('administrador');
    $this->actingAs($admin);

    Livewire::test(LegajoApiSettings::class)
        ->assertSee('El plazo estimado venció');
});
