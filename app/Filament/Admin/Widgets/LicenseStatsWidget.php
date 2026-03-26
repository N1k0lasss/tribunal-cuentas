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
        return Auth::user()?->hasRole('administrador') ?? false;
    }

    protected function getStats(): array
    {
        $total = Solicitud::count();
        $pendientes = Solicitud::whereIn('estado', ['pendiente', 'en_revision'])->count();
        $aprobadas = Solicitud::where('estado', 'aprobado')->count();
        $rechazadas = Solicitud::where('estado', 'rechazado')->count();

        return [
            Stat::make('Total', $total)
                ->description('Todas las solicitudes')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make('Pendientes', $pendientes)
                ->description('En espera de aprobación')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Aprobadas', $aprobadas)
                ->description('Licencias concedidas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Rechazadas', $rechazadas)
                ->description('Solicitudes denegadas')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
