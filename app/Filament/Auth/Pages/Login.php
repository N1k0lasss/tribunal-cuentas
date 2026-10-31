<?php

namespace App\Filament\Auth\Pages;

use App\Listeners\LoginAttemptListener;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable|null
    {
        return 'Sistema de Gestión de Licencias';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Tribunal de Cuentas de Santiago del Estero';
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $email = $data['email'] ?? '';

        if ($this->isAccountLocked($email)) {
            $seconds = app(RateLimiter::class)->availableIn(
                LoginAttemptListener::throttleKey($email),
            );

            throw ValidationException::withMessages([
                'data.email' => [
                    __('Cuenta bloqueada temporalmente por :attempts intentos fallidos. Intente nuevamente en :minutes minutos.', [
                        'attempts' => LoginAttemptListener::MAX_ATTEMPTS,
                        'minutes' => (int) ceil($seconds / 60),
                    ]),
                ],
            ]);
        }

        return parent::authenticate();
    }

    protected function rateLimit($maxAttempts, $decaySeconds = 60, $method = null, $component = null): void
    {
        // Suprimimos el rate limit nativo de Filament (5 intentos/min)
        // porque nosotros manejamos el bloqueo con 4 intentos / 15 min
    }

    protected function isAccountLocked(string $email): bool
    {
        if (blank($email)) {
            return false;
        }

        return app(RateLimiter::class)->tooManyAttempts(
            LoginAttemptListener::throttleKey($email),
            LoginAttemptListener::MAX_ATTEMPTS,
        );
    }
}
