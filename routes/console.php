<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:check-expired-documents')->daily();
// 10 minutos de expiración: si el comando se interrumpe, no queda bloqueado
// durante las 24 horas que usa withoutOverlapping() por defecto.
Schedule::command('app:enviar-licencias-legajos')->everyFiveMinutes()->withoutOverlapping(10);
