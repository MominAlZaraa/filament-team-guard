<?php

namespace Filament\Jetstream\TwoFactor\Actions;

use Filament\Jetstream\TwoFactor\Events\RecoveryCodesGenerated;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;

class GenerateNewRecoveryCodes
{
    public function __invoke(User $user): void
    {
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(
                Collection::times(8, fn () => RecoveryCode::generate())->all()
            )),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }
}
