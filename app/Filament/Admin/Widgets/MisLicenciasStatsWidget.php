<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Solicitud;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MisLicenciasStatsWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    protected string $view = 'filament.admin.widgets.mis-licencias-stats-widget';

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

        $solicitudesAprobadas = Solicitud::where('agente_id', $agenteId)
            ->where('estado', 'aprobado')
            ->whereYear('fecha_solicitud', $anioActual)
            ->count();

        $solicitudesPendientes = Solicitud::where('agente_id', $agenteId)
            ->whereIn('estado', ['pendiente', 'en_revision'])
            ->count();

        // Mini chart: solicitudes por mes (últimos 6 meses)
        $chartAprobadas = $this->getMonthlyChart($agenteId, 'aprobado');
        $chartPendientes = $this->getMonthlyChart($agenteId, ['pendiente', 'en_revision']);

        return [
            Stat::make('Solicitudes Aprobadas', $solicitudesAprobadas)
                ->description('Licencias finalizadas '.$anioActual.' (Clic para ver detalle)')
                ->descriptionIcon('heroicon-m-chevron-down')
                ->color('info')
                ->chart($chartAprobadas)
                ->extraAttributes([
                    'class' => 'ring-1 ring-info-600/20 cursor-pointer hover:ring-2 hover:ring-info-500/50 transition-all',
                    'x-on:click' => 'activeTab = activeTab === \'aprobadas\' ? null : \'aprobadas\'',
                    'title' => 'Haz clic para desplegar los detalles',
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
     * Detalle de cada solicitud aprobada del año actual.
     *
     * @return array<int, array{tipo: string, fecha_inicio: string, fecha_fin: string, dias: int}>
     */
    public function getDetalleLicencias(): array
    {
        $agenteId = Auth::user()?->agente?->id;

        if (! $agenteId) {
            return [];
        }

        return Solicitud::where('agente_id', $agenteId)
            ->where('estado', 'aprobado')
            ->whereYear('fecha_solicitud', (int) date('Y'))
            ->with('tipoSolicitud')
            ->orderByDesc('fecha_inicio')
            ->get()
            ->map(fn ($s) => [
                'tipo' => $s->tipoSolicitud?->nombre ?? 'Sin tipo',
                'fecha_inicio' => $s->fecha_inicio?->format('d/m/Y') ?? '-',
                'fecha_fin' => $s->fecha_fin?->format('d/m/Y') ?? '-',
                'dias' => $s->dias_solicitados,
            ])
            ->toArray();
    }

    /**
     * Total de licencias aprobadas del agente en el año actual.
     */
    public function getTotalLicencias(): int
    {
        $agenteId = Auth::user()?->agente?->id;

        if (! $agenteId) {
            return 0;
        }

        return Solicitud::where('agente_id', $agenteId)
            ->where('estado', 'aprobado')
            ->whereYear('fecha_solicitud', (int) date('Y'))
            ->count();
    }

    /**
     * Genera datos para mini-chart mensual (últimos 6 meses).
     *
     * @return array<int>
     */
    private function getMonthlyChart(int $agenteId, string|array $estado): array
    {
        $cacheKey = 'monthly_chart_'.$agenteId.'_'.(is_array($estado) ? implode('_', $estado) : $estado);

        return Cache::remember($cacheKey, 300, function () use ($agenteId, $estado) {
            $data = [];
            $sixMonthsAgo = now()->subMonths(5)->startOfMonth();

            $query = Solicitud::where('agente_id', $agenteId)
                ->where('fecha_solicitud', '>=', $sixMonthsAgo);

            if (is_array($estado)) {
                $query->whereIn('estado', $estado);
            } else {
                $query->where('estado', $estado);
            }

            $results = $query->get()
                ->groupBy(fn ($solicitud) => $solicitud->fecha_solicitud->format('Y-m'))
                ->map(fn ($group) => (object) ['total' => $group->count()]);
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $key = $date->format('Y-m');
                $data[] = (int) ($results[$key]->total ?? 0);
            }

            return $data;
        });
    }
}
