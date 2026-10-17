<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Services\PdfIntegracionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SolicitudPdfController extends Controller
{
    /**
     * Descarga del PDF de la solicitud para el circuito de firma de aprobaciones.
     * Cada entidad descarga este documento, lo firma digitalmente en el organismo
     * que corresponda y sube la versión firmada al aprobar.
     */
    public function ver(Request $request, Solicitud $solicitud): StreamedResponse
    {
        Gate::authorize('view', $solicitud);

        $this->cargarRelaciones($solicitud);

        // El número de resolución puede llegar por query string mientras la entidad
        // todavía no confirmó la aprobación (el número se persiste al confirmar).
        $numeroResolucion = $this->normalizarNumero($request->query('numero'));

        return $this->descargar(
            $this->pdfDelSistema($solicitud, $numeroResolucion),
            $this->nombreArchivo($solicitud),
        );
    }

    /**
     * Documento listo para firmar en la etapa de Secretaría General: toma el PDF ya
     * firmado por la etapa anterior y le integra el número de resolución, de modo que
     * Secretaría firme un único archivo con la firma y el número. Si no hay un PDF
     * previo importable, cae al PDF generado por el sistema (que ya incluye el número).
     */
    public function integrado(Request $request, Solicitud $solicitud): StreamedResponse
    {
        Gate::authorize('view', $solicitud);

        $this->cargarRelaciones($solicitud);

        // Si la pantalla no envió el número por query, se usa el ya persistido.
        $numeroResolucion = $this->normalizarNumero($request->query('numero'))
            ?? $solicitud->resolucion?->numero;

        $fechaResolucion = $solicitud->resolucion?->fecha_emision?->format('d/m/Y');

        $contenido = $this->documentoParaFirmar($solicitud, $numeroResolucion, $fechaResolucion);

        return $this->descargar(
            $contenido,
            $this->nombreArchivo($solicitud, '_para_firmar'),
        );
    }

    /**
     * Devuelve el contenido del documento que se firmará: el PDF de la etapa anterior
     * con el número integrado, o el PDF del sistema como respaldo.
     */
    private function documentoParaFirmar(Solicitud $solicitud, ?string $numeroResolucion, ?string $fechaResolucion): string
    {
        $documentoAnterior = $solicitud->documentoFirmadoVigente();

        if ($documentoAnterior === null
            || blank($numeroResolucion)
            || ! Storage::disk('local')->exists($documentoAnterior->ruta)) {
            return $this->pdfDelSistema($solicitud, $numeroResolucion);
        }

        try {
            if (Storage::disk('local')->mimeType($documentoAnterior->ruta) !== 'application/pdf') {
                return $this->pdfDelSistema($solicitud, $numeroResolucion);
            }

            return app(PdfIntegracionService::class)->estamparNumeroResolucion(
                Storage::disk('local')->get($documentoAnterior->ruta),
                $numeroResolucion,
                $fechaResolucion,
            );
        } catch (\Throwable $e) {
            // El PDF firmado no se pudo importar (por ejemplo, está encriptado):
            // se informa y se usa el PDF generado por el sistema como respaldo.
            report($e);

            return $this->pdfDelSistema($solicitud, $numeroResolucion);
        }
    }

    private function pdfDelSistema(Solicitud $solicitud, ?string $numeroResolucion): string
    {
        return Pdf::loadView('pdf.solicitud', [
            'solicitud' => $solicitud,
            'numeroResolucion' => $numeroResolucion,
        ])->output();
    }

    private function descargar(string $contenido, string $nombreArchivo): StreamedResponse
    {
        return response()->streamDownload(
            fn () => print $contenido,
            $nombreArchivo,
        );
    }

    private function nombreArchivo(Solicitud $solicitud, string $sufijo = ''): string
    {
        return 'solicitud_' . str_pad((string) $solicitud->id, 6, '0', STR_PAD_LEFT) . $sufijo . '.pdf';
    }

    private function normalizarNumero(mixed $numero): ?string
    {
        $numero = is_string($numero) ? trim($numero) : null;

        if ($numero === '' || $numero === null) {
            return null;
        }

        return mb_substr($numero, 0, 255);
    }

    private function cargarRelaciones(Solicitud $solicitud): void
    {
        $solicitud->loadMissing([
            'agente.area',
            'tipoSolicitud',
            'aprobaciones.aprobador',
            'documentos',
            'resolucion',
        ]);
    }
}
