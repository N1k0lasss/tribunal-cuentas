<?php

use App\Models\Agente;
use App\Models\Documento;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

it('sends database notification on creation of solicitud without required documents', function () {
    // 1. Crear tipo de solicitud que requiere adjunto
    $tipoConAdjunto = TipoSolicitud::factory()->create([
        'requiere_adjunto' => true,
    ]);

    // 2. Crear agente y usuario solicitante
    $userSolicitante = User::factory()->create();
    $userSolicitante->assignRole('agente');
    $agenteSolicitante = Agente::factory()->create(['user_id' => $userSolicitante->id]);

    // 3. Crear la solicitud sin documentos
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agenteSolicitante->id,
        'tipo_solicitud_id' => $tipoConAdjunto->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    // 4. Verificar que se creó la notificación en la base de datos para el usuario
    $notification = DB::table('notifications')
        ->where('notifiable_id', $userSolicitante->id)
        ->where('notifiable_type', User::class)
        ->first();

    expect($notification)->not->toBeNull();

    $notificationData = json_decode($notification->data, true);
    expect($notificationData['title'])->toBe('Documentación pendiente');
    expect($notificationData['body'])->toContain('Dispone de un plazo de 72 horas para adjuntar la documentación');
});

it('sends warning notification when 24h remain and rejects when 72h pass', function () {
    // 1. Crear el usuario Administrador y su Agente
    $adminUser = User::factory()->create();
    $adminUser->assignRole('administrador');
    $adminAgente = Agente::factory()->create(['user_id' => $adminUser->id]);

    // 2. Crear Tipo de Solicitud que requiere adjuntos
    $tipoConAdjunto = TipoSolicitud::factory()->create([
        'requiere_adjunto' => true,
    ]);

    // 3. Crear Agente solicitante
    $agenteSolicitante = Agente::factory()->create();
    $userSolicitante = User::factory()->create(['email' => 'agente@example.com']);
    $agenteSolicitante->update(['user_id' => $userSolicitante->id]);

    // Limpiar notificaciones de la creación inicial
    DB::table('notifications')->truncate();

    // CASO A: Solicitud con adjunto requerido, creada hace 50 horas, SIN documentos (DEBE RECIBIR ADVERTENCIA 24hs)
    Carbon::setTestNow(now()->subHours(50));
    $solicitudAdvertencia = Solicitud::factory()->create([
        'agente_id' => $agenteSolicitante->id,
        'tipo_solicitud_id' => $tipoConAdjunto->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    // CASO B: Solicitud con adjunto requerido, creada hace 73 horas, SIN documentos (DEBE RECHAZARSE)
    Carbon::setTestNow(now()->subHours(23)); // 73 horas en total antes del final
    $solicitudExpirar = Solicitud::factory()->create([
        'agente_id' => $agenteSolicitante->id,
        'tipo_solicitud_id' => $tipoConAdjunto->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    // Volver el tiempo al presente
    Carbon::setTestNow();

    // Limpiar notificaciones de creación automática
    DB::table('notifications')->truncate();

    // Ejecutar el comando Artisan
    $this->artisan('app:check-expired-documents')
        ->assertSuccessful()
        ->expectsOutput("Notificación de 24hs enviada al agente para la solicitud #{$solicitudAdvertencia->id}.")
        ->expectsOutput("Solicitud #{$solicitudExpirar->id} rechazada automáticamente con éxito.");

    // Verificar estados de las solicitudes
    expect($solicitudAdvertencia->refresh()->estado)->toBe(Solicitud::ESTADO_PENDIENTE);
    expect($solicitudExpirar->refresh()->estado)->toBe(Solicitud::ESTADO_RECHAZADO);

    // Verificar que se haya creado la notificación de 24 horas restantes
    $notification24h = DB::table('notifications')
        ->where('notifiable_id', $userSolicitante->id)
        ->where('notifiable_type', User::class)
        ->whereJsonContains('data->title', 'Plazo próximo a vencer')
        ->first();

    expect($notification24h)->not->toBeNull();
    $notifData = json_decode($notification24h->data, true);
    expect($notifData['body'])->toContain('Le quedan menos de 24 horas para adjuntar la documentación');

    // Verificar que no envíe notificación duplicada al volver a correr
    $this->artisan('app:check-expired-documents')
        ->assertSuccessful();

    $notifCount = DB::table('notifications')
        ->where('notifiable_id', $userSolicitante->id)
        ->where('notifiable_type', User::class)
        ->whereJsonContains('data->title', 'Plazo próximo a vencer')
        ->count();

    expect($notifCount)->toBe(1); // Mantiene solo una notificación
});

it('rechaza solicitudes cuyos documentos no tienen archivo físico', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('administrador');
    Agente::factory()->create(['user_id' => $adminUser->id]);

    $tipoConAdjunto = TipoSolicitud::factory()->create([
        'requiere_adjunto' => true,
    ]);

    $agenteSolicitante = Agente::factory()->create();

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agenteSolicitante->id,
        'tipo_solicitud_id' => $tipoConAdjunto->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'ruta' => 'documentos/no-existe.pdf',
    ]);

    $solicitud->forceFill(['created_at' => now()->subHours(73)])->saveQuietly();

    $this->artisan('app:check-expired-documents')
        ->assertSuccessful();

    expect($solicitud->refresh()->estado)->toBe(Solicitud::ESTADO_RECHAZADO);
});
