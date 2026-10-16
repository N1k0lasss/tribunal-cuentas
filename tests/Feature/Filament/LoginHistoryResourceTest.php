<?php

use App\Filament\Admin\Resources\LoginHistories\LoginHistoryResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
});

it('can render Login Histories list page for administrator', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('administrador');

    $this->actingAs($adminUser)
        ->get(LoginHistoryResource::getUrl('index'))
        ->assertSuccessful();
});

it('cannot render Login Histories list page for agent', function () {
    $agentUser = User::factory()->create();
    $agentUser->assignRole('agente');

    $this->actingAs($agentUser)
        ->get(LoginHistoryResource::getUrl('index'))
        ->assertStatus(403);
});
