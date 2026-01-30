<?php

namespace Filament\Jetstream\Actions;

use Filament\Facades\Filament;
use Filament\Jetstream\Contracts\AcceptsTeamInvitations;
use Filament\Jetstream\Contracts\AddsTeamMembers;
use Filament\Jetstream\Jetstream;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;

class AcceptTeamInvitation implements AcceptsTeamInvitations
{
    /**
     * Accept a team invitation.
     */
    public function accept(string | int $invitationId): RedirectResponse
    {
        $model = Jetstream::plugin()->teamInvitationModel();
        $invitation = $model::whereKey($invitationId)->with('team')->firstOrFail();

        $team = $invitation->team;
        $panel = Filament::getCurrentPanel();
        $userModel = Jetstream::plugin()->userModel();

        // Check if user exists (following Jetstream pattern - user must exist)
        $user = $userModel::where('email', $invitation->email)->first();

        // If user doesn't exist, redirect to registration (if enabled) or show error
        if (! $user) {
            if ($panel->hasRegistration()) {
                // Store invitation ID in session to accept after registration
                session()->put('team_invitation_id', $invitationId);

                return redirect()->to($panel->getRegistrationUrl());
            }

            abort(403, __('We were unable to find a registered user with this email address. Please create an account first.'));
        }

        // Check if user is already on the team
        abort_if(
            boolean: $team->hasUserWithEmail($user->email),
            code: 403,
            message: __('filament-team-guard::default.action.add_team_member.error_message.email_already_joined')
        );

        // If user is not authenticated, redirect to login
        if (! Filament::auth()->check()) {
            // Store invitation ID in session to accept after login
            session()->put('team_invitation_id', $invitationId);

            return redirect()->to($panel->getLoginUrl());
        }

        // If authenticated user doesn't match invitation email, show error
        if (Filament::auth()->user()->email !== $invitation->email) {
            abort(403, __('This invitation is for a different email address.'));
        }

        // User exists and is authenticated - add to team using AddsTeamMembers contract (Jetstream pattern)
        /** @var AddsTeamMembers $addTeamMemberAction */
        $addTeamMemberAction = app(AddsTeamMembers::class);

        // Use team owner for authorization (following Jetstream pattern)
        $addTeamMemberAction->add(
            $team->owner,
            $team,
            $invitation->email,
            $invitation->role
        );

        // Switch to the team
        $user->switchTeam($team);

        // Delete the invitation
        $invitation->delete();

        // Show success notification
        Notification::make()
            ->success()
            ->title(__('filament-team-guard::default.notification.accepted_invitation.success.title'))
            ->body(
                __('filament-team-guard::default.notification.accepted_invitation.success.message', [
                    'team' => $team->name,
                ])
            )
            ->send();

        return redirect()->to($panel->getHomeUrl());
    }
}
