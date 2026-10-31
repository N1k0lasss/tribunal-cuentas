<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Resolucion;
use App\Services\ResolucionDiariaService;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResolucionPdfController extends Controller
{
    public function ver(Resolucion $resolucion, ResolucionDiariaService $service): StreamedResponse
    {
        Gate::authorize('view', $resolucion);

        return $service->descargar($resolucion);
    }
}
