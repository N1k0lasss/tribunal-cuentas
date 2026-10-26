<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Rutas que quedan fuera del cierre por inactividad.
     *
     * Son los endpoints del latido y de consulta de estado: si el middleware los
     * cortara con 419/redirect, el propio JavaScript no podría averiguar si la
     * sesión sigue viva (rompería la verificación previa al aviso).
     *
     * @var list<string>
     */
    private const RUTAS_EXENTAS = [
        'session/actividad',
        'session/estado',
    ];

    /**
     * Mensaje que se muestra en el login cuando la sesión se cerró sola.
     */
    public const MENSAJE = 'Tu sesión expiró por inactividad.';

    /**
     * Cierra la sesión cuando pasó el tiempo de inactividad configurado.
     *
     * El marcador `last_activity_time` SOLO lo actualiza el endpoint de
     * actividad real (SessionActivityController), que dispara el JavaScript del
     * panel ante eventos de mouse/teclado. De esa forma el polling de Livewire
     * (notificaciones cada ~30s) no mantiene la sesión viva artificialmente.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        if ($this->esRutaExenta($request)) {
            return $next($request);
        }

        $ultimaActividad = session('last_activity_time');

        // Primera petición después del login: inicializamos el marcador.
        if (! $ultimaActividad) {
            session(['last_activity_time' => now()]);

            return $next($request);
        }

        // Carbon 3 devuelve el diff CON SIGNO, por eso hay que pedirlo absoluto.
        $minutosInactivo = now()->diffInMinutes($ultimaActividad, true);

        if ($minutosInactivo >= self::minutosDeInactividad()) {
            return $this->cerrarSesion($request);
        }

        return $next($request);
    }

    /**
     * Minutos de inactividad antes de cerrar la sesión.
     *
     * Fuente de verdad: config/session.php → `inactivity_lifetime`
     * (variable SESSION_LIFETIME). Se usa la `lifetime` como respaldo por si el
     * cache de configuración quedó viejo (sin la clave nueva) tras un deploy.
     */
    public static function minutosDeInactividad(): int
    {
        return max(1, (int) config('session.inactivity_lifetime', config('session.lifetime', 15)));
    }

    /**
     * ¿La sesión del servidor dura más que la inactividad aplicada por la app?
     *
     * Laravel (driver database) descarta la sesión a los `session.lifetime`
     * minutos, y lo evalúa ANTES de este middleware. Si ambos valores fueran
     * iguales, la sesión podría caer primero y el usuario terminaría en el login
     * sin el aviso de inactividad.
     *
     * Se define en config/session.php: `lifetime` = `inactivity_lifetime` + 5.
     */
    public static function sesionDelServidorSobreviveALaInactividad(): bool
    {
        return (int) config('session.lifetime') > self::minutosDeInactividad();
    }

    protected function cerrarSesion(Request $request): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // El mensaje viaja en el flash para que lo muestre el login. Además lo
        // agregamos como parámetro en la URL de destino, porque la página de
        // login hace peticiones Livewire que pueden consumir el flash antes de
        // que se dibuje el aviso.
        $request->session()->flash('mensaje', self::MENSAJE);

        $url = $this->urlLoginExpirada();

        // Livewire y las peticiones AJAX (por ejemplo el latido de actividad)
        // NO pueden recibir un redirect: devolvemos 419 y el JS del panel
        // redirige al login.
        //
        // DEUDA TÉCNICA CONOCIDA: el 419 acá significa "sesión cerrada por
        // inactividad", pero Laravel también usa 419 para "token CSRF inválido"
        // (VerifyCsrfToken), que NO implica que la sesión murió. Como el JS no
        // puede distinguir ambos casos por el código, ante un 419 consulta
        // /session/estado (SessionStatusController) y recién avisa si la sesión
        // se cerró de verdad. La solución de fondo sería devolver un código
        // propio (p. ej. 401) para este caso; no se hizo para no romper el
        // contrato con Livewire.
        if ($this->esPeticionAjax($request)) {
            return response()->json(['message' => self::MENSAJE, 'redirect' => $url], 419);
        }

        return redirect($url);
    }

    protected function esPeticionAjax(Request $request): bool
    {
        return $request->hasHeader('X-Livewire')
            || $request->is('livewire*')
            || $request->expectsJson();
    }

    /**
     * ¿Es una ruta exenta del cierre por inactividad? (latido y estado).
     */
    protected function esRutaExenta(Request $request): bool
    {
        return $request->is(...self::RUTAS_EXENTAS);
    }

    protected function urlLogin(): string
    {
        if (Route::has('filament.admin.auth.login')) {
            return route('filament.admin.auth.login');
        }

        return '/'.trim((string) env('FILAMENT_PATH', 'sistema'), '/').'/login';
    }

    /**
     * URL del login con el marcador que hace visible el aviso de expiración.
     */
    protected function urlLoginExpirada(): string
    {
        $url = $this->urlLogin();

        return $url.(str_contains($url, '?') ? '&' : '?').'sesion_expirada=1';
    }
}
