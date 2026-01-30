<?php

namespace Filament\Jetstream\TwoFactor;

use Filament\Jetstream\TwoFactor\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Illuminate\Contracts\Cache\Repository;
use PragmaRX\Google2FA\Google2FA;

/**
 * Google2FA-backed implementation of the two-factor provider for Filament Jetstream.
 *
 * Largely based on the provider from stephenjude/filament-two-factor-authentication.
 */
class TwoFactorAuthenticationProvider implements TwoFactorAuthenticationProviderContract
{
    protected Google2FA $engine;

    protected ?Repository $cache;

    public function __construct(Google2FA $engine, ?Repository $cache = null)
    {
        $this->engine = $engine;
        $this->cache = $cache;
    }

    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function qrCodeUrl(string $companyName, string $companyEmail, string $secret): string
    {
        return $this->engine->getQRCodeUrl($companyName, $companyEmail, $secret);
    }

    public function verify(string $secret, string $code): bool
    {
        if (is_int($customWindow = config('fortify-options.two-factor-authentication.window'))) {
            $this->engine->setWindow($customWindow);
        }

        $timestamp = $this->engine->verifyKeyNewer(
            $secret,
            $code,
            optional($this->cache)->get($key = 'fortify.2fa_codes.' . md5($code))
        );

        if ($timestamp === false) {
            return false;
        }

        if ($timestamp === true) {
            $timestamp = $this->engine->getTimestamp();
        }

        optional($this->cache)->put(
            $key,
            $timestamp,
            ($this->engine->getWindow() ?: 1) * 60
        );

        return true;
    }
}
