<?php

namespace App\Filament\Admin\Pages;

use App\Models\Solicitud;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;
use Filament\Actions\Action;

class ReporteAnual extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected string $view = 'filament.admin.pages.reporte-anual';

    protected static ?string $title = 'Reporte Anual';

    protected static ?string $navigationLabel = 'Reporte Anual';

    protected static string|\UnitEnum|null $navigationGroup = 'Flujo de licencias';

    protected static ?string $slug = 'reporte-anual';

    protected static ?int $navigationSort = 7;

    public string $anio;

    public function mount(): void
    {
        $this->anio = (string) date('Y');
    }

    public static function canAccess(): bool
    {
        // Solo administradores, secretaria general y presidencia pueden acceder a este reporte anual
        return auth()->user()?->hasRole(['administrador', 'secretaria_general', 'presidencia']) ?? false;
    }

    public function getAnios(): array
    {
        $primerAnio = Solicitud::min('fecha_solicitud');
        $primerAnio = $primerAnio ? (int) date('Y', strtotime($primerAnio)) : (int) date('Y');
        $anioActual = (int) date('Y');

        $anios = [];
        for ($y = $anioActual; $y >= $primerAnio; $y--) {
            $anios[(string) $y] = (string) $y;
        }

        return $anios;
    }

    public function getReportData(): array
    {
        $year = $this->anio;

        $solicitudes = Solicitud::query()
            ->whereYear('fecha_solicitud', $year)
            ->with(['agente.area', 'tipoSolicitud'])
            ->get();

        $totalSolicitudes = $solicitudes->count();
        $totalDias = $solicitudes->sum('dias_solicitados');
        $totalAprobadas = $solicitudes->where('estado', 'aprobado')->count();
        $totalRechazadas = $solicitudes->where('estado', 'rechazado')->count();
        $totalPendientes = $solicitudes->whereIn('estado', ['pendiente', 'en_revision'])->count();

        // Licencias más solicitadas
        $licencias = $solicitudes->groupBy('tipo_solicitud_id')
            ->map(function ($group) use ($totalSolicitudes) {
                $first = $group->first();
                $cantidad = $group->count();
                return [
                    'nombre' => $first->tipoSolicitud->nombre ?? 'Desconocido',
                    'cantidad' => $cantidad,
                    'dias' => $group->sum('dias_solicitados'),
                    'porcentaje' => $totalSolicitudes > 0 ? round(($cantidad / $totalSolicitudes) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('cantidad')
            ->values()
            ->all();

        // Solicitudes por Área
        $areas = $solicitudes->groupBy(function ($solicitud) {
                return $solicitud->agente?->area_id ?? 0;
            })
            ->map(function ($group) use ($totalSolicitudes) {
                $first = $group->first();
                $areaNombre = $first->agente?->area?->nombre ?? 'Sin Área';
                $cantidad = $group->count();
                return [
                    'nombre' => $areaNombre,
                    'cantidad' => $cantidad,
                    'dias' => $group->sum('dias_solicitados'),
                    'porcentaje' => $totalSolicitudes > 0 ? round(($cantidad / $totalSolicitudes) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('cantidad')
            ->values()
            ->all();

        $mostRequestedLicense = count($licencias) > 0 ? $licencias[0]['nombre'] : 'Ninguna';
        $mostActiveArea = count($areas) > 0 ? $areas[0]['nombre'] : 'Ninguna';

        return [
            'totalSolicitudes' => $totalSolicitudes,
            'totalDias' => $totalDias,
            'totalAprobadas' => $totalAprobadas,
            'totalRechazadas' => $totalRechazadas,
            'totalPendientes' => $totalPendientes,
            'mostRequestedLicense' => $mostRequestedLicense,
            'mostActiveArea' => $mostActiveArea,
            'licencias' => $licencias,
            'areas' => $areas,
        ];
    }

    public function exportarPdf()
    {
        $data = $this->getReportData();
        $pdf = Pdf::loadView('filament.admin.pages.reporte-anual-pdf', [
            'data' => $data,
            'anio' => $this->anio,
        ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'reporte_anual_licencias_' . $this->anio . '.pdf'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(fn () => $this->exportarPdf()),
        ];
    }
}
