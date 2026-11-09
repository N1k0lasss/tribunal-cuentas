<?php

namespace App\Notifications;

use App\Models\LegajoLicenciaEnvio;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnvioLicenciaFallidoNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly LegajoLicenciaEnvio $envio,
        public readonly string $detalle,
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
        return (new MailMessage)
            ->subject('No se pudo enviar una licencia al Sistema de Legajos')
            ->line("La solicitud #{$this->envio->solicitud_id} quedó aprobada, pero no pudo registrarse en Legajos después de agotar los reintentos.")
            ->line("Detalle: {$this->detalle}")
            ->action('Revisar envíos de licencias', url('/sistema/envios-licencias-legajos'))
            ->line('La aprobación no se revirtió. Se puede reintentar desde el panel una vez resuelto el problema.');
    }
}
