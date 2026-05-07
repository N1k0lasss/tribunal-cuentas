<?php

namespace App\Notifications;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolicitudVencimientoNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Solicitud $solicitud,
        private readonly string $tipo,
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
        $mensaje = $this->tipo === 'justificacion'
            ? 'Venció el plazo de 48 horas para presentar justificación médica.'
            : 'Venció el plazo de 48 horas para solicitar prórroga.';

        return (new MailMessage)
            ->subject('Vencimiento de plazo legal')
            ->line("Solicitud #{$this->solicitud->id}")
            ->line($mensaje)
            ->line('Contacte a RR.HH. para regularizar la situación si corresponde.');
    }
}
