<?php

namespace App\Actions\FilamentJetstream;

use Filament\Jetstream\Actions\AcceptTeamInvitation as BaseAcceptTeamInvitation;
use Illuminate\Http\RedirectResponse;

class AcceptTeamInvitation extends BaseAcceptTeamInvitation
{
    /*
     You can customize the team invitation acceptance logic here by overriding the accept() method.

     The accept() method receives the invitation ID and should return a RedirectResponse.

     Common customization scenarios:
     - Redirect to custom registration page instead of Filament registration
     - Store additional invitation data in session for use during registration
     - Add custom validation or authorization checks
     - Modify the invitation acceptance flow

     public function accept(string|int $invitationId): RedirectResponse
     {
         $model = Jetstream::plugin()->teamInvitationModel();
         $invitation = $model::whereKey($invitationId)->with('team')->firstOrFail();

         $team = $invitation->team;
         $panel = Filament::getCurrentPanel();
         $userModel = Jetstream::plugin()->userModel();

         $user = $userModel::where('email', $invitation->email)->first();

         if (!$user) {
             session()->put('team_invitation_id', $invitationId);
             session()->put('team_invitation_email', $invitation->email);

             return redirect()->route('register')
                 ->with('error', __('You have been invited to join :team. Please register to accept this invitation.', [
                     'team' => $team->name,
                 ]));
         }

         if ($team->hasUserWithEmail($user->email)) {
             abort(403, __('filament-team-guard::default.action.add_team_member.error_message.email_already_joined'));
         }

         if (!Filament::auth()->check()) {
             session()->put('team_invitation_id', $invitationId);
             return redirect()->to($panel->getLoginUrl());
         }

         if (Filament::auth()->user()->email !== $invitation->email) {
             abort(403, __('This invitation is for a different email address.'));
         }

         $addTeamMemberAction = app(\Filament\Jetstream\Contracts\AddsTeamMembers::class);
         $addTeamMemberAction->add($team->owner, $team, $invitation->email, $invitation->role);

         $user->switchTeam($team);
         $invitation->delete();

         Notification::make()
             ->success()
             ->title(__('filament-team-guard::default.notification.accepted_invitation.success.title'))
             ->body(__('filament-team-guard::default.notification.accepted_invitation.success.message', [
                 'team' => $team->name,
             ]))
             ->send();

         return redirect()->to($panel->getHomeUrl());
     }
     */
}
