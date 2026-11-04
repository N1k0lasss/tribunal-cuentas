@component('mail::message')
# Recuperación de contraseña

Su código de confirmación es:

@component('mail::panel')
<strong style="font-size: 28px; letter-spacing: 8px;">{{ $code }}</strong>
@endcomponent

El código vence en 10 minutos y puede utilizarse una sola vez. Si usted no solicitó este cambio, ignore este mensaje.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
