<?php

namespace Filament\Jetstream\Actions;

use Filament\Jetstream\Events\TeamMemberUpdated;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Rules\Role;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class UpdateTeamMemberRole
{
    /**
     * Update the role for the given team member.
     */
    public function update(FilamentUser $user, Model $team, int $teamMemberId, string $role): void
    {
        Gate::forUser($user)->authorize('updateTeamMember', $team);

        Validator::make([
            'role' => $role,
        ], [
            'role' => ['required', 'string', new Role],
        ])->validate();

        $team->users()->updateExistingPivot($teamMemberId, [
            'role' => $role,
        ]);

        $userModel = Jetstream::plugin()->userModel();
        $teamMember = $userModel::findOrFail($teamMemberId);

        TeamMemberUpdated::dispatch($team->fresh(), $teamMember);
    }
}
