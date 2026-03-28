<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Ruta principal que redirige al dashboard
//Route::get('/', fn () => redirect()->route('filament.admin.pages.dashboard'));

// NUEVA RUTA: Carga el Portal del Empleado (welcome.blade.php)
Route::get('/', function () {
    return view('welcome');
});

// NUEVA RUTA: Pantalla de Recibos para el agente
Route::get('/recibos', function () {
    return "Esta será la pantalla de Recibos de Sueldo del Agente";
})->name('recibos.index');

require __DIR__.'/settings.php';
