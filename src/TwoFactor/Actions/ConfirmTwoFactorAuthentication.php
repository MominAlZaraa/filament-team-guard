<?php

namespace Filament\Jetstream\TwoFactor\Actions;

use Filament\Jetstream\TwoFactor\Contracts\TwoFactorAuthenticationProvider;
use Filament\Jetstream\TwoFactor\Events\TwoFactorAuthenticationConfirmed;
use Illuminate\Foundation\Auth\User;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorAuthentication
{
    public function __construct(
        protected TwoFactorAuthenticationProvider $provider,
    ) {}

    public function __invoke(User $user, string $code): void
    {
        if (empty($user->two_factor_secret) ||
            empty($code) ||
            ! $this->provider->verify(decrypt($user->two_factor_secret), $code)) {
            throw ValidationException::withMessages([
                'data.code' => __('filament-team-guard::two_factor.actions.confirm_two_factor_authentication.wrong_code'),
            ]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        TwoFactorAuthenticationConfirmed::dispatch($user);
    }
}
