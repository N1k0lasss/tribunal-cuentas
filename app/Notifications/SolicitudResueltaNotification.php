<?php

namespace App\Notifications;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolicitudResueltaNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Solicitud $solicitud,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $estado = match ($this->solicitud->estado) {
            Solicitud::ESTADO_APROBADO => 'aprobada',
            Solicitud::ESTADO_RECHAZADO => 'rechazada',
            default => 'actualizada',
        };

        return (new MailMessage)
            ->subject('Resolución de solicitud de licencia')
            ->line("Su solicitud #{$this->solicitud->id} fue {$estado}.")
            ->line('Ingrese al sistema para revisar el detalle del trámite.');
    }
}
