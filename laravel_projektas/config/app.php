<?php

return [

    'name' => env('APP_NAME', 'Kubiliuks'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => env('APP_TIMEZONE', 'Europe/Vilnius'),

    'locale' => env('APP_LOCALE', 'lt'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'lt'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'lt_LT'),

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];