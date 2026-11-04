<?php

namespace App\Filament\Auth\Pages;

use App\Services\PasswordRecoveryService;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Validation\Rules\Password;

class PasswordRecovery extends SimplePage
{
    protected string $view = 'filament.auth.password-recovery';

    public string $step = 'request';

    public string $identifier = '';

    public string $code = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?int $recoveryId = null;

    public function requestCode(PasswordRecoveryService $service): void
    {
        $this->validate([
            'identifier' => ['required', 'email', 'max:255'],
        ]);

        $this->identifier = $service->normalizeIdentifier('email', $this->identifier);
        $this->recoveryId = $service->request('email', $this->identifier, request()->ip());
        $this->step = 'verify';

        Notification::make()
            ->title('Si el correo es válido, recibirá un código de confirmación.')
            ->success()
            ->send();
    }

    public function verifyCode(PasswordRecoveryService $service): void
    {
        $this->validate(['code' => ['required', 'digits:6']]);

        if (! $service->verify($this->recoveryId, $this->code, request()->ip())) {
            $this->addError('code', 'El código es incorrecto, venció o alcanzó el límite de intentos.');

            return;
        }

        $this->step = 'password';
        $this->code = '';
    }

    public function resetPassword(PasswordRecoveryService $service): void
    {
        $this->validate([
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(10)->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'password.min' => 'La contraseña debe tener al menos 10 caracteres.',
            'password.mixed' => 'La contraseña debe incluir mayúsculas y minúsculas.',
            'password.numbers' => 'La contraseña debe incluir números.',
            'password.symbols' => 'La contraseña debe incluir símbolos.',
        ]);

        if (! $this->recoveryId || ! $service->resetPassword($this->recoveryId, $this->password)) {
            $this->addError('password', 'La sesión de recuperación venció. Solicite un nuevo código.');

            return;
        }

        Notification::make()
            ->title('Contraseña actualizada correctamente.')
            ->success()
            ->send();

        $this->redirect(filament()->getLoginUrl());
    }

    public function loginUrl(): string
    {
        return filament()->getLoginUrl();
    }

    public function getTitle(): string
    {
        return 'Recuperar contraseña';
    }

    public function getHeading(): string
    {
        return match ($this->step) {
            'verify' => 'Ingrese el código de confirmación',
            'password' => 'Defina una nueva contraseña',
            default => 'Recuperar contraseña',
        };
    }

    public function getSubheading(): string
    {
        return match ($this->step) {
            'verify' => 'Revise su correo e ingrese el código de 6 dígitos.',
            'password' => 'Use al menos 10 caracteres, mayúsculas, minúsculas, números y símbolos.',
            default => 'Ingrese su correo para recibir un código de confirmación.',
        };
    }
}
