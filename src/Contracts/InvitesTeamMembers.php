<?php

namespace Filament\Jetstream\Contracts;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;

/**
 * @method void invite(FilamentUser $user, Model $team, string $email, string $role = null)
 */
interface InvitesTeamMembers
{
    //
}
