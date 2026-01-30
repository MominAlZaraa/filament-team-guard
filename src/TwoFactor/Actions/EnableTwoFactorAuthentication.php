<?php

namespace Filament\Jetstream\TwoFactor\Actions;

use Filament\Jetstream\TwoFactor\Contracts\TwoFactorAuthenticationProvider;
use Filament\Jetstream\TwoFactor\Events\TwoFactorAuthenticationEnabled;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;

class EnableTwoFactorAuthentication
{
    public function __construct(
        protected TwoFactorAuthenticationProvider $provider,
    ) {}

    public function __invoke(User $user, bool $force = false): void
    {
        if (empty($user->two_factor_secret) || $force === true) {
            $user->forceFill([
                'two_factor_secret' => encrypt($this->provider->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(
                    8,
                    fn () => RecoveryCode::generate()
                )->all())),
            ])->save();

            $user->setTwoFactorChallengePassed();

            TwoFactorAuthenticationEnabled::dispatch($user);
        }
    }
}
