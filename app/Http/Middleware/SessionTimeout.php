<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {

            $ultimaActividad = session('last_activity_time');

            // Si existe registro de la última actividad
            if ($ultimaActividad) {
                $minutosInactivo = now()->diffInMinutes($ultimaActividad);

                if ($minutosInactivo >= 5) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect('/admin/login')->with('mensaje', 'Tu sesión expiró por inactividad.');
                }
            }

            // Actualiza la marca de tiempo en cada request
            session(['last_activity_time' => now()]);
        }

        return $next($request);
    }
}