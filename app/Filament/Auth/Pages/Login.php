<?php

namespace App\Filament\Auth\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    // El view del padre maneja todo el renderizado de Filament v5.
    // Solo sobreescribimos heading y subheading; el logo y el footer
    // se inyectan vía CSS + render hook en AdminPanelProvider.

    public function getHeading(): string|Htmlable|null
    {
        return 'Sistema de Gestión de Licencias';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Tribunal de Cuentas de Santiago del Estero';
    }
}
