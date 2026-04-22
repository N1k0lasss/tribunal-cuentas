<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Saldo;
use App\Models\Solicitud;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MisSaldosStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

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

        $anioActual = (int) date('Y');

        // Calcular licencias disponibles
        $saldos = Saldo::where('agente_id', $agenteId)
            ->where('anio', $anioActual)
            ->get();


        $diasDisponibles = $saldos->sum(fn ($saldo) => $saldo->dias_otorgados - $saldo->dias_usados);

        // Solicitudes aprobadas del año
        $solicitudesAprobadas = Solicitud::where('agente_id', $agenteId)
            ->where('estado', 'aprobado')
            ->whereYear('fecha_solicitud', $anioActual)
            ->count();


        // Solicitudes en curso (pendientes / en revisión)
        $solicitudesPendientes = Solicitud::where('agente_id', $agenteId)
            ->whereIn('estado', ['pendiente', 'en_revision'])
            ->count();

        // Mini chart: solicitudes por mes (últimos 6 meses)
        $chartAprobadas = $this->getMonthlyChart($agenteId, 'aprobado');
        $chartPendientes = $this->getMonthlyChart($agenteId, ['pendiente', 'en_revision']);

        return [
            Stat::make('Días Disponibles', max(0, $diasDisponibles).' días')
                ->description('Para el año en curso')
            Stat::make('Días Disponibles', max(0, $diasDisponibles) . ' días')
                ->description('Saldo para el año ' . $anioActual)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

                ->color('success')
                ->chart([max(0, $diasDisponibles), max(0, $diasDisponibles)])
                ->extraAttributes([
                    'class' => 'ring-1 ring-success-600/20',
                ]),

            Stat::make('Solicitudes Aprobadas', $solicitudesAprobadas)
                ->description('Licencias finalizadas ' . $anioActual)
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),

                ->color('info')
                ->chart($chartAprobadas)
                ->extraAttributes([
                    'class' => 'ring-1 ring-info-600/20',
                ]),

            Stat::make('Trámites en Curso', $solicitudesPendientes)
                ->description('Solicitudes pendientes de firma')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart($chartPendientes)
                ->extraAttributes([
                    'class' => 'ring-1 ring-warning-600/20',
                ]),
        ];
    }

    /**
     * Genera datos para mini-chart mensual (últimos 6 meses).
     */
    private function getMonthlyChart(int $agenteId, string|array $estado): array
    {
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $query = Solicitud::where('agente_id', $agenteId)
                ->whereYear('fecha_solicitud', $date->year)
                ->whereMonth('fecha_solicitud', $date->month);

            if (is_array($estado)) {
                $query->whereIn('estado', $estado);
            } else {
                $query->where('estado', $estado);
            }

            $data[] = $query->count();
        }

        return $data;
    }
}
