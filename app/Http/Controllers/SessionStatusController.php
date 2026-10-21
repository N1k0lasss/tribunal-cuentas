<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class SessionStatusController extends Controller
{
    /**
     * Informa si la sesión del usuario sigue viva.
     *
     * Existe para que el JavaScript del panel NO muestre el aviso de "sesión
     * terminada" a partir de un 419 aislado (por ejemplo, un token CSRF viejo).
     * Ante un 419/401 el script llama a este endpoint y recién avisa si acá
     * confirma que la sesión se cerró de verdad.
     *
     * Va SIN middleware `auth`: si la sesión murió queremos una respuesta 200
     * con `authenticated = false` (no un redirect al login, que el fetch vería
     * como `redirected` y volvería a confundir al script).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $autenticado = Auth::check();

        return response()->json([
            'authenticated' => $autenticado,
            'reason' => $autenticado ? null : 'session_expired',
        ]);
    }
}
