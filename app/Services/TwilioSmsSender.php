<?php

namespace App\Services;

use App\Contracts\SmsSender;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TwilioSmsSender implements SmsSender
{
    public function send(string $phone, string $message): void
    {
        $sid = (string) config('services.twilio.sid');
        $token = (string) config('services.twilio.token');
        $from = (string) config('services.twilio.from');

        if ($sid === '' || $token === '' || $from === '') {
            throw new RuntimeException('La configuración de Twilio está incompleta.');
        }

        $response = Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $phone,
                'Body' => $message,
            ]);

        $response->throw();
    }
}
