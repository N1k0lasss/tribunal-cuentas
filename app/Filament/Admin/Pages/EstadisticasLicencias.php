<?php

namespace App\Filament\Admin\Pages;

use App\Models\Agente;
use App\Models\Solicitud;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class EstadisticasLicencias extends Page
{
    protected string $view = 'filament.admin.pages.estadisticas-licencias';

    protected static ?string $navigationLabel = 'Estadísticas';

    protected static string|\UnitEnum|null $navigationGroup = 'Flujo de licencias';

    protected static ?string $slug = 'estadisticas-licencias';

    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['administrador', 'jefatura', 'secretaria_general', 'presidencia']) ?? false;
    }

    public function getFilters(): array
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

        $agentes = Agente::query()->orderBy('apellido')->get();

        return [
            'anios' => $anios,
            'agentes' => $agentes,
        ];
    }

    /**
     * @return array{
     *     anio: int,
     *     estado: string|null,
     *     agente: Agente|null,
     *     agente_label: string,
     *     total_solicitudes: int,
     *     total_dias: int,
     *     tipos_utilizados: int,
     *     por_tipo: Collection<int, array{tipo: string, cantidad: int, dias_totales: int, promedio_dias: float}>
     * }
     */
    public function getLicenciasStatsFromRequest(): array
    {
        $anio = (int) (request()->query('anio', now()->year));
        $estado = request()->query('estado', null);
        $agenteId = request()->query('agente_id', null);

        $baseQuery = Solicitud::query()
            ->whereYear('fecha_solicitud', $anio)
            ->when($estado, fn (Builder $q) => $q->where('estado', $estado))
            ->when($agenteId, fn (Builder $q) => $q->where('agente_id', $agenteId));

        $totalSolicitudes = (clone $baseQuery)->count();
        $totalDias = (int) (clone $baseQuery)->sum('dias_solicitados');
        $tiposUtilizados = (int) (clone $baseQuery)
            ->whereNotNull('tipo_solicitud_id')
            ->distinct()
            ->count('tipo_solicitud_id');

        $porTipo = (clone $baseQuery)
            ->leftJoin('tipo_solicituds as ts', 'ts.id', '=', 'solicituds.tipo_solicitud_id')
            ->selectRaw("COALESCE(ts.nombre, 'Sin tipo') as tipo")
            ->selectRaw('COUNT(*) as cantidad')
            ->selectRaw('COALESCE(SUM(solicituds.dias_solicitados), 0) as dias_totales')
            ->selectRaw('COALESCE(AVG(solicituds.dias_solicitados), 0) as promedio_dias')
            ->groupByRaw("COALESCE(ts.nombre, 'Sin tipo')")
            ->orderByDesc('cantidad')
            ->orderBy('tipo')
            ->get()
            ->map(fn ($row) => [
                'tipo' => (string) $row->tipo,
                'cantidad' => (int) $row->cantidad,
                'dias_totales' => (int) $row->dias_totales,
                'promedio_dias' => round((float) $row->promedio_dias, 1),
            ]);

        $agente = $agenteId ? Agente::find($agenteId) : null;

        return [
            'anio' => $anio,
            'estado' => $estado,
            'agente' => $agente,
            'agente_label' => $agente ? "{$agente->apellido}, {$agente->nombre}" : 'Todos los agentes',
            'total_solicitudes' => $totalSolicitudes,
            'total_dias' => $totalDias,
            'tipos_utilizados' => $tiposUtilizados,
            'por_tipo' => $porTipo,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_estadisticas_pdf')
                ->label('Exportar estadísticas PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    $stats = $this->getLicenciasStatsFromRequest();

                    $pdf = Pdf::loadView('filament.admin.pages.estadisticas-licencias-pdf', [
                        'stats' => $stats,
                    ]);

                    $nombreArchivo = 'estadisticas_licencias_'.$stats['anio'];

                    if ($stats['agente']) {
                        $nombreArchivo .= '_'.str_replace(' ', '_', $stats['agente']->apellido);
                    } else {
                        $nombreArchivo .= '_todos';
                    }

                    $nombreArchivo .= '.pdf';

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        $nombreArchivo
                    );
                }),
        ];
    }
}
