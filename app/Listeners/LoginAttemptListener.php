<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Cache\RateLimiter;

class LoginAttemptListener
{
    public const int MAX_ATTEMPTS = 4;

    public const int LOCKOUT_DURATION = 15; // minutes

    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;

        if (! $email) {
            return;
        }

        app(RateLimiter::class)->hit(
            $this->throttleKey($email),
            static::LOCKOUT_DURATION * 60,
        );
    }

    public static function throttleKey(string $email): string
    {
        return 'auth:login:' . strtolower($email);
    }
}
