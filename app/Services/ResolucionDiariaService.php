<?php

namespace App\Services;

use App\Models\Resolucion;
use App\Models\Solicitud;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResolucionDiariaService
{
    /**
     * Fecha de hoy en la zona horaria de negocio.
     */
    public static function hoy(): Carbon
    {
        return Carbon::today(config('app.business_timezone'));
    }

    /**
     * Licencias cuya aprobación final se registró en la fecha indicada.
     *
     * @return Collection<int, Solicitud>
     */
    public function solicitudesDelDia(?Carbon $fecha = null): Collection
    {
        $fecha ??= self::hoy();

        return Solicitud::query()
            ->where('estado', Solicitud::ESTADO_APROBADO)
            ->whereDate('fecha_aprobacion', $fecha)
            ->with(['agente.area', 'tipoSolicitud'])
            ->get()
            ->sortBy(fn (Solicitud $solicitud): string => mb_strtolower(
                ($solicitud->agente?->apellido ?? '').' '.($solicitud->agente?->nombre ?? '')
            ))
            ->values();
    }

    /**
     * Carga las licencias asociadas para poder imprimirlas.
     */
    public function cargarSolicitudes(Resolucion $resolucion): Resolucion
    {
        return $resolucion->load([
            'solicitudes.agente.area',
            'solicitudes.tipoSolicitud',
            'documentos',
        ]);
    }

    public function pdf(Resolucion $resolucion): string
    {
        $this->cargarSolicitudes($resolucion);

        return Pdf::loadView('pdf.resolucion-diaria', [
            'resolucion' => $resolucion,
        ])->output();
    }

    public function descargar(Resolucion $resolucion): StreamedResponse
    {
        return response()->streamDownload(
            fn () => print $this->pdf($resolucion),
            $this->nombreArchivo($resolucion),
        );
    }

    public function nombreArchivo(Resolucion $resolucion): string
    {
        $fecha = $resolucion->fecha_emision?->format('Y-m-d') ?? self::hoy()->format('Y-m-d');
        $numero = preg_replace('/[^A-Za-z0-9_-]+/', '-', $resolucion->numero) ?: (string) $resolucion->id;

        return "Resolucion_{$numero}_{$fecha}.pdf";
    }
}
