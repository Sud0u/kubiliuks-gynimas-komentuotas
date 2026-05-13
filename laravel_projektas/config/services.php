<?php

return [

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

    'recaptcha' => [
        'enabled' => env('RECAPTCHA_ENABLED', false),
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'min_score' => env('RECAPTCHA_MIN_SCORE', 0.5),
    ],

    'paysera' => [
        'enabled' => env('PAYSERA_ENABLED', false),
        'project_id' => env('PAYSERA_PROJECT_ID'),
        'sign_password' => env('PAYSERA_SIGN_PASSWORD'),
        'test' => (int) env('PAYSERA_TEST', 1),
        'currency' => env('PAYSERA_CURRENCY', 'EUR'),
        'country' => env('PAYSERA_COUNTRY', 'LT'),
    ],

];