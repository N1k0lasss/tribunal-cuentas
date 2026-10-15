<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Solicitud;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LicenciasResumenStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['administrador', 'jefatura', 'secretaria_general', 'presidencia']) ?? false;
    }

    protected function getStats(): array
    {
        $hoy = now()->startOfDay();
        $inicioSemana = now()->startOfWeek(Carbon::MONDAY);
        $finSemana = now()->endOfWeek(Carbon::SUNDAY);

        $base = Solicitud::query()
            ->where('estado', Solicitud::ESTADO_APROBADO)
            ->with(['agente.area']);

        $ausentesHoy = (clone $base)
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(function ($query) use ($hoy) {
                $query->whereDate('fecha_fin', '>=', $hoy)->orWhereNull('fecha_fin');
            })
            ->distinct('agente_id')
            ->count('agente_id');

        $solicitudesSemana = (clone $base)
            ->whereDate('fecha_inicio', '<=', $finSemana)
            ->where(function ($query) use ($inicioSemana) {
                $query->whereDate('fecha_fin', '>=', $inicioSemana)->orWhereNull('fecha_fin');
            })
            ->get();

        $picoSemanal = 0;
        foreach ($inicioSemana->daysUntil($finSemana->copy()->addDay()) as $dia) {
            $cantidad = $solicitudesSemana
                ->filter(fn (Solicitud $solicitud) => $this->incluyeDia($solicitud, $dia))
                ->unique('agente_id')
                ->count();

            $picoSemanal = max($picoSemanal, $cantidad);
        }

        $sectorMasAfectado = $solicitudesSemana
            ->filter(fn (Solicitud $solicitud) => $this->incluyeDia($solicitud, $hoy))
            ->groupBy(fn (Solicitud $solicitud) => $solicitud->agente?->area?->nombre ?? 'Sin área')
            ->map(fn ($grupo) => $grupo->unique('agente_id')->count())
            ->sortDesc();

        $nombreSector = $sectorMasAfectado->keys()->first() ?? 'Sin datos';
        $cantidadSector = $sectorMasAfectado->first() ?? 0;

        return [
            Stat::make('Ausentes Hoy', $ausentesHoy)
                ->description('Agentes con licencia aprobada hoy')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('warning'),

            Stat::make('Pico Semanal', $picoSemanal)
                ->description('Máxima cantidad simultánea en la semana')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color('info'),

            Stat::make('Sector Más Afectado Hoy', $nombreSector)
                ->description($cantidadSector.' agente(s) en licencia')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('danger'),
        ];
    }

    private function incluyeDia(Solicitud $solicitud, Carbon $dia): bool
    {
        $inicio = Carbon::parse($solicitud->fecha_inicio)->startOfDay();
        $fin = Carbon::parse($solicitud->fecha_fin ?? $solicitud->fecha_inicio)->endOfDay();

        return $dia->betweenIncluded($inicio, $fin);
    }
}
