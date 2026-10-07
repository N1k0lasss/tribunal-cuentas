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

    'legajos_api' => [
        'base_url' => env('LEGAJOS_API_URL', 'https://10.0.7.6/api'),
        'token' => env('LEGAJOS_API_TOKEN'),
        'verify_ssl' => filter_var(env('LEGAJOS_API_VERIFY_SSL', false), FILTER_VALIDATE_BOOLEAN),
        'timeout' => (int) env('LEGAJOS_API_TIMEOUT', 15),
        'retries' => (int) env('LEGAJOS_API_RETRIES', 2),
    ],

];
