<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $path = env('FILAMENT_PATH', 'sistema');
    $response = $this->get("/{$path}/login");
    $response->assertOk();
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $path = env('FILAMENT_PATH', 'sistema');
    $response = $this->get("/{$path}");
    $response->assertOk();
});
