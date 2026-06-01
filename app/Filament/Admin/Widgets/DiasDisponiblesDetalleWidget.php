<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Saldo;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DiasDisponiblesDetalleWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?int $sort = 0;

    protected static string $view = 'filament.admin.widgets.dias-disponibles-detalle-widget';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('agente') ?? false;
    }

    /**
     * Total de días disponibles del agente en el año actual.
     */
    public function getTotalDiasDisponibles(): int
    {
        $agenteId = Auth::user()?->agente?->id;

        if (! $agenteId) {
            return 0;
        }

        $saldos = Saldo::where('agente_id', $agenteId)
            ->where('anio', (int) date('Y'))
            ->get();

        return max(0, $saldos->sum(fn($saldo) => $saldo->dias_otorgados - $saldo->dias_usados));
    }

    /**
     * Detalle de saldos por tipo de licencia para el año actual.
     *
     * @return array<int, array{tipo: string, otorgados: int, usados: int, disponibles: int}>
     */
    public function getDetalleSaldos(): array
    {
        $agenteId = Auth::user()?->agente?->id;

        if (! $agenteId) {
            return [];
        }

        return Saldo::where('agente_id', $agenteId)
            ->where('anio', (int) date('Y'))
            ->get()
            ->map(fn($saldo) => [
                'tipo'        => $saldo->tipo_saldo,
                'otorgados'   => $saldo->dias_otorgados,
                'usados'      => $saldo->dias_usados,
                'disponibles' => max(0, $saldo->dias_otorgados - $saldo->dias_usados),
            ])
            ->sortByDesc('disponibles')
            ->values()
            ->toArray();
    }

    /**
     * Acción que abre el modal con el desglose por tipo de licencia.
     */
    public function verDetalleAction(): Action
    {
        return Action::make('verDetalle')
            ->label('Ver detalle por licencia')
            ->icon('heroicon-m-table-cells')
            ->color('success')
            ->modalHeading('Detalle de Días Disponibles — ' . date('Y'))
            ->modalDescription('Saldo de días disponibles por cada tipo de licencia en el año actual.')
            ->modalIcon('heroicon-o-calendar-days')
            ->modalIconColor('success')
            ->modalWidth('2xl')
            ->modalContent(fn() => view(
                'filament.admin.widgets.dias-disponibles-modal',
                [
                    'saldos' => $this->getDetalleSaldos(),
                    'total'  => $this->getTotalDiasDisponibles(),
                ]
            ))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar');
    }
}
