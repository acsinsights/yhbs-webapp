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

    'ottu' => [
        'merchant_id' => env('OTTU_MERCHANT_ID'),
        'api_key' => env('OTTU_API_KEY'),
        'api_url' => env('OTTU_API_URL', 'https://sandbox.ottu.net'),
        'webhook_secret' => env('OTTU_WEBHOOK_SECRET'),
        'currency' => env('OTTU_CURRENCY', 'KWD'),
        'sdk_url' => 'https://assets.ottu.net/checkout/v3/checkout.min.js',
    ],
];
