<?php

namespace Filament\Jetstream;

use Filament\Jetstream\TwoFactor\TwoFactorAuthenticatable;
use Filament\Panel;

trait InteractsWIthProfile
{
    use HasProfilePhoto;
    use TwoFactorAuthenticatable;

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
