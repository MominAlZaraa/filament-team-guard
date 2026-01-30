<?php

namespace Filament\Jetstream\TwoFactor\Events;

use Illuminate\Foundation\Auth\User;

class RecoveryCodeReplaced extends TwoFactorAuthenticationEvent
{
    public function __construct(
        User $user,
        public string $code,
    ) {
        parent::__construct($user);
    }
}
