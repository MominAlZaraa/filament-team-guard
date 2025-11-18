<?php

namespace App\Actions\FilamentJetstream;

use Filament\Jetstream\Actions\UpdateTeamName as BaseUpdateTeamName;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class UpdateTeamName extends BaseUpdateTeamName
{
    /*
     You can customize the team name update logic here by overriding methods from the base class.

     Common customization scenarios:
     - Add additional validation rules
     - Modify update behavior
     - Log activity or send notifications

     public function update(FilamentUser $user, Model $team, array $input): void
     {
         Gate::forUser($user)->authorize('update', $team);

         Validator::make($input, [
             'name' => ['required', 'string', 'max:255'],
         ])->validateWithBag('updateTeamName');

         $team->forceFill([
             'name' => $input['name'],
         ])->save();
     }
     */
}
