<?php

declare(strict_types=1);

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Throwable;

/**
 * Integra el número de resolución dentro de un PDF ya firmado (sin regenerarlo),
 * estampando una franja en la parte superior de la primera página.
 *
 * Se usa en la etapa de Secretaría General para que el documento que se firma
 * contenga, en un único archivo, la firma de la etapa anterior y el número.
 */
class PdfIntegracionService
{
    /** Alto de la franja superior, en milímetros. */
    private const ALTO_FRANJA = 12.0;

    /**
     * Devuelve el contenido binario del PDF de origen con el número de resolución
     * estampado en la parte superior de la primera página.
     *
     * @param  string  $contenidoPdf  Bytes del PDF firmado que se quiere completar.
     * @param  string  $numero  Número de resolución a estampar.
     * @param  string|null  $fecha  Fecha de emisión ya formateada (d/m/Y), opcional.
     *
     * @throws Throwable Si el PDF de origen no puede importarse.
     */
    public function estamparNumeroResolucion(string $contenidoPdf, string $numero, ?string $fecha = null): string
    {
        $rutaOrigen = tempnam(sys_get_temp_dir(), 'pdf_origen_');

        if ($rutaOrigen === false) {
            throw new \RuntimeException('No se pudo crear el archivo temporal para integrar el PDF.');
        }

        file_put_contents($rutaOrigen, $contenidoPdf);

        try {
            $pdf = new Fpdi;
            $totalPaginas = $pdf->setSourceFile($rutaOrigen);

            $etiqueta = 'Resolución N° '.$numero;
            if (filled($fecha)) {
                $etiqueta .= '   |   '.$fecha;
            }

            for ($numeroPagina = 1; $numeroPagina <= $totalPaginas; $numeroPagina++) {
                $plantilla = $pdf->importPage($numeroPagina);
                $medidas = $pdf->getTemplateSize($plantilla);
                $orientacion = $medidas['width'] > $medidas['height'] ? 'L' : 'P';

                if ($numeroPagina === 1) {
                    // La primera página crece en alto para alojar la franja y el
                    // contenido original se desplaza hacia abajo, sin taparlo.
                    $pdf->AddPage($orientacion, [$medidas['width'], $medidas['height'] + self::ALTO_FRANJA]);

                    $pdf->SetFillColor(240, 253, 244);
                    $pdf->Rect(0, 0, $medidas['width'], self::ALTO_FRANJA, 'F');
                    $pdf->SetDrawColor(22, 163, 74);
                    $pdf->SetLineWidth(0.8);
                    $pdf->Line(0, self::ALTO_FRANJA, $medidas['width'], self::ALTO_FRANJA);

                    $pdf->SetTextColor(6, 95, 70);
                    $pdf->SetFont('Helvetica', 'B', 12);
                    $pdf->SetXY(6, 3.4);
                    $pdf->Cell(0, 5.2, mb_convert_encoding($etiqueta, 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');

                    $pdf->useTemplate($plantilla, 0, self::ALTO_FRANJA, $medidas['width'], $medidas['height']);
                } else {
                    $pdf->AddPage($orientacion, [$medidas['width'], $medidas['height']]);
                    $pdf->useTemplate($plantilla, 0, 0, $medidas['width'], $medidas['height']);
                }
            }

            return $pdf->Output('S');
        } finally {
            @unlink($rutaOrigen);
        }
    }
}
