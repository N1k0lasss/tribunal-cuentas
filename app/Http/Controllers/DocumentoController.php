<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentoController extends Controller
{
    public function ver(Documento $documento): BinaryFileResponse
    {
        Gate::authorize('view', $documento);

        if (! $documento->rutaValida()) {
            abort(404, 'Documento no encontrado.');
        }

        $disk = Storage::disk('local');
        $path = $documento->ruta;

        if (! $disk->exists($path)) {
            abort(404, 'Documento no encontrado.');
        }

        $root = realpath($disk->path(''));
        $fullPath = realpath($disk->path($path));

        if ($root === false
            || $fullPath === false
            || ! is_file($fullPath)
            || ! str_starts_with($fullPath, $root.DIRECTORY_SEPARATOR)) {
            abort(404, 'Documento no encontrado.');
        }

        $mime = $disk->mimeType($path);

        if (! in_array($mime, [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true)) {
            abort(404, 'Documento no encontrado.');
        }

        $nombre = $documento->nombre_archivo ?: basename($path);
        $nombre = preg_replace('/[^\pL\pN\s._-]+/u', '_', $nombre) ?: 'documento';
        $nombre = str_replace(["\r", "\n", '"'], '', $nombre);

        $response = new BinaryFileResponse($fullPath, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$nombre.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Pragma' => 'no-cache',
        ], false);

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');
        $response->setMaxAge(0);

        return $response;
    }
}
