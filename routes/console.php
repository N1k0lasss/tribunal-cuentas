<?php

use App\Models\Solicitud;
use App\Notifications\SolicitudVencimientoNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    Solicitud::query()
        ->whereNotNull('justificacion_vence_en')
        ->whereNull('justificacion_presentada_en')
        ->whereNull('justificacion_vencimiento_notificado_en')
        ->where('justificacion_vence_en', '<', now())
        ->with('agente.user')
        ->chunkById(100, function ($solicitudes): void {
            foreach ($solicitudes as $solicitud) {
                $email = $solicitud->agente?->user?->email;

                if ($email === null) {
                    continue;
                }

                Notification::route('mail', $email)
                    ->notify(new SolicitudVencimientoNotification($solicitud, 'justificacion'));

                $solicitud->forceFill([
                    'justificacion_vencimiento_notificado_en' => now(),
                ])->save();
            }
        });

    Solicitud::query()
        ->where('es_prorroga', true)
        ->whereNotNull('prorroga_vence_en')
        ->whereNull('prorroga_vencimiento_notificado_en')
        ->where('prorroga_vence_en', '<', now())
        ->with('agente.user')
        ->chunkById(100, function ($solicitudes): void {
            foreach ($solicitudes as $solicitud) {
                $email = $solicitud->agente?->user?->email;

                if ($email === null) {
                    continue;
                }

                Notification::route('mail', $email)
                    ->notify(new SolicitudVencimientoNotification($solicitud, 'prorroga'));

                $solicitud->forceFill([
                    'prorroga_vencimiento_notificado_en' => now(),
                ])->save();
            }
        });
})->hourly();
