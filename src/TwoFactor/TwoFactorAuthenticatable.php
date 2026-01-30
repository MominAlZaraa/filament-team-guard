<?php

namespace Filament\Jetstream\TwoFactor;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Filament\Jetstream\TwoFactor\Actions\RecoveryCode;
use Filament\Jetstream\TwoFactor\Events\RecoveryCodeReplaced;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;

/**
 * Trait providing two-factor authentication helpers for the Filament Jetstream user model.
 *
 * This implementation is heavily inspired by the excellent
 * stephenjude/filament-two-factor-authentication package by Stephen Jude.
 */
trait TwoFactorAuthenticatable
{
    use InteractsWithPasskeys;

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return ! is_null($this->two_factor_secret)
            && ! is_null($this->two_factor_confirmed_at);
    }

    public function hasEnabledPasskeyAuthentication(): bool
    {
        return (bool) $this?->passkeys()?->exists();
    }

    public function passkeyAuthenticated(): bool
    {
        $passkeyAuthenticated = Cache::pull("passkey::auth::$this->id", false);

        if ($passkeyAuthenticated && $this->hasEnabledTwoFactorAuthentication()) {
            $this->setTwoFactorChallengePassed();
        }

        return $passkeyAuthenticated;
    }

    public function isTwoFactorChallengePassed(): bool
    {
        if ($twoFactorSecretFromSession = session()->get("login:challenge:secret:$this->id")) {
            return decrypt($this->two_factor_secret) === decrypt($twoFactorSecretFromSession);
        }

        return false;
    }

    public function setTwoFactorChallengePassed(): void
    {
        session()->put("login:challenge:secret:$this->id", $this->two_factor_secret);
    }

    public function recoveryCodes(): array
    {
        return json_decode(decrypt($this->two_factor_recovery_codes), true);
    }

    public function replaceRecoveryCode(string $code): void
    {
        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(
                str_replace(
                    $code,
                    RecoveryCode::generate(),
                    decrypt($this->two_factor_recovery_codes)
                )
            ),
        ])->save();

        RecoveryCodeReplaced::dispatch($this, $code);
    }

    public function twoFactorQrCodeSvg(): string
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(192, 0, null, null, Fill::uniformColor(
                    new Rgb(255, 255, 255),
                    new Rgb(45, 55, 72)
                )),
                new SvgImageBackEnd
            )
        ))->writeString($this->twoFactorQrCodeUrl());

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    public function twoFactorQrCodeUrl(): string
    {
        return app(TwoFactorAuthenticationProvider::class)->qrCodeUrl(
            companyName: config('app.name'),
            companyEmail: $this->email,
            secret: decrypt($this->two_factor_secret),
        );
    }
}
