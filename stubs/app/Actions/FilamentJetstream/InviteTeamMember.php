<?php

namespace App\Actions\FilamentJetstream;

use Filament\Jetstream\Actions\InviteTeamMember as BaseInviteTeamMember;
use Filament\Jetstream\Events\InvitingTeamMember;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Mail\TeamInvitation;
use Filament\Jetstream\Rules\Role;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class InviteTeamMember extends BaseInviteTeamMember
{
    /*
     You can customize the invitation logic here by overriding methods from the base class.

     Common customization scenarios:
     - Add custom validation rules
     - Modify invitation behavior
     - Customize invitation email sending

     public function invite(FilamentUser $user, Model $team, string $email, ?string $role = null): void
     {
         Gate::forUser($user)->authorize('addTeamMember', $team);

         $this->validate($team, $email, $role);

         InvitingTeamMember::dispatch($team, $email, $role);

         $invitation = $team->teamInvitations()->create([
             'email' => $email,
             'role' => $role,
         ]);

         Mail::to($email)->send(new TeamInvitation($invitation));
     }

     protected function validate(Model $team, string $email, ?string $role): void
     {
         Validator::make([
             'email' => $email,
             'role' => $role,
         ], $this->rules($team), [
             'email.unique' => __('filament-jetstream::default.action.add_team_member.error_message.email_already_invited'),
         ])->after(
             $this->ensureUserIsNotAlreadyOnTeam($team, $email)
         )->validateWithBag('addTeamMember');
     }
     */
}
