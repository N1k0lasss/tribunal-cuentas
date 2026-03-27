<?php

namespace App\Filament\Admin\Resources\Aprobacions\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumenLicenciasWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Aprobadas', '0')
                ->description('Licencias aprobadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'), // Esto le dará el color verde

            Stat::make('Pendientes', '1')
                ->description('Esperando revisión')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'), // Esto le dará el color amarillo

            Stat::make('Rechazadas', '0')
                ->description('Licencias denegadas')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),  // Esto le dará el color rojo

            Stat::make('Total Solicitudes', '1')
                ->description('Historial completo')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),    // Esto le dará el color azul
        ];
    }
}
