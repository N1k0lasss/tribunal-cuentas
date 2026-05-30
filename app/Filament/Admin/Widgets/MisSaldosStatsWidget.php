<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Saldo;
use App\Models\Solicitud;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MisSaldosStatsWidget extends BaseWidget
{
    protected static ?int $sort = -1;

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
        /** @var int $diasDisponibles */
        $saldos = Saldo::where('agente_id', $agenteId)
            ->where('anio', $anioActual)
            ->get();

        $diasDisponibles = $saldos->sum(fn($saldo) => $saldo->dias_otorgados - $saldo->dias_usados);

        // Solicitudes aprobadas del año
        /** @var int $solicitudesAprobadas */
        $solicitudesAprobadas = Solicitud::where('agente_id', $agenteId)
            ->where('estado', 'aprobado')
            ->whereYear('fecha_solicitud', $anioActual)
            ->count();

        // Solicitudes en curso (pendientes / en revisión)
        /** @var int $solicitudesPendientes */
        $solicitudesPendientes = Solicitud::where('agente_id', $agenteId)
            ->whereIn('estado', ['pendiente', 'en_revision'])
            ->count();

        // Mini chart: solicitudes por mes (últimos 6 meses)
        $chartAprobadas = $this->getMonthlyChart($agenteId, 'aprobado');
        $chartPendientes = $this->getMonthlyChart($agenteId, ['pendiente', 'en_revision']);

        return [
            Stat::make('Días Disponibles', max(0, $diasDisponibles) . ' días')
                ->description('Saldo para el año ' . $anioActual)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success')
                ->chart([max(0, $diasDisponibles), max(0, $diasDisponibles)])
                ->extraAttributes([
                    'class' => 'ring-1 ring-success-600/20',
                ]),

            Stat::make('Solicitudes Aprobadas', $solicitudesAprobadas)
                ->description('Licencias finalizadas ' . $anioActual)
                ->descriptionIcon('heroicon-m-check-badge')
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
     *
     * @param int $agenteId
     * @param string|array $estado
     * @return array<int>
     */
    private function getMonthlyChart(int $agenteId, string|array $estado): array
    {
        $cacheKey = 'monthly_chart_' . $agenteId . '_' . (is_array($estado) ? implode('_', $estado) : $estado);

        return Cache::remember($cacheKey, 300, function () use ($agenteId, $estado) {
            $data = [];
            $sixMonthsAgo = now()->subMonths(5)->startOfMonth();

            $query = Solicitud::selectRaw('YEAR(fecha_solicitud) as year, MONTH(fecha_solicitud) as month, COUNT(*) as total')
                ->where('agente_id', $agenteId)
                ->where('fecha_solicitud', '>=', $sixMonthsAgo);

            if (is_array($estado)) {
                $query->whereIn('estado', $estado);
            } else {
                $query->where('estado', $estado);
            }

            $results = $query->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(fn ($r) => $r->year . '-' . str_pad($r->month, 2, '0', STR_PAD_LEFT));

            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $key = $date->year . '-' . str_pad($date->month, 2, '0', STR_PAD_LEFT);
                $data[] = (int) ($results[$key]->total ?? 0);
            }

            return $data;
        });
    }
}
