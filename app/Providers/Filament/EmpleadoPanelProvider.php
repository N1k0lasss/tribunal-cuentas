<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class EmpleadoPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel

            ->id('empleado')
            ->default()
            ->path('')
            ->login()
            ->brandName('Tribunal de Cuentas') // Cambia el texto "tribunal-cuentas"
            ->brandLogo(asset('images/tcse_logo.png')) // Agrega tu logo
            ->brandLogoHeight('15rem') // Ajusta el tamaño del logo
            ->colors([
                // Puedes usar un color predeterminado o el HEX exacto de tu diseño anterior
                'primary' => Color::hex('#1e3a8a'),
            ])


            ->discoverResources(in: app_path('Filament/Empleado/Resources'), for: 'App\Filament\Empleado\Resources')
            ->discoverPages(in: app_path('Filament/Empleado/Pages'), for: 'App\Filament\Empleado\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Empleado/Widgets'), for: 'App\Filament\Empleado\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
