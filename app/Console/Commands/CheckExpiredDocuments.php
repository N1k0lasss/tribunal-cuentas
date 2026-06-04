<?php

namespace App\Console\Commands;

use App\Models\Aprobacion;
use App\Models\Solicitud;
use App\Models\User;
use App\Notifications\SolicitudResueltaNotification;
use App\Services\WorkflowAuditoriaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CheckExpiredDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-expired-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rechaza automáticamente las solicitudes que requieren documentación adjunta y no la presentaron tras 72 horas';

    /**
     * Execute the console command.
     */
    public function handle(WorkflowAuditoriaService $auditoriaService): int
    {
        // 1. Enviar advertencias de 24 horas restantes (solicitudes entre 48 y 72 horas sin adjuntos)
        $solicitudesAdvertencia = Solicitud::whereHas('tipoSolicitud', function ($query) {
                $query->where('requiere_adjunto', true);
            })
            ->whereIn('estado', [Solicitud::ESTADO_PENDIENTE, Solicitud::ESTADO_EN_REVISION])
            ->where('created_at', '<=', now()->subHours(48))
            ->where('created_at', '>', now()->subHours(72))
            ->whereDoesntHave('documentos')
            ->get();

        foreach ($solicitudesAdvertencia as $solicitud) {
            $user = $solicitud->agente?->user;
            if (!$user) {
                continue;
            }

            $bodyText = "¡Atención! Le quedan menos de 24 horas para adjuntar la documentación de la solicitud N° {$solicitud->id}.";

            $alreadyNotified = DB::table('notifications')
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->whereJsonContains('data->body', $bodyText)
                ->exists();

            if (!$alreadyNotified) {
                \Filament\Notifications\Notification::make()
                    ->title('Plazo próximo a vencer')
                    ->warning()
                    ->body($bodyText)
                    ->persistent()
                    ->sendToDatabase($user);

                $this->info("Notificación de 24hs enviada al agente para la solicitud #{$solicitud->id}.");
            }
        }

        // 2. Obtener solicitudes en estado pendiente o en revisión que requieren adjuntos y no tienen documentos (más de 72 horas)
        $solicitudes = Solicitud::whereHas('tipoSolicitud', function ($query) {
                $query->where('requiere_adjunto', true);
            })
            ->whereIn('estado', [Solicitud::ESTADO_PENDIENTE, Solicitud::ESTADO_EN_REVISION])
            ->where('created_at', '<=', now()->subHours(72))
            ->whereDoesntHave('documentos')
            ->get();

        if ($solicitudes->isEmpty()) {
            $this->info('No se encontraron solicitudes con documentación vencida.');
            return Command::SUCCESS;
        }

        // Buscar un usuario administrador para asociarlo al rechazo del sistema
        $adminUser = User::role('administrador')->first();
        if (!$adminUser) {
            $this->error('No se encontró un usuario administrador para procesar el rechazo automático.');
            return Command::FAILURE;
        }

        $adminAgenteId = $adminUser->agente?->id;
        if (!$adminAgenteId) {
            $this->error('El usuario administrador encontrado no tiene un agente asociado.');
            return Command::FAILURE;
        }

        $this->info("Procesando " . $solicitudes->count() . " solicitudes vencidas...");

        foreach ($solicitudes as $solicitud) {
            DB::transaction(function () use ($solicitud, $adminUser, $adminAgenteId, $auditoriaService) {
                $estadoAnterior = $solicitud->estado;
                $etapa = $solicitud->etapaActual() ?? Solicitud::ETAPA_JEFATURA;

                // 1. Crear el registro de rechazo en el historial de aprobaciones
                Aprobacion::create([
                    'solicitud_id' => $solicitud->id,
                    'aprobado_por' => $adminAgenteId,
                    'etapa' => $etapa,
                    'estado' => 'rechazado',
                    'observacion' => 'Rechazada automáticamente por falta de documentación (Plazo de 72 hs vencido).',
                    'fecha' => now(),
                ]);

                // 2. Actualizar el estado de la solicitud
                $solicitud->update(['estado' => Solicitud::ESTADO_RECHAZADO]);

                // 3. Registrar en auditoría
                $auditoriaService->registrar(
                    solicitud: $solicitud,
                    evento: 'solicitud_resuelta',
                    estadoAnterior: $estadoAnterior,
                    estadoNuevo: Solicitud::ESTADO_RECHAZADO,
                    etapa: $etapa,
                    detalle: 'Rechazada automáticamente por falta de documentación (Plazo de 72 hs vencido).',
                    actor: $adminUser
                );

                // 4. Enviar notificación por correo al agente
                $email = $solicitud->agente?->user?->email;
                if ($email !== null) {
                    Notification::route('mail', $email)
                        ->notify(new SolicitudResueltaNotification($solicitud));
                }

                $this->info("Solicitud #{$solicitud->id} rechazada automáticamente con éxito.");
            });
        }

        return Command::SUCCESS;
    }
}
