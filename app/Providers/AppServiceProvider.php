<?php

namespace App\Providers;

use App\Hashing\Sha256Hasher;
use App\Listeners\LogSuccessfulLogin;
use App\Models\Solicitud;
use App\Observers\SolicitudObserver;
use App\Services\LegajoApiService;
use Carbon\CarbonImmutable;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
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
        // Singleton: la memoria corta de la API (30 s por DNI) debe compartirse
        // entre todas las entradas de una misma pantalla.
        $this->app->singleton(LegajoApiService::class);
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

        Event::listen(
            Login::class,
            LogSuccessfulLogin::class
        );

        Solicitud::observe(SolicitudObserver::class);

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => View::make('components.session-timeout-script')->render(),
        );

        Event::listen(Login::class, function ($event) {
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
