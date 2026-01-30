<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Turnstile (njoguamos/laravel-turnstile)
    |--------------------------------------------------------------------------
    |
    | When ->turnstile() is enabled, Filament Jetstream injects the Turnstile
    | widget into auth forms and validates using njoguamos/laravel-turnstile.
    | Site key, secret key, and enabled state come from that package's config
    | (config/turnstile.php). Run `php artisan turnstile:install` in your
    | app and set TURNSTILE_SITE_KEY, TURNSTILE_SECRET_KEY, TURNSTILE_ENABLED.
    |
    */

    'turnstile' => [
        'reset_event' => env('TURNSTILE_RESET_EVENT', 'filament-team-guard-turnstile-reset'),
    ],
];
