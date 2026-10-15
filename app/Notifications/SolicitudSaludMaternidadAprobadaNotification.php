<?php

namespace App\Notifications;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolicitudSaludMaternidadAprobadaNotification extends Notification
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
        $agente = $this->solicitud->agente;
        $nombreAgente = $agente !== null
            ? trim(($agente->nombre ?? '').' '.($agente->apellido ?? ''))
            : 'Agente no disponible';

        return (new MailMessage)
            ->subject('Aprobación informativa - Salud y Maternidad')
            ->line('Se informa la aprobación de una solicitud del régimen Salud y Maternidad.')
            ->line("Solicitud #{$this->solicitud->id}")
            ->line("Agente: {$nombreAgente}")
            ->line('Esta notificación es informativa y no requiere acción.');
    }
}
