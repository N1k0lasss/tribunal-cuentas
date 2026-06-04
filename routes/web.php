<?php

declare(strict_types=1);

use App\Models\Documento;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// EL TELETRANSPORTADOR: Empuja al usuario al login del admin
Route::redirect('/', '/admin');

// NUEVA RUTA: Pantalla de Recibos para el agente (¡Lo dejamos intacto!)
Route::get('/recibos', function () {
    return "Esta será la pantalla de Recibos de Sueldo del Agente";
})->name('recibos.index');

// Ruta segura para ver documentos adjuntos (requiere autenticación)
Route::get('/documentos/{documento}/ver', function (Documento $documento) {
    $path = $documento->ruta;

    if (! Storage::disk('local')->exists($path)) {
        abort(404, 'Documento no encontrado.');
    }

    $mime = Storage::disk('local')->mimeType($path);
    $contenido = Storage::disk('local')->get($path);
    $nombre = $documento->nombre_archivo ?? basename($path);

    return response($contenido, 200)
        ->header('Content-Type', $mime)
        ->header('Content-Disposition', 'inline; filename="' . $nombre . '"');
})->middleware('auth')->name('documento.ver');

require __DIR__ . '/settings.php';

Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');
