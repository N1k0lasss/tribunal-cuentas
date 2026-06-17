<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Solicitud;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class LicenciasPorSectorCalendarWidget extends FullCalendarWidget
{
    protected static ?string $heading = 'Licencias Aprobadas por Día (Sectores)';

    protected static ?int $sort = 11;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['administrador', 'jefatura', 'secretaria_general', 'presidencia']) ?? false;
    }

    public function config(): array
    {
        return [
            'displayEventTime' => false,
            'eventDisplay' => 'block',
            'locale' => 'es',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek',
            ],
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $start = Carbon::parse($fetchInfo['start'])->startOfDay();
        $end = Carbon::parse($fetchInfo['end'])->subDay()->endOfDay();

        $solicitudes = Solicitud::query()
            ->where('estado', Solicitud::ESTADO_APROBADO)
            ->whereDate('fecha_inicio', '<=', $end)
            ->where(function ($query) use ($start) {
                $query
                    ->whereDate('fecha_fin', '>=', $start)
                    ->orWhereNull('fecha_fin');
            })
            ->with(['agente:id,nombre,apellido,area_id', 'agente.area:id,nombre'])
            ->get();

        $events = [];

        foreach (Carbon::parse($fetchInfo['start'])->startOfDay()->daysUntil(Carbon::parse($fetchInfo['end'])->startOfDay()) as $date) {
            $enLicencia = $solicitudes
                ->filter(fn (Solicitud $solicitud) => $this->solicitudIncluyeFecha($solicitud, $date))
                ->unique('agente_id')
                ->values();

            if ($enLicencia->isEmpty()) {
                continue;
            }

            $porArea = $this->agruparPorArea($enLicencia);

            foreach ($porArea as $areaNombre => $agentes) {
                $cantidad = count($agentes);

                $events[] = [
                    'title' => $areaNombre.': '.$cantidad,
                    'start' => $date->toDateString(),
                    'end' => $date->copy()->addDay()->toDateString(),
                    'allDay' => true,
                    'backgroundColor' => $this->colorPorCantidad($cantidad),
                    'borderColor' => $this->colorPorCantidad($cantidad),
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'area' => $areaNombre,
                        'cantidad' => $cantidad,
                        'agentes' => $agentes,
                    ],
                ];
            }
        }

        return $events;
    }

    private function solicitudIncluyeFecha(Solicitud $solicitud, Carbon $date): bool
    {
        $inicio = Carbon::parse($solicitud->fecha_inicio)->startOfDay();
        $fin = Carbon::parse($solicitud->fecha_fin ?? $solicitud->fecha_inicio)->endOfDay();

        return $date->betweenIncluded($inicio, $fin);
    }

    /**
     * @param Collection<int, Solicitud> $solicitudes
     * @return array<string, array<int, string>>
     */
    private function agruparPorArea(Collection $solicitudes): array
    {
        return $solicitudes
            ->groupBy(fn (Solicitud $solicitud) => $solicitud->agente?->area?->nombre ?? 'Sin área')
            ->map(function (Collection $grupo): array {
                return $grupo
                    ->map(fn (Solicitud $solicitud) => trim(($solicitud->agente?->apellido ?? '').', '.($solicitud->agente?->nombre ?? '')))
                    ->filter()
                    ->sort()
                    ->values()
                    ->all();
            })
            ->sortKeys()
            ->all();
    }

    private function colorPorCantidad(int $cantidad): string
    {
        if ($cantidad >= 5) {
            return '#b91c1c';
        }

        if ($cantidad >= 3) {
            return '#d97706';
        }

        return '#15803d';
    }
}
