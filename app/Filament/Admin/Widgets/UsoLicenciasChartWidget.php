<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Solicitud;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class UsoLicenciasChartWidget extends ChartWidget
{
    protected ?string $heading = 'Estadísticas Anuales por Tipo de Licencia';

    protected static ?int $sort = 0;

    protected ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = null;

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('agente') ?? false;
    }

    protected function getData(): array
    {
        $agenteId = Auth::user()?->agente?->id;

        if (! $agenteId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $anioSeleccionado = (int) ($this->filter ?? now()->year);

        $solicitudes = Solicitud::query()
            ->where('agente_id', $agenteId)
            ->whereYear('fecha_solicitud', $anioSeleccionado)
            ->with('tipoSolicitud')
            ->get();

        $agrupadasPorTipo = $solicitudes
            ->groupBy(fn (Solicitud $solicitud) => $solicitud->tipoSolicitud?->nombre ?? 'Sin tipo')
            ->sortKeys();

        $labels = $agrupadasPorTipo->keys()->values()->all();

        if ($labels === []) {
            $labels = ['Sin registros'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad de solicitudes',
                    'data' => $agrupadasPorTipo->map(fn ($group) => $group->count())->values()->all() ?: [0],
                    'backgroundColor' => '#0f766e',
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Días solicitados',
                    'data' => $agrupadasPorTipo->map(fn ($group) => (int) $group->sum('dias_solicitados'))->values()->all() ?: [0],
                    'backgroundColor' => '#1d4ed8',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getFilters(): ?array
    {
        $primerRegistro = Solicitud::query()
            ->whereNotNull('fecha_solicitud')
            ->min('fecha_solicitud');

        $anioActual = now()->year;
        $primerAnio = $primerRegistro ? (int) date('Y', strtotime($primerRegistro)) : $anioActual;

        $anios = [];

        for ($anio = $anioActual; $anio >= $primerAnio; $anio--) {
            $anios[(string) $anio] = (string) $anio;
        }

        return $anios;
    }
}
