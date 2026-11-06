<?php

use App\Filament\Admin\Resources\Areas\AreaResource;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('administrador', 'web');

    $user = User::factory()->create();
    $user->assignRole('administrador');

    $this->actingAs($user);
});

it('can render Areas list page', function () {
    $this->get(AreaResource::getUrl('index'))
        ->assertSuccessful();
});
