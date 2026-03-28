<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Saldo;
use App\Models\Solicitud;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MisSaldosStatsWidget extends BaseWidget
{
    public static function canView(): bool
    {
        return Auth::user()?->hasRole('agente') ?? false;
    }

    protected function getStats(): array
    {
        $agenteId = Auth::user()?->agente?->id;

        if (! $agenteId) {
            return [];
        }

        // Calcular licencias disponibles
        $saldos = Saldo::where('agente_id', $agenteId)
            ->where('anio', date('Y'))
            ->get();

        $diasDisponibles = $saldos->sum(fn ($saldo) => $saldo->dias_otorgados - $saldo->dias_usados);

        // Calcular licencias solicitadas (histórico total o anual, usamos total por ahora)
        $solicitudesAprobadas = Solicitud::where('agente_id', $agenteId)
            ->where('estado', 'aprobado')
            ->count();

        $solicitudesPendientes = Solicitud::where('agente_id', $agenteId)
            ->whereIn('estado', ['pendiente', 'en_revision'])
            ->count();

        return [
            Stat::make('Días Disponibles', max(0, $diasDisponibles).' días')
                ->description('Para el año en curso')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('Solicitudes Aprobadas', $solicitudesAprobadas)
                ->description('Histórico de licencias finalizadas')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),

            Stat::make('Trámites en Curso', $solicitudesPendientes)
                ->description('Solicitudes pendientes de firma')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
