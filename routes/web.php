<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// EL TELETRANSPORTADOR: Empuja al usuario al login del admin
Route::redirect('/', '/admin');

// NUEVA RUTA: Pantalla de Recibos para el agente (¡Lo dejamos intacto!)
Route::get('/recibos', function () {
    return "Esta será la pantalla de Recibos de Sueldo del Agente";
})->name('recibos.index');

require __DIR__ . '/settings.php';

Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');
