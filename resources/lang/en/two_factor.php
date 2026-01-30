<?php

/**
 * Filament Jetstream - Embedded Two-Factor Authentication Translations
 *
 * This file contains translations for the embedded TOTP and passkey flows.
 * The structure is adapted from stephenjude/filament-two-factor-authentication
 * but namespaced under `filament-team-guard::two_factor.*`.
 */

return [
    'plugin' => [
        'user_menu_item_label' => '2FA Settings',
    ],

    'section' => [
        'header' => 'Two Factor Authentication',
        'description' => 'Add additional security to your account using two factor authentication.',
        'passkey' => [
            'header' => 'Passkeys',
            'description' => 'Use passkeys (Face ID, fingerprint, or PIN) for passwordless login.',
        ],
        'dashboard' => 'Dashboard',
    ],

    'components' => [
        'enable' => [
            'header' => 'You have not enabled two factor authentication.',
            'description' => 'When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s authenticator application.',
        ],

        'enabled' => [
            'header' => 'You have enabled two factor authentication.',
            'description' => 'Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.',
        ],

        'setup_confirmation' => [
            'header' => 'Finish enabling two factor authentication.',
            'description' => 'When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s authenticator application.',
            'scan_qr_code' => 'To finish enabling two factor authentication, scan the following QR code using your phone\'s authenticator application or enter the setup key and provide the generated OTP code.',
        ],

        'base' => [
            'wrong_user' => 'The authenticated user object must be a Filament Auth model to allow the profile page to update it.',
            'rate_limit_exceeded' => 'Too many requests',
            'try_again' => 'Please try again in :seconds seconds',
        ],

        '2fa' => [
            'confirm' => 'Confirm',
            'cancel' => 'Cancel',
            'enable' => 'Enable',
            'disable' => 'Disable',
            'confirm_password' => 'Confirm Password',
            'wrong_password' => 'The provided password was incorrect.',
            'code' => 'Code',
            'setup_key' => 'Setup Key: :setup_key.',
            'current_password' => 'Current Password',
            'regenerate_recovery_codes' => 'Generate New Recovery Codes',
        ],

        'passkey' => [
            'add' => 'Create Passkey',
            'name' => 'Name',
            'added' => 'Passkey added successfully.',
            'login' => 'Login with Passkey',
            'tootip' => 'Use Face ID, fingerprint, or PIN',
            'notice' => [
                'header' => 'Passkeys are a passwordless login method using your device’s biometric authentication. Instead of typing a password, you approve login on your trusted device.',
            ],
        ],
    ],

    'pages' => [
        'subheading' => 'Or',
        'challenge' => [
            'title' => 'Two Factor Challenge',
            'action_label' => 'Use a recovery code',
            'confirm' => 'Please confirm access to your account by entering the authentication code provided by your authenticator application.',
            'code' => 'Code',
            'error' => 'The provided two factor authentication code was invalid.',
        ],
        'recovery' => [
            'action_label' => 'Use an authentication code',
            'form_hint' => 'Please confirm access to your account by entering one of your emergency recovery codes.',
            'error' => 'The provided two factor authentication code was invalid.',
            'title' => 'Recovery Code',
        ],
    ],

    'actions' => [
        'confirm_two_factor_authentication' => [
            'wrong_code' => 'The provided two factor authentication code was invalid.',
        ],
    ],
];
