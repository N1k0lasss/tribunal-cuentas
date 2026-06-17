<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Solicitud;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class LicenciasPorAgentesCalendarWidget extends FullCalendarWidget
{
    protected static ?string $heading = 'Licencias Aprobadas por Día (Agentes)';

    protected static ?int $sort = 10;

    protected int | string | array $columnSpan = 'full';

    public ?array $selectedEventDetails = null;

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

            $events[] = [
                'title' => $enLicencia->count().' agentes de licencia',
                'start' => $date->toDateString(),
                'end' => $date->copy()->addDay()->toDateString(),
                'allDay' => true,
                'backgroundColor' => '#0f766e',
                'borderColor' => '#0f766e',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'tipo' => 'agentes',
                    'fecha' => $date->format('d/m/Y'),
                    'agentes' => $this->mapAgentes($enLicencia),
                ],
            ];
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
     * @return array<int, string>
     */
    private function mapAgentes(Collection $solicitudes): array
    {
        return $solicitudes
            ->map(function (Solicitud $solicitud): string {
                $agente = $solicitud->agente;
                $area = $agente?->area?->nombre;

                return trim(($agente?->apellido ?? '').', '.($agente?->nombre ?? '')).($area ? ' ('.$area.')' : '');
            })
            ->filter()
            ->sort()
            ->values()
            ->all();
    }

    public function onEventClick(array $event = []): void
    {
        $props = $event['extendedProps'] ?? [];

        $this->selectedEventDetails = [
            'titulo' => $event['title'] ?? 'Detalle del día',
            'fecha' => $props['fecha'] ?? null,
            'agentes' => $props['agentes'] ?? [],
        ];
    }
}
