<?php

namespace Filament\Jetstream\TwoFactor\Contracts;

/**
 * Contract for the two-factor authentication provider used by Filament Jetstream.
 *
 * Adapted from the interface in stephenjude/filament-two-factor-authentication.
 */
interface TwoFactorAuthenticationProvider
{
    public function generateSecretKey(): string;

    public function qrCodeUrl(string $companyName, string $companyEmail, string $secret): string;

    public function verify(string $secret, string $code): bool;
}
