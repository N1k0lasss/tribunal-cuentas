<?php

namespace App\Filament\Admin\Pages;

use App\Models\LegajoApiCredential;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class LegajoApiSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'API de Legajos';

    protected static ?string $title = 'Credencial de la API de Legajos';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $slug = 'api-legajos';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.admin.pages.legajo-api-settings';

    public string $token = '';

    public static function canAccess(): bool
    {
        $usuario = Auth::user();

        return $usuario instanceof User && $usuario->hasRole('administrador');
    }

    public function guardar(): void
    {
        abort_unless(static::canAccess(), 403);

        $validated = $this->validate([
            'token' => ['required', 'string', 'max:4096'],
        ], [
            'token.required' => 'Ingresá el token que te proporcionó Legajos.',
            'token.max' => 'El token no puede superar los 4096 caracteres.',
        ]);

        LegajoApiCredential::query()->updateOrCreate(
            ['id' => LegajoApiCredential::SINGLETON_ID],
            [
                'token' => $validated['token'],
                'updated_by' => Auth::id(),
            ],
        );

        $this->reset('token');

        Notification::make()
            ->title('Token de Legajos actualizado')
            ->success()
            ->send();
    }

    public function getCredentialStatus(): array
    {
        $credential = LegajoApiCredential::query()->find(LegajoApiCredential::SINGLETON_ID);

        if ($credential !== null) {
            $updatedAt = $credential->updated_at;

            return [
                'configured' => true,
                'source' => 'panel',
                'updated_at' => $updatedAt,
                'expires_at' => $updatedAt?->copy()->addHours(24),
                'expired' => $updatedAt !== null && now()->greaterThanOrEqualTo($updatedAt->copy()->addHours(24)),
            ];
        }

        return [
            'configured' => filled(config('services.legajos.token')),
            'source' => 'env',
            'updated_at' => null,
            'expires_at' => null,
            'expired' => false,
        ];
    }
}
