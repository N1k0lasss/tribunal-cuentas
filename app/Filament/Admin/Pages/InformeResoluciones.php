<?php

namespace App\Filament\Admin\Pages;

use App\Models\Resolucion;
use App\Models\Solicitud;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;
use Filament\Actions\Action;

class InformeResoluciones extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected string $view = 'filament.admin.pages.informe-resoluciones';

    protected static ?string $title = 'Informe de Resoluciones';

    protected static ?string $navigationLabel = 'Informe de Resoluciones';

    protected static string|\UnitEnum|null $navigationGroup = 'Flujo de licencias';

    protected static ?string $slug = 'informe-resoluciones';

    protected static ?int $navigationSort = 8;

    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public string $estadoFiltro = '';

    public function mount(): void
    {
        $this->fechaDesde = date('Y-01-01');
        $this->fechaHasta = date('Y-m-d');
        $this->estadoFiltro = '';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['administrador', 'secretaria_general', 'presidencia']) ?? false;
    }

    public function getResoluciones(): \Illuminate\Database\Eloquent\Collection
    {
        $query = Resolucion::query()
            ->with(['solicitud.agente.area', 'solicitud.tipoSolicitud'])
            ->orderBy('fecha_emision', 'desc')
            ->orderBy('numero');

        if ($this->fechaDesde) {
            $query->whereDate('fecha_emision', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta) {
            $query->whereDate('fecha_emision', '<=', $this->fechaHasta);
        }

        if ($this->estadoFiltro) {
            $query->whereHas('solicitud', function ($q) {
                $q->where('estado', $this->estadoFiltro);
            });
        }

        return $query->get();
    }

    public function getResumenData(): array
    {
        $resoluciones = $this->getResoluciones();

        $totalResoluciones = $resoluciones->count();

        $estadosConteo = $resoluciones->groupBy(fn ($r) => $r->solicitud?->estado ?? 'sin_solicitud')
            ->map(fn ($group) => $group->count());

        $tiposConteo = $resoluciones->groupBy(fn ($r) => $r->solicitud?->tipoSolicitud?->nombre ?? 'Sin tipo')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->take(5);

        return [
            'total' => $totalResoluciones,
            'aprobadas' => $estadosConteo->get('aprobado', 0),
            'rechazadas' => $estadosConteo->get('rechazado', 0),
            'pendientes' => ($estadosConteo->get('pendiente', 0) + $estadosConteo->get('en_revision', 0)),
            'tiposTop' => $tiposConteo->toArray(),
        ];
    }

    public function exportarPdf(): mixed
    {
        $resoluciones = $this->getResoluciones();
        $resumen = $this->getResumenData();

        $pdf = Pdf::loadView('filament.admin.pages.informe-resoluciones-pdf', [
            'resoluciones' => $resoluciones,
            'resumen' => $resumen,
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'estadoFiltro' => $this->estadoFiltro,
        ]);

        $pdf->setPaper('a4', 'landscape');

        $nombreArchivo = 'informe_resoluciones_' . date('Ymd_His') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $nombreArchivo
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
