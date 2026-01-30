<?php

namespace Filament\Jetstream\TwoFactor\Actions;

use Illuminate\Support\Str;

/**
 * Generate recovery codes for two-factor authentication.
 *
 * Based on stephenjude/filament-two-factor-authentication.
 */
class RecoveryCode
{
    public static function generate(): string
    {
        return Str::random(10) . '-' . Str::random(10);
    }
}
