<?php

namespace App\Services;

use App\Models\Agente;
use App\Models\Saldo;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use Illuminate\Database\Eloquent\Collection;

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
        $solicitud->loadMissing('agente', 'tipoSolicitud');

        if ($solicitud->dias_solicitados === null || $solicitud->dias_solicitados <= 0) {
            return;
        }

        $pendiente = $solicitud->dias_solicitados;
        $saldos = $this->obtenerSaldosVigentes($solicitud->agente, $solicitud->tipoSolicitud)
            ->sortBy('anio');

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
    }
}
