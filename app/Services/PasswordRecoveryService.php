<?php

namespace App\Services;

use App\Contracts\SmsSender;
use App\Mail\PasswordRecoveryCodeMail;
use App\Models\PasswordRecoveryCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;
use Throwable;

class PasswordRecoveryService
{
    private const CODE_TTL_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly SmsSender $smsSender) {}

    public function request(string $channel, string $identifier, ?string $ip): ?int
    {
        if ($channel === 'sms' && ! config('auth.password_recovery.sms_enabled', false)) {
            throw new RuntimeException('La recuperación por SMS está deshabilitada.');
        }

        if (! in_array($channel, ['email', 'sms'], true)) {
            throw new RuntimeException('Canal de recuperación inválido.');
        }

        $identifier = $this->normalizeIdentifier($channel, $identifier);
        $ipKey = $this->rateLimitKey('request-ip', $ip ?? 'unknown');
        $identifierKey = $this->rateLimitKey('request-identifier', $channel.':'.$identifier);

        if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($identifierKey, 5)) {
            $this->audit('Solicitud de recuperación limitada', $channel, $identifier);

            return null;
        }

        RateLimiter::hit($ipKey, 60);
        RateLimiter::hit($identifierKey, 60);

        $user = $channel === 'email'
            ? User::query()
                ->whereRaw('LOWER(email) = ?', [$identifier])
                ->whereNotNull('email_verified_at')
                ->first()
            : User::query()
                ->where('telefono', $identifier)
                ->whereNotNull('telefono_verified_at')
                ->first();

        $this->audit('Solicitud de recuperación', $channel, $identifier, $user);

        if (! $user) {
            return null;
        }

        $code = (string) random_int(100000, 999999);
        $recovery = PasswordRecoveryCode::query()->create([
            'user_id' => $user->id,
            'channel' => $channel,
            'identifier' => $identifier,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
        ]);

        try {
            if ($channel === 'email') {
                Mail::to($user->email)->send(new PasswordRecoveryCodeMail($code));
            } elseif ($channel === 'sms') {
                $this->smsSender->send($user->telefono, "Su código de recuperación es {$code}. Vence en 10 minutos.");
            }
        } catch (Throwable $exception) {
            $recovery->delete();
            report($exception);
            $this->audit('Error al enviar código de recuperación', $channel, $identifier, $user);

            return null;
        }

        return $recovery->id;
    }

    public function verify(?int $recoveryId, string $code, ?string $ip): bool
    {
        if ($recoveryId === null || ! preg_match('/^\d{6}$/', $code)) {
            $this->audit('Verificación fallida de recuperación', 'unknown', 'unknown');

            return false;
        }

        $recovery = PasswordRecoveryCode::query()->with('user')->find($recoveryId);

        if (! $recovery) {
            $this->audit('Verificación fallida de recuperación', 'unknown', 'unknown');

            return false;
        }

        $ipKey = $this->rateLimitKey('verify-ip', $ip ?? 'unknown');
        $identifierKey = $this->rateLimitKey('verify-identifier', $recovery->channel.':'.$recovery->identifier);

        if (RateLimiter::tooManyAttempts($ipKey, self::MAX_ATTEMPTS) ||
            RateLimiter::tooManyAttempts($identifierKey, self::MAX_ATTEMPTS)) {
            $this->audit('Verificación bloqueada por límite de intentos', $recovery->channel, $recovery->identifier, $recovery->user);

            return false;
        }

        RateLimiter::hit($ipKey, 600);
        RateLimiter::hit($identifierKey, 600);

        $verified = DB::transaction(function () use ($recoveryId, $code): bool {
            $locked = PasswordRecoveryCode::query()->lockForUpdate()->find($recoveryId);

            if (! $locked || $locked->used_at || $locked->verified_at || $locked->expires_at->isPast() || $locked->attempts >= self::MAX_ATTEMPTS) {
                return false;
            }

            if (! Hash::check($code, $locked->code_hash)) {
                $locked->increment('attempts');

                return false;
            }

            $locked->forceFill(['verified_at' => now()])->save();

            return true;
        });

        $this->audit(
            $verified ? 'Código de recuperación verificado' : 'Verificación fallida de recuperación',
            $recovery->channel,
            $recovery->identifier,
            $recovery->user,
        );

        return $verified;
    }

    public function resetPassword(int $recoveryId, string $password): bool
    {
        Validator::make(['password' => $password], [
            'password' => [
                'required',
                'string',
                Password::min(10)->mixedCase()->numbers()->symbols(),
            ],
        ])->validate();

        return DB::transaction(function () use ($recoveryId, $password): bool {
            $recovery = PasswordRecoveryCode::query()->with('user')->lockForUpdate()->find($recoveryId);

            if (! $recovery || ! $recovery->verified_at || $recovery->used_at || $recovery->verified_at->addMinutes(self::CODE_TTL_MINUTES)->isPast()) {
                return false;
            }

            $user = User::query()->lockForUpdate()->find($recovery->user_id);
            if (! $user) {
                return false;
            }

            $user->password = $password;
            $user->remember_token = null;
            $user->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $recovery->forceFill(['used_at' => now()])->save();

            $this->audit('Cambio de contraseña exitoso', $recovery->channel, $recovery->identifier, $user);

            return true;
        });
    }

    public function normalizeIdentifier(string $channel, string $identifier): string
    {
        $identifier = trim($identifier);

        if ($channel === 'email') {
            return mb_strtolower($identifier);
        }

        if (! preg_match('/^\+[1-9]\d{1,14}$/', $identifier)) {
            throw new RuntimeException('El teléfono debe estar en formato E.164.');
        }

        return $identifier;
    }

    private function rateLimitKey(string $action, string $value): string
    {
        return 'password-recovery:'.$action.':'.hash('sha256', $value);
    }

    private function audit(string $description, string $channel, string $identifier, ?User $user = null): void
    {
        activity('auth')
            ->causedBy($user)
            ->withProperties([
                'channel' => $channel,
                'identifier_hash' => hash('sha256', $identifier),
            ])
            ->log($description);
    }
}
