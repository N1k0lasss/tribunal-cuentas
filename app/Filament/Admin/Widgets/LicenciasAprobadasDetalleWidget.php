<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Solicitud;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class LicenciasAprobadasDetalleWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?int $sort = 1;

    protected static string $view = 'filament.admin.widgets.licencias-aprobadas-detalle-widget';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('agente') ?? false;
    }

    /**
     * Total de solicitudes aprobadas del agente en el año actual.
     */
    public function getTotalAprobadas(): int
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
            ->map(fn($solicitud) => [
                'tipo'         => $solicitud->tipoSolicitud?->nombre ?? 'Sin tipo',
                'fecha_inicio' => $solicitud->fecha_inicio?->format('d/m/Y') ?? '-',
                'fecha_fin'    => $solicitud->fecha_fin?->format('d/m/Y') ?? '-',
                'dias'         => $solicitud->dias_solicitados,
            ])
            ->toArray();
    }

    /**
     * Acción que abre el modal con el detalle de licencias aprobadas.
     */
    public function verDetalleAction(): Action
    {
        return Action::make('verDetalle')
            ->label('Ver licencias aprobadas')
            ->icon('heroicon-m-clipboard-document-check')
            ->color('info')
            ->modalHeading('Licencias Aprobadas — ' . date('Y'))
            ->modalDescription('Listado de todas las licencias aprobadas en el año actual.')
            ->modalIcon('heroicon-o-check-badge')
            ->modalIconColor('info')
            ->modalWidth('3xl')
            ->modalContent(fn() => view(
                'filament.admin.widgets.licencias-aprobadas-modal',
                [
                    'licencias' => $this->getDetalleLicencias(),
                    'total'     => $this->getTotalAprobadas(),
                ]
            ))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar');
    }
}
