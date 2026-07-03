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

    'stripe' => [
        'secret'          => env('STRIPE_SECRET_KEY'),
        'client_id'       => env('STRIPE_CLIENT_ID'),
        'webhook_secret'  => env('STRIPE_WEBHOOK_SECRET'),
        'pix_enabled'     => env('STRIPE_PIX_ENABLED', true),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token'  => env('TWILIO_AUTH_TOKEN'),
        'from'        => env('TWILIO_WHATSAPP_FROM'), // e.g. whatsapp:+14155238886
    ],

    'google' => [
        'places_key'      => env('GOOGLE_PLACES_API_KEY'),
        'ads' => [
            'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
            'client_id'       => env('GOOGLE_ADS_CLIENT_ID'),
            'client_secret'   => env('GOOGLE_ADS_CLIENT_SECRET'),
            'refresh_token'   => env('GOOGLE_ADS_REFRESH_TOKEN'),
            'customer_id'     => env('GOOGLE_ADS_CUSTOMER_ID'),
        ],
    ],

    'apify' => [
        'token' => env('APIFY_TOKEN'),
    ],

];
