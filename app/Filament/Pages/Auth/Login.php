<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    // Indicamos que use la vista que creamos recién
     protected static string $view = 'filament.pages.Auth.login';
}
