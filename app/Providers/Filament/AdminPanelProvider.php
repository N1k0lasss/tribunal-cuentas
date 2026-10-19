<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Support\Colors\Color;
use Filament\Widgets\Widget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Support\Facades\FilamentColor;
use Filament\View\PanelsRenderHook;
//use Filament\Widgets\AccountWidget;
//use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path(env('FILAMENT_PATH', 'sistema'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->profile()
            ->passwordReset()
            // ->emailVerification() // Desactivado temporalmente
            ->brandName('TCSE Licencias')
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->brandLogoHeight('6rem')
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->colors([
                'primary' => Color::Hex('#1A365D'), // Navy
                'secondary' => Color::Hex('#6B1C5E'), // Bordeaux
                'danger' => Color::Hex('#6B1C5E'), // Bordeaux para alertas
                'info' => Color::Hex('#2B4C7E'), // Navy más claro
                'success' => Color::Teal,
                'warning' => Color::Amber,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.admin.styles'),
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn () => view('filament.auth.login-left-panel'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn () => view('filament.auth.login-logo'),
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn () => view('filament.admin.footer'),
            )
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,

            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Admin\Widgets\AgenteInfoWidget::class,
                \App\Filament\Admin\Widgets\MisSaldosStatsWidget::class,
                \App\Filament\Admin\Widgets\LicenseStatsWidget::class,
                \App\Filament\Admin\Widgets\SolicitudesPendientesWidget::class,
                \App\Filament\Admin\Widgets\UsoLicenciasChartWidget::class,
                \App\Filament\Admin\Widgets\ProximaFechaWidget::class,
                //AccountWidget::class,
                //FilamentInfoWidget::class,
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
                // Necesario acá además del grupo web de bootstrap/app.php: las
                // rutas del panel usan esta lista propia de Filament, no el
                // grupo web (que sí cubre /livewire/update).
                \App\Http\Middleware\SessionTimeout::class,
                \Illuminate\Routing\Middleware\ThrottleRequests::class . ':20,1',
            ])
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
                \App\Http\Middleware\BlockOutsideWorkingHours::class,
            ])
            ->plugins([
                FilamentFullCalendarPlugin::make(),
            ]);
    }
}
