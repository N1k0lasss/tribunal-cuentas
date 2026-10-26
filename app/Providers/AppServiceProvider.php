<?php

namespace App\Providers;

use App\Hashing\Sha256Hasher;
use App\Http\Middleware\SessionTimeout;
use App\Listeners\LogSuccessfulLogin;
use App\Models\Solicitud;
use App\Observers\SolicitudObserver;
use Carbon\CarbonImmutable;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Hash::extend('sha256', function () {
            return new Sha256Hasher;
        });

        $this->configureDefaults();

        self::ensureSessionOutlivesInactivity();

        Event::listen(
            Login::class,
            LogSuccessfulLogin::class
        );

        Solicitud::observe(SolicitudObserver::class);

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            // El script de inactividad solo aplica con sesión iniciada: en el
            // login (layout simple) no debe inyectarse.
            fn (): string => auth()->check()
                ? View::make('components.session-timeout-script')->render()
                : '',
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
            function (): string {
                // Muestra el aviso de "sesión expirada por inactividad" en el login.
                // Se toma del flash o, si la página de login ya consumió el flash
                // con alguna petición Livewire, del parámetro de la URL.
                $mensaje = session('mensaje');

                if (! $mensaje && request()->boolean('sesion_expirada')) {
                    $mensaje = SessionTimeout::MENSAJE;
                }

                return $mensaje
                    ? View::make('filament.auth.session-expired-message', ['mensaje' => $mensaje])->render()
                    : '';
            },
        );

        Event::listen(Login::class, function ($event) {
            activity('auth')
                ->causedBy($event->user)
                ->log('Usuario inició sesión');
        });
    }

    /**
     * La sesión del servidor debe durar más que la inactividad que aplica la app.
     *
     * Si no, Laravel (driver database) puede descartar la sesión antes de que
     * App\Http\Middleware\SessionTimeout alcance a mostrar el aviso, y el usuario
     * terminaría en el login sin ninguna explicación.
     *
     * La invariante la define config/session.php (`lifetime` = `inactivity_lifetime` + 5)
     * y se verifica en SessionTimeout (única fuente de verdad).
     */
    public static function ensureSessionOutlivesInactivity(): void
    {
        if (SessionTimeout::sesionDelServidorSobreviveALaInactividad()) {
            return;
        }

        Log::warning(
            'config/session.php: "lifetime" debe ser mayor que "inactivity_lifetime", si no el aviso de sesión expirada podría no mostrarse.',
            [
                'lifetime' => (int) config('session.lifetime'),
                'inactivity_lifetime' => SessionTimeout::minutosDeInactividad(),
            ],
        );
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
