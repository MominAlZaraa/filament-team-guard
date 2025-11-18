<?php

namespace App\Actions\FilamentJetstream;

use Filament\Jetstream\Actions\RemoveTeamMember as BaseRemoveTeamMember;
use Filament\Jetstream\Events\RemovingTeamMember;
use Filament\Jetstream\Events\TeamMemberRemoved;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class RemoveTeamMember extends BaseRemoveTeamMember
{
    /*
     You can customize the team member removal logic here by overriding methods from the base class.

     Common customization scenarios:
     - Add custom logic before or after removing a team member
     - Send notifications or log activity
     - Perform additional cleanup

     public function remove(FilamentUser $user, Model $team, FilamentUser $teamMember): void
     {
         Gate::forUser($user)->authorize('removeTeamMember', $team);

         RemovingTeamMember::dispatch($team, $teamMember);

         $team->users()->detach($teamMember);

         TeamMemberRemoved::dispatch($team, $teamMember);
     }
     */
}
