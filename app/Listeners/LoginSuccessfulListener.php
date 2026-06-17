<?php

namespace App\Listeners;

use App\Listeners\LoginAttemptListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiter;

class LoginSuccessfulListener
{
    public function handle(Login $event): void
    {
        app(RateLimiter::class)->clear(
            LoginAttemptListener::throttleKey($event->user->email),
        );
    }
}
