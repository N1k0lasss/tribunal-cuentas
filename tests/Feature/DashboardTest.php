<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('guests are redirected to the login page', function () {
    $response = $this->get('/admin/login');
    $response->assertOk();
});

test('authenticated users can visit the dashboard', function () {
    Role::findOrCreate('administrador', 'web');
    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('administrador');
    $this->actingAs($user);

    $response = $this->get('/admin');
    $response->assertOk();
});
