<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BlockOutsideWorkingHours
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // 1. If not authenticated, let general auth middleware handle it.
        if (!$user) {
            return $next($request);
        }

        // Bypass working hours restriction in unit tests unless a test time is explicitly set.
        if (app()->runningUnitTests() && !Carbon::hasTestNow()) {
            return $next($request);
        }

        // 2. Check if the user has an exempt role.
        if ($user->hasAnyRole(['administrador', 'presidencia', 'personal'])) {
            return $next($request);
        }

        // 3. Check if the current time is outside working hours.
        // Official hours: Monday to Friday from 07:00 to 13:10 hs (America/Argentina/Buenos_Aires).
        $now = Carbon::now();
        $localTime = $now->timezone('America/Argentina/Buenos_Aires');

        if ($this->isOutsideWorkingHours($localTime)) {
            // 4. Intercept only critical routes and Livewire components.
            if ($this->isCriticalRequest($request)) {
                abort(403, 'Acceso denegado: Operación no permitida fuera del horario laboral establecido (Lunes a Viernes de 07:00 a 13:10 hs).');
            }
        }

        return $next($request);
    }

    /**
     * Determine if the date/time is outside working hours.
     */
    protected function isOutsideWorkingHours(Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return true;
        }

        $time = $date->format('H:i');
        return $time < '07:00' || $time > '13:10';
    }

    /**
     * Check if the request is targeting critical routes or components.
     */
    protected function isCriticalRequest(Request $request): bool
    {
        $criticalPaths = [
            'admin/solicituds',
            'admin/aprobacions',
            'admin/saldos',
            'admin/documentos',
        ];

        $path = ltrim($request->path(), '/');

        // Check if the primary path is critical.
        foreach ($criticalPaths as $criticalPath) {
            if (str_starts_with($path, $criticalPath)) {
                return true;
            }
        }

        // Check if it's a Livewire request.
        if (str_contains($path, 'livewire') && str_ends_with($path, 'update')) {
            // 1. Check Referer header (checks the page the request originated from).
            $referer = $request->header('Referer');
            if ($referer) {
                $refererPath = parse_url($referer, PHP_URL_PATH);
                if ($refererPath) {
                    $refererPath = ltrim($refererPath, '/');
                    foreach ($criticalPaths as $criticalPath) {
                        if (str_starts_with($refererPath, $criticalPath)) {
                            return true;
                        }
                    }
                }
            }

            // 2. Check JSON payload components.
            $components = $request->input('components');
            if (is_array($components)) {
                foreach ($components as $component) {
                    $snapshot = $component['snapshot'] ?? null;
                    $class = null;
                    if (is_string($snapshot)) {
                        $decoded = json_decode($snapshot, true);
                        $class = $decoded['memo']['name'] ?? $decoded['class'] ?? null;
                    } elseif (is_array($snapshot)) {
                        $class = $snapshot['memo']['name'] ?? $snapshot['class'] ?? null;
                    }

                    if ($class) {
                        if (str_contains($class, 'Solicitud') || 
                            str_contains($class, 'Aprobacion') || 
                            str_contains($class, 'Saldo') || 
                            str_contains($class, 'Documento') || 
                            str_contains($class, 'SolicitudesPendientesWidget')) {
                            return true;
                        }
                    }
                }
            }

            // 3. Body text search fallback for maximum resilience.
            $content = $request->getContent();
            if ($content && (
                str_contains($content, 'Solicitud') || 
                str_contains($content, 'Aprobacion') || 
                str_contains($content, 'Saldo') || 
                str_contains($content, 'Documento') || 
                str_contains($content, 'SolicitudesPendientesWidget')
            )) {
                return true;
            }
        }

        return false;
    }
}
