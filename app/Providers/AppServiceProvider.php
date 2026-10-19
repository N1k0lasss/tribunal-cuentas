<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use App\Hashing\Sha256Hasher;
use App\Http\Middleware\SessionTimeout;

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
            return new Sha256Hasher();
        });

        $this->configureDefaults();

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \App\Listeners\LogSuccessfulLogin::class
        );

        \App\Models\Solicitud::observe(\App\Observers\SolicitudObserver::class);

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

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            activity('auth')
                ->causedBy($event->user)
                ->log('Usuario inició sesión');
        });
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
