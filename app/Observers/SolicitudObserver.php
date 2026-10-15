<?php

namespace App\Observers;

use App\Models\Solicitud;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class SolicitudObserver
{
    /**
     * Handle the Solicitud "created" event.
     */
    public function created(Solicitud $solicitud): void
    {
        DB::afterCommit(function () use ($solicitud) {
            // Recargar relaciones necesarias
            $solicitud->loadMissing('tipoSolicitud', 'agente.user', 'documentos');

            // Si el tipo de solicitud requiere adjunto y el apartado de documentos sigue vacío
            if ($solicitud->tipoSolicitud?->requiere_adjunto && $solicitud->documentos->isEmpty()) {
                $user = $solicitud->agente?->user;

                if ($user) {
                    Notification::make()
                        ->title('Documentación pendiente')
                        ->warning()
                        ->body("¡Atención! Dispone de un plazo de 72 horas para adjuntar la documentación respaldatoria requerida para la solicitud N° {$solicitud->id}.")
                        ->persistent()
                        ->sendToDatabase($user);
                }
            }
        });
    }
}
