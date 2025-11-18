<?php

namespace App\Actions\FilamentJetstream;

use Filament\Jetstream\Actions\CreateTeam as BaseCreateTeam;
use Filament\Jetstream\Events\AddingTeam;
use Filament\Jetstream\Jetstream;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CreateTeam extends BaseCreateTeam
{
    /*
     You can customize the team creation logic here by overriding methods from the base class.

     Common customization scenarios:
     - Add additional validation rules
     - Modify team creation behavior
     - Add custom attributes during team creation

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
     */
}
