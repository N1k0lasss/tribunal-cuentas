<?php

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles using the RoleSeeder
    $this->seed(\Database\Seeders\RoleSeeder::class);
});

test('login history is recorded inside working hours', function () {
    // Wednesday June 17, 2026 at 09:00:00 (Weekday, Inside Working Hours)
    $insideTime = Carbon::parse('2026-06-17 09:00:00', 'America/Argentina/Buenos_Aires');
    $this->travelTo($insideTime);

    $user = User::factory()->create();
    
    // Dispatch login event
    event(new Login('web', $user, false));

    $this->assertDatabaseHas('login_histories', [
        'user_id' => $user->id,
        'outside_working_hours' => false,
    ]);
});

test('login history is recorded outside working hours on weekdays', function () {
    // Wednesday June 17, 2026 at 15:00:00 (Weekday, Outside Working Hours)
    $outsideTime = Carbon::parse('2026-06-17 15:00:00', 'America/Argentina/Buenos_Aires');
    $this->travelTo($outsideTime);

    $user = User::factory()->create();

    // Dispatch login event
    event(new Login('web', $user, false));

    $this->assertDatabaseHas('login_histories', [
        'user_id' => $user->id,
        'outside_working_hours' => true,
    ]);
});

test('login history is recorded outside working hours on weekends', function () {
    // Saturday June 20, 2026 at 10:00:00 (Weekend)
    $weekendTime = Carbon::parse('2026-06-20 10:00:00', 'America/Argentina/Buenos_Aires');
    $this->travelTo($weekendTime);

    $user = User::factory()->create();

    // Dispatch login event
    event(new Login('web', $user, false));

    $this->assertDatabaseHas('login_histories', [
        'user_id' => $user->id,
        'outside_working_hours' => true,
    ]);
});

test('agent is allowed to access critical routes during working hours', function () {
    // Tuesday June 16, 2026 at 10:00:00 (Weekday, Inside)
    $time = Carbon::parse('2026-06-16 10:00:00', 'America/Argentina/Buenos_Aires');
    $this->travelTo($time);

    $agentUser = User::factory()->create();
    $agentUser->assignRole('agente');

    $this->actingAs($agentUser);

    $response = $this->get('/admin/solicituds');
    // It should not return 403. Depending on Filament view policy, it could return 200.
    $response->assertStatus(200);
});

test('agent is blocked from accessing critical routes outside working hours', function () {
    // Tuesday June 16, 2026 at 15:00:00 (Weekday, Outside)
    $time = Carbon::parse('2026-06-16 15:00:00', 'America/Argentina/Buenos_Aires');
    $this->travelTo($time);

    $agentUser = User::factory()->create();
    $agentUser->assignRole('agente');

    $this->actingAs($agentUser);

    $response = $this->get('/admin/solicituds');
    $response->assertStatus(403);
});

test('agent is blocked from accessing critical routes during weekends', function () {
    // Sunday June 21, 2026 at 11:00:00 (Weekend)
    $time = Carbon::parse('2026-06-21 11:00:00', 'America/Argentina/Buenos_Aires');
    $this->travelTo($time);

    $agentUser = User::factory()->create();
    $agentUser->assignRole('agente');

    $this->actingAs($agentUser);

    $response = $this->get('/admin/solicituds');
    $response->assertStatus(403);
});

test('exempt roles can access critical routes outside working hours', function ($role) {
    // Sunday June 21, 2026 at 11:00:00 (Weekend)
    $time = Carbon::parse('2026-06-21 11:00:00', 'America/Argentina/Buenos_Aires');
    $this->travelTo($time);

    $exemptUser = User::factory()->create();
    $exemptUser->assignRole($role);

    $this->actingAs($exemptUser);

    $response = $this->get('/admin/solicituds');
    // Exempt roles have full access or standard auth permission, but not blocked by working hours middleware (not 403).
    $this->assertNotEquals(403, $response->status());
})->with(['administrador', 'presidencia', 'personal']);

test('agent is blocked from executing critical Livewire actions outside working hours', function () {
    // Sunday June 21, 2026 at 11:00:00 (Weekend)
    $time = Carbon::parse('2026-06-21 11:00:00', 'America/Argentina/Buenos_Aires');
    $this->travelTo($time);

    $agentUser = User::factory()->create();
    $agentUser->assignRole('agente');

    $this->actingAs($agentUser);

    // Make a Livewire POST request simulating updating a Solicitud component
    $response = $this->postJson(route('default-livewire.update'), [
        'components' => [
            [
                'snapshot' => json_encode([
                    'class' => 'App\\Filament\\Admin\\Widgets\\SolicitudesPendientesWidget',
                ]),
                'updates' => [],
                'calls' => [
                    [
                        'method' => 'callMountedTableAction',
                        'params' => ['aprobar', 1],
                    ],
                ],
            ],
        ],
    ], [
        'X-Livewire' => 'true',
    ]);

    $response->assertStatus(403);
});
