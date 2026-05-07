<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Solicitud;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class LicenseStatsWidget extends BaseWidget
{
    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['administrador', 'jefatura']) ?? false;
    }

    protected function getStats(): array
    {
        $pendientes = Solicitud::whereIn('estado', ['pendiente', 'en_revision'])->count();
        
        $ausentesHoy = Solicitud::where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', now())
            ->whereDate('fecha_fin', '>=', now())
            ->count();
            
        $aprobadasMes = Solicitud::where('estado', 'aprobado')
            ->whereMonth('fecha_solicitud', now()->month)
            ->whereYear('fecha_solicitud', now()->year)
            ->count();

        return [
            Stat::make('Solicitudes Pendientes', $pendientes)
                ->description('Esperando revisión urgente')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($pendientes > 0 ? 'danger' : 'gray'),

            Stat::make('Agentes Ausentes Hoy', $ausentesHoy)
                ->description('Personal fuera del Tribunal')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('warning'),

            Stat::make('Aprobadas este Mes', $aprobadasMes)
                ->description('Volumen de ausencias autorizadas')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
