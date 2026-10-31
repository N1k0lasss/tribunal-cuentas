<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionActivityController extends Controller
{
    /**
     * Registra "actividad real" del usuario (movimiento de mouse / teclado).
     *
     * Lo dispara el JavaScript del panel y lo consume el middleware
     * SessionTimeout para el cierre automático por inactividad. A diferencia del
     * polling de Livewire, este endpoint solo se llama cuando hubo interacción
     * humana, así el reloj de inactividad no se reinicia solo.
     */
    public function __invoke(Request $request): Response
    {
        if (auth()->check()) {
            $request->session()->put('last_activity_time', now());
        }

        return response()->noContent();
    }
}
