<?php

use App\Http\Middleware\SessionTimeout;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Cierre automático por inactividad
|--------------------------------------------------------------------------
|
| Cubre el middleware SessionTimeout y el endpoint de "actividad real"
| (session.actividad). El marcador last_activity_time solo lo actualiza el
| endpoint de actividad, de modo que el polling de Livewire no reinicia el
| reloj de inactividad.
|
*/

test('cierra la sesión cuando se supera el tiempo de inactividad', function () {
    $user = User::factory()->create();
    $lifetime = (int) config('session.inactivity_lifetime');

    $this->withSession(['last_activity_time' => now()->subMinutes($lifetime + 1)])
        ->actingAs($user)
        ->get('/recibos')
        ->assertRedirect(route('filament.admin.auth.login').'?sesion_expirada=1')
        ->assertSessionHas('mensaje');

    $this->assertGuest();
});

test('el login muestra el aviso cuando llega con sesion_expirada=1', function () {
    $response = $this->get(route('filament.admin.auth.login').'?sesion_expirada=1');

    $response->assertOk();
    $response->assertSee(SessionTimeout::MENSAJE);
});

test('el login NO muestra el aviso en un acceso normal', function () {
    $response = $this->get(route('filament.admin.auth.login'));

    $response->assertOk();
    $response->assertDontSee(SessionTimeout::MENSAJE);
});

test('mantiene la sesión cuando hubo actividad reciente', function () {
    $user = User::factory()->create();

    $this->withSession(['last_activity_time' => now()->subSeconds(30)])
        ->actingAs($user)
        ->get('/recibos')
        ->assertOk();

    $this->assertAuthenticatedAs($user);
});

test('inicializa el marcador si todavía no existe', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/recibos')
        ->assertOk()
        ->assertSessionHas('last_activity_time');
});

test('el endpoint de actividad actualiza el marcador', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('session.actividad'))
        ->assertNoContent()
        ->assertSessionHas('last_activity_time');
});

test('el endpoint de actividad no falla sin sesión iniciada', function () {
    $this->post(route('session.actividad'))->assertNoContent();
});

/*
|--------------------------------------------------------------------------
| Endpoint de estado de sesión (session.estado)
|--------------------------------------------------------------------------
|
| El JS lo consulta ante un 419/401 para no mostrar el aviso de "sesión
| terminada" por un token vencido si la sesión sigue viva.
|
*/

test('el endpoint de estado confirma la sesión viva', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('session.estado'))
        ->assertOk()
        ->assertJson(['authenticated' => true]);
});

test('el endpoint de estado responde 200 con authenticated=false sin sesión', function () {
    // Nunca un redirect ni un 419: el fetch del script debe poder leer el JSON.
    $this->getJson(route('session.estado'))
        ->assertOk()
        ->assertJson(['authenticated' => false, 'reason' => 'session_expired']);
});

test('el cierre por inactividad NO se aplica a los endpoints del script', function () {
    // Aunque el marcador esté vencido, el latido y la consulta de estado deben
    // poder responder: si no, el JS no podría verificar nada.
    $user = User::factory()->create();
    $lifetime = (int) config('session.inactivity_lifetime');

    $this->withSession(['last_activity_time' => now()->subMinutes($lifetime + 1)])
        ->actingAs($user)
        ->post(route('session.actividad'))
        ->assertNoContent();

    $this->withSession(['last_activity_time' => now()->subMinutes($lifetime + 1)])
        ->actingAs($user)
        ->getJson(route('session.estado'))
        ->assertOk();
});

test('tras el cierre por inactividad el endpoint de estado confirma la sesión caída', function () {
    // Flujo real que sostiene el fix: una petición inactiva recibe 419 y, cuando
    // el JS consulta el estado para confirmar, el servidor responde
    // authenticated=false. Así el aviso se muestra (y no por un 419 aislado).
    $user = User::factory()->create();
    $lifetime = (int) config('session.inactivity_lifetime');

    $this->withSession(['last_activity_time' => now()->subMinutes($lifetime + 1)])
        ->actingAs($user)
        ->getJson('/recibos')
        ->assertStatus(419);

    $this->getJson(route('session.estado'))
        ->assertOk()
        ->assertJson(['authenticated' => false]);
});

test('una petición de Livewire inactiva recibe 419 en lugar de un redirect', function () {
    $user = User::factory()->create();
    $lifetime = (int) config('session.inactivity_lifetime');

    $this->withSession(['last_activity_time' => now()->subMinutes($lifetime + 1)])
        ->actingAs($user)
        ->withHeaders(['X-Livewire' => 'true'])
        ->get('/recibos')
        ->assertStatus(419)
        ->assertJson(['message' => SessionTimeout::MENSAJE])
        ->assertSessionHas('mensaje', SessionTimeout::MENSAJE);
});

test('una petición AJAX (JSON) inactiva recibe 419 en lugar de un redirect', function () {
    $user = User::factory()->create();
    $lifetime = (int) config('session.inactivity_lifetime');

    $this->withSession(['last_activity_time' => now()->subMinutes($lifetime + 1)])
        ->actingAs($user)
        ->getJson('/recibos')
        ->assertStatus(419)
        ->assertJson(['message' => SessionTimeout::MENSAJE])
        ->assertSessionHas('mensaje', SessionTimeout::MENSAJE);
});

test('la sesión del servidor dura más que la inactividad', function () {
    // Contrato que protege el aviso. La lógica vive en SessionTimeout (única
    // fuente de verdad); acá se verifica que la configuración lo respete.
    expect(SessionTimeout::sesionDelServidorSobreviveALaInactividad())->toBeTrue();
});

test('el chequeo del margen detecta una configuración inválida', function () {
    // Si la sesión del servidor no durara más que la inactividad, el aviso podría
    // no mostrarse. Verificamos que el chequeo (usado por AppServiceProvider para
    // advertir en producción) lo detecte.
    config([
        'session.lifetime' => 15,
        'session.inactivity_lifetime' => 15,
    ]);

    expect(SessionTimeout::sesionDelServidorSobreviveALaInactividad())->toBeFalse();
});

test('advierte en el log si la sesión del servidor no dura más que la inactividad', function () {
    Log::spy();

    config([
        'session.lifetime' => 15,
        'session.inactivity_lifetime' => 15,
    ]);

    AppServiceProvider::ensureSessionOutlivesInactivity();

    Log::shouldHaveReceived('warning')->once();
});
