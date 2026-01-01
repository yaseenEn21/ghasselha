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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'pusher' => [
        'app_id' => env('PUSHER_APP_ID'),
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'cluster' => env('PUSHER_APP_CLUSTER', 'ap1'),
        'use_tls' => env('PUSHER_USE_TLS', true),
        'channel' => env('PUSHER_NOTIFICATION_CHANNEL', 'dashboard.notifications'),
        'event' => env('PUSHER_NOTIFICATION_EVENT', 'product.created'),
    ],

    'smsservice' => [
        'user_name' => env('SMS_USER_NAME', ''),
        'user_pass' => env('SMS_USER_PASS', ''),
        'api_token' => env('SMS_API_TOKEN', ''),
    ],

    'moyasar' => [
        'secret' => env('MOYASAR_SECRET_KEY'),
        'webhook_secret' => env('MOYASAR_WEBHOOK_SECRET'),
        'currency' => env('MOYASAR_CURRENCY', 'SAR'),
        'success_url' => env('MOYASAR_SUCCESS_URL'),
        'back_url' => env('MOYASAR_BACK_URL'),
        'callback_url' => env('MOYASAR_CALLBACK_URL'),
    ],


];
