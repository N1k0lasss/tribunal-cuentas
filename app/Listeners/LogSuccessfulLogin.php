<?php

namespace App\Listeners;

use App\Models\LoginHistory;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Carbon;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Use America/Argentina/Buenos_Aires timezone to check working hours
        $now = Carbon::now();
        $localTime = $now->timezone('America/Argentina/Buenos_Aires');

        $outsideWorkingHours = false;

        if ($localTime->isWeekend()) {
            $outsideWorkingHours = true;
        } else {
            $time = $localTime->format('H:i');
            if ($time < '07:00' || $time > '13:10') {
                $outsideWorkingHours = true;
            }
        }

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'login_at' => $now,
            'outside_working_hours' => $outsideWorkingHours,
        ]);
    }
}
