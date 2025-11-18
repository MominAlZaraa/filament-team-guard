<?php

namespace App\Actions\FilamentJetstream;

use Filament\Jetstream\Actions\DeleteUser as BaseDeleteUser;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Models\Team;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Facades\DB;

class DeleteUser extends BaseDeleteUser
{
    /*
     You can customize the user deletion logic here by overriding methods from the base class.

     Common customization scenarios:
     - Add custom logic before or after user deletion
     - Perform additional cleanup
     - Log activity or send notifications

     public function delete(FilamentUser $user): void
     {
         DB::transaction(function () use ($user) {
             if (Jetstream::plugin()?->hasTeamsFeatures()) {
                 $user->teams()->detach();

                 $user->ownedTeams->each(function (Team $team) {
                     $team->delete();
                 });
             }

             $user->deleteProfilePhoto();

             $user->tokens?->each->delete();

             $user->delete();
         });
     }
     */
}
