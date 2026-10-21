<?php

declare(strict_types=1);

use App\Http\Controllers\SessionActivityController;
use App\Http\Controllers\SessionStatusController;
use App\Http\Controllers\SolicitudPdfController;
use App\Models\Documento;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

$filamentPath = env('FILAMENT_PATH', 'sistema');

// EL TELETRANSPORTADOR: Empuja al usuario al login del admin (ruta oculta)
Route::redirect('/', '/' . $filamentPath);

// Ocultar ruta admin redireccionando a inicio (si el path no es admin)
if ($filamentPath !== 'admin') {
    Route::redirect('/admin', '/');
}

// NUEVA RUTA: Pantalla de Recibos para el agente (¡Lo dejamos intacto!)
Route::get('/recibos', function () {
    return "Esta será la pantalla de Recibos de Sueldo del Agente";
})->name('recibos.index');

// Ruta segura para ver documentos adjuntos (requiere autenticación)
Route::get('/documentos/{documento}/ver', function (Documento $documento) {
    Gate::authorize('view', $documento);

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

// Descarga del PDF de la solicitud para el circuito de firma de aprobaciones.
// Cada entidad descarga este documento, lo firma digitalmente en el organismo
// que corresponda y sube la versión firmada al aprobar.
Route::get('/solicitudes/{solicitud}/pdf', [SolicitudPdfController::class, 'ver'])
    ->middleware('auth')
    ->name('solicitud.pdf');

// Documento listo para firmar en la etapa de Secretaría General: toma el PDF ya
// firmado por la etapa anterior y le integra el número de resolución, de modo que
// Secretaría firme un único archivo con la firma y el número. Si no hay un PDF
// previo importable, cae al PDF generado por el sistema (que ya incluye el número).
Route::get('/solicitudes/{solicitud}/pdf-integrado', [SolicitudPdfController::class, 'integrado'])
    ->middleware('auth')
    ->name('solicitud.pdf.integrado');

// Marcador de "actividad real" del usuario (mouse / teclado). Lo dispara el JS
// del panel y lo usa el middleware SessionTimeout para el cierre por
// inactividad: así el polling de Livewire no cuenta como actividad.
Route::post('/session/actividad', SessionActivityController::class)
    ->name('session.actividad');

// Estado real de la sesión (¿sigue viva?). Lo consulta el JS del panel cuando una
// petición falla con 419/401, para no mostrar el aviso de "sesión terminada" por
// un token vencido si la sesión en realidad sigue activa. Sin middleware `auth`
// a propósito: si la sesión murió debe responder 200 con authenticated = false.
Route::get('/session/estado', SessionStatusController::class)
    ->name('session.estado');

require __DIR__ . '/settings.php';

Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');
