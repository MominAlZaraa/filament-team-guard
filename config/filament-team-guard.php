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

    /*
    |--------------------------------------------------------------------------
    | Team invitation security
    |--------------------------------------------------------------------------
    |
    | Invitation acceptance links are signed and can be time-limited.
    | Set the expiration window (in minutes) to reduce replay risk from leaked links.
    |
    */
    'team_invitations' => [
        'expires_in_minutes' => env('FILAMENT_TEAM_GUARD_INVITATION_EXPIRES_IN_MINUTES', 10080),
    ],
];
