<?php

namespace Filament\Jetstream\TwoFactor\Actions;

use Filament\Jetstream\TwoFactor\Events\TwoFactorAuthenticationDisabled;
use Illuminate\Foundation\Auth\User;

class DisableTwoFactorAuthentication
{
    public function __invoke(User $user): void
    {
        if (! is_null($user->two_factor_secret) ||
            ! is_null($user->two_factor_recovery_codes) ||
            ! is_null($user->two_factor_confirmed_at)) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->save();

            TwoFactorAuthenticationDisabled::dispatch($user);
        }
    }
}
