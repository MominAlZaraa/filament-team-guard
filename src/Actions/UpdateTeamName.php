<?php

namespace Filament\Jetstream\Actions;

use Filament\Jetstream\Contracts\UpdatesTeamNames;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class UpdateTeamName implements UpdatesTeamNames
{
    /**
     * Validate and update the given team's name.
     *
     * @param  array<string, string>  $input
     */
    public function update(FilamentUser $user, Model $team, array $input): void
    {
        Gate::forUser($user)->authorize('update', $team);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('updateTeamName');

        // Find the team by ID to ensure we have a fresh instance that exists in the database
        // Use get_class() to support custom team models
        $team = get_class($team)::findOrFail($team->id);

        // Update the team name using update() which ensures we're updating an existing record
        $team->update([
            'name' => $input['name'],
        ]);
    }
}
