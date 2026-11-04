@component('mail::message')
<div style="color: #03357B; font-size: 22px; font-weight: 700; text-align: center;">
    Recuperación de contraseña
</div>

Su código de confirmación es:

@component('mail::panel')
<strong style="color: #03357B; font-size: 30px; letter-spacing: 8px;">{{ $code }}</strong>
@endcomponent

El código vence en 10 minutos y puede utilizarse una sola vez. Si usted no solicitó este cambio, ignore este mensaje.

Saludos,<br>
Tribunal de Cuentas de Santiago del Estero
@endcomponent
