<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | API del Sistema de Legajos (fuente de verdad de los datos del agente).
    | Ver integracion-legajos-api.md.
    */
    'legajos' => [
        'url' => env('LEGAJOS_API_URL'),
        // Token legacy: la credencial administrable del panel tiene prioridad.
        'token' => env('LEGAJOS_API_TOKEN'),
        // false: el servidor de Legajos usa certificado autofirmado.
        'verify' => env('LEGAJOS_API_VERIFY_SSL', true),
        'timeout' => env('LEGAJOS_API_TIMEOUT', 10),
        // Acota el caso "host caído" (no llega a establecer conexión).
        'connect_timeout' => env('LEGAJOS_API_CONNECT_TIMEOUT', 3),
    ],

];
