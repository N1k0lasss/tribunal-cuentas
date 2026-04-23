<?php

namespace App\Services;

use App\Models\Agente;
use App\Models\Saldo;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaldoLicenciaService
{
    /**
     * @return Collection<int, Saldo>
     */
    public function obtenerSaldosVigentes(Agente $agente, TipoSolicitud $tipoSolicitud, ?int $anio = null): Collection
    {
        $anioBase = $anio ?? now()->year;

        return Saldo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo_saldo', $tipoSolicitud->nombre)
            ->whereBetween('anio', [$anioBase - 2, $anioBase])
            ->orderBy('anio')
            ->get();
    }

    public function calcularDiasDisponibles(Agente $agente, TipoSolicitud $tipoSolicitud, ?int $anio = null): int
    {
        return $this->obtenerSaldosVigentes($agente, $tipoSolicitud, $anio)
            ->sum(fn (Saldo $saldo): int => max(0, $saldo->dias_otorgados - $saldo->dias_usados));
    }

    public function consumirSaldoPorAprobacion(Solicitud $solicitud): void
    {
        DB::transaction(function () use ($solicitud): void {
            $solicitud->loadMissing('agente', 'tipoSolicitud');

            if ($solicitud->dias_solicitados === null || $solicitud->dias_solicitados <= 0) {
                return;
            }

            if ($solicitud->agente === null || $solicitud->tipoSolicitud === null) {
                throw ValidationException::withMessages([
                    'solicitud' => 'No se puede consumir saldo sin agente y régimen legal vinculados.',
                ]);
            }

            $pendiente = $solicitud->dias_solicitados;
            $saldos = Saldo::query()
                ->where('agente_id', $solicitud->agente->id)
                ->where('tipo_saldo', $solicitud->tipoSolicitud->nombre)
                ->whereBetween('anio', [now()->year - 2, now()->year])
                ->orderBy('anio')
                ->lockForUpdate()
                ->get();

            foreach ($saldos as $saldo) {
                if ($pendiente <= 0) {
                    break;
                }

                $disponible = max(0, $saldo->dias_otorgados - $saldo->dias_usados);

                if ($disponible === 0) {
                    continue;
                }

                $aConsumir = min($pendiente, $disponible);
                $saldo->increment('dias_usados', $aConsumir);
                $pendiente -= $aConsumir;
            }

            if ($pendiente > 0) {
                throw ValidationException::withMessages([
                    'dias_solicitados' => 'No hay saldo suficiente para completar la imputación de la licencia aprobada.',
                ]);
            }
        });
    }
}
