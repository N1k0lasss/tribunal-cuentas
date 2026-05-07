<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Saldo;
use App\Models\Solicitud;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class UsoLicenciasChartWidget extends ChartWidget
{
    protected ?string $heading = 'Resumen de Uso de Licencias';
    
    protected static ?int $sort = 0;

    protected ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('agente') ?? false;
    }

    protected function getData(): array
    {
        $agenteId = Auth::user()?->agente?->id;
        
        if (!$agenteId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $anioActual = now()->year;

        // 1. Calcular Días Disponibles Totales del año actual
        $diasDisponibles = Saldo::where('agente_id', $agenteId)
            ->where('anio', $anioActual)
            ->get()
            ->sum(fn($s) => max(0, $s->dias_otorgados - $s->dias_usados));

        // 2. Calcular Días Usados por Tipo de Licencia (Solicitudes aprobadas)
        $usoPorTipo = Solicitud::where('agente_id', $agenteId)
            ->where('estado', 'aprobado')
            ->whereYear('fecha_solicitud', $anioActual)
            ->with('tipoSolicitud')
            ->get()
            ->groupBy('tipoSolicitud.nombre')
            ->map(fn($group) => $group->sum('dias_solicitados'));

        $labels = ['Días Disponibles'];
        $data = [$diasDisponibles];
        $colors = ['#10b981']; // Verde para disponibles

        // Paleta de colores para los tipos de licencia
        $palette = ['#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444', '#ec4899', '#6366f1', '#14b8a6'];
        $i = 0;

        foreach ($usoPorTipo as $nombre => $dias) {
            if ($dias > 0) {
                $labels[] = $nombre;
                $data[] = $dias;
                $colors[] = $palette[$i % count($palette)];
                $i++;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Días',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => 'transparent',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
