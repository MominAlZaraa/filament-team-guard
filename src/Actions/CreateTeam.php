<?php

namespace Filament\Jetstream\Actions;

use Filament\Jetstream\Contracts\CreatesTeams;
use Filament\Jetstream\Events\AddingTeam;
use Filament\Jetstream\Jetstream;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CreateTeam implements CreatesTeams
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  array<string, string>  $input
     */
    public function create(FilamentUser $user, array $input): Model
    {
        $teamModel = Jetstream::plugin()->teamModel();

        Gate::forUser($user)->authorize('create', new $teamModel);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('createTeam');

        AddingTeam::dispatch($user);

        $user->switchTeam(
            $team = $user->ownedTeams()->create([
                'name' => $input['name'],
                'personal_team' => false,
            ])
        );

        return $team;
    }
}
