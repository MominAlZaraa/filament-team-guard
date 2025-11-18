<?php

namespace Filament\Jetstream\Actions;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ValidateTeamDeletion
{
    /**
     * Validate that the team can be deleted by the given user.
     */
    public function validate(FilamentUser $user, Model $team): void
    {
        Gate::forUser($user)->authorize('delete', $team);

        if ($team->personal_team) {
            throw ValidationException::withMessages([
                'team' => [__('You may not delete your personal team.')],
            ])->errorBag('deleteTeam');
        }
    }
}
