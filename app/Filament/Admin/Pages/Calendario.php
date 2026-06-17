<?php

namespace App\Filament\Admin\Pages;

use App\Models\Area;
use App\Models\Solicitud;
use Carbon\CarbonInterface;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Calendario extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Calendario de Licencias';

    protected static ?string $title = 'Calendario de Licencias';

    protected static string|\UnitEnum|null $navigationGroup = 'Calendarios';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.admin.pages.calendario';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['administrador', 'jefatura', 'secretaria_general', 'presidencia']) ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    /**
     * @return array{desde: CarbonInterface, hasta: CarbonInterface, vista: string, area_id: int|null}
     */
    public function getFiltersFromRequest(): array
    {
        $hoy = now()->startOfDay();

        $desde = $this->parseDate((string) request()->query('desde', $hoy->toDateString()), $hoy);
        $hasta = $this->parseDate((string) request()->query('hasta', $hoy->copy()->addDays(13)->toDateString()), $hoy->copy()->addDays(13));

        if ($hasta->lt($desde)) {
            $hasta = $desde->copy();
        }

        if ($desde->diffInDays($hasta) > 90) {
            $hasta = $desde->copy()->addDays(90);
        }

        $vista = request()->query('vista', 'area');
        if (! in_array($vista, ['area', 'agentes'], true)) {
            $vista = 'area';
        }

        $areaId = request()->query('area_id');
        $areaId = is_numeric($areaId) ? (int) $areaId : null;

        if ($areaId !== null && ! Area::query()->whereKey($areaId)->exists()) {
            $areaId = null;
        }

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'vista' => $vista,
            'area_id' => $areaId,
        ];
    }

    /**
     * @return Collection<int, Area>
     */
    public function getAreas(): Collection
    {
        return Area::query()->orderBy('nombre')->get();
    }

    /**
     * @return array{ausentes_hoy: int, pico_periodo: int, sector_mas_afectado: string, agentes_sector_mas_afectado: int}
     */
    public function getResumen(): array
    {
        $filters = $this->getFiltersFromRequest();
        $rows = $this->buildCalendarRows($filters['desde'], $filters['hasta']);
        $hoyKey = now()->toDateString();

        $ausentesHoy = count($rows[$hoyKey]['agentes'] ?? []);

        $picoPeriodo = 0;
        $sectorMasAfectado = 'Sin datos';
        $agentesSector = 0;

        foreach ($rows as $row) {
            $totalDia = count($row['agentes']);
            if ($totalDia > $picoPeriodo) {
                $picoPeriodo = $totalDia;
            }

            foreach ($row['areas'] as $areaNombre => $agentes) {
                $cantidad = count($agentes);
                if ($cantidad > $agentesSector) {
                    $agentesSector = $cantidad;
                    $sectorMasAfectado = (string) $areaNombre;
                }
            }
        }

        return [
            'ausentes_hoy' => $ausentesHoy,
            'pico_periodo' => $picoPeriodo,
            'sector_mas_afectado' => $sectorMasAfectado,
            'agentes_sector_mas_afectado' => $agentesSector,
        ];
    }

    /**
     * @return array<int, array{fecha: string, area: string, cantidad: int, agentes: array<int, string>}>
     */
    public function getRowsPorArea(): array
    {
        $filters = $this->getFiltersFromRequest();
        $rows = $this->buildCalendarRows($filters['desde'], $filters['hasta']);
        $data = [];

        foreach ($rows as $row) {
            foreach ($row['areas'] as $area => $agentes) {
                if ($filters['area_id'] !== null && $this->getAreaNombreById($filters['area_id']) !== $area) {
                    continue;
                }

                $data[] = [
                    'fecha' => Carbon::parse($row['fecha'])->format('d/m/Y'),
                    'area' => (string) $area,
                    'cantidad' => count($agentes),
                    'agentes' => array_values($agentes),
                ];
            }
        }

        return $data;
    }

    /**
     * @return array<int, array{fecha: string, total: int, agentes: array<int, string>}>
     */
    public function getRowsPorAgentes(): array
    {
        $filters = $this->getFiltersFromRequest();
        $rows = $this->buildCalendarRows($filters['desde'], $filters['hasta']);
        $data = [];

        foreach ($rows as $row) {
            $agentes = array_values($row['agentes']);

            if ($filters['area_id'] !== null) {
                $areaNombre = $this->getAreaNombreById($filters['area_id']);
                $agentes = array_values(array_filter($agentes, fn (string $item) => str_ends_with($item, '('.$areaNombre.')')));
            }

            $data[] = [
                'fecha' => Carbon::parse($row['fecha'])->format('d/m/Y'),
                'total' => count($agentes),
                'agentes' => $agentes,
            ];
        }

        return $data;
    }

    /**
     * @return array<string, array{fecha: string, agentes: array<int, string>, areas: array<string, array<int, string>>}>
     */
    private function buildCalendarRows(CarbonInterface $desde, CarbonInterface $hasta): array
    {
        $periodo = $desde->copy()->daysUntil($hasta->copy()->addDay());
        $rows = [];

        foreach ($periodo as $dia) {
            $rows[$dia->toDateString()] = [
                'fecha' => $dia->toDateString(),
                'agentes' => [],
                'areas' => [],
            ];
        }

        $solicitudes = Solicitud::query()
            ->where('estado', Solicitud::ESTADO_APROBADO)
            ->whereDate('fecha_inicio', '<=', $hasta)
            ->where(function ($query) use ($desde) {
                $query->whereDate('fecha_fin', '>=', $desde)->orWhereNull('fecha_fin');
            })
            ->with(['agente:id,nombre,apellido,area_id', 'agente.area:id,nombre'])
            ->get();

        foreach ($solicitudes as $solicitud) {
            $agente = $solicitud->agente;
            if (! $agente) {
                continue;
            }

            $displayName = trim(($agente->apellido ?? '').', '.($agente->nombre ?? ''));
            $areaNombre = $agente->area?->nombre ?? 'Sin area';

            $inicio = Carbon::parse($solicitud->fecha_inicio)->startOfDay();
            $fin = Carbon::parse($solicitud->fecha_fin ?? $solicitud->fecha_inicio)->startOfDay();

            if ($inicio->lt($desde)) {
                $inicio = $desde->copy();
            }

            if ($fin->gt($hasta)) {
                $fin = $hasta->copy();
            }

            foreach ($inicio->daysUntil($fin->copy()->addDay()) as $dia) {
                $key = $dia->toDateString();
                if (! isset($rows[$key])) {
                    continue;
                }

                $rows[$key]['agentes'][$agente->id] = $displayName.' ('.$areaNombre.')';
                $rows[$key]['areas'][$areaNombre][$agente->id] = $displayName;
            }
        }

        foreach ($rows as &$row) {
            asort($row['agentes']);
            ksort($row['areas']);
            foreach ($row['areas'] as &$agentes) {
                asort($agentes);
            }
            unset($agentes);
        }
        unset($row);

        return $rows;
    }

    private function parseDate(string $value, CarbonInterface $default): CarbonInterface
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return $default->copy();
        }
    }

    private function getAreaNombreById(int $areaId): string
    {
        return (string) (Area::query()->whereKey($areaId)->value('nombre') ?? '');
    }
}
