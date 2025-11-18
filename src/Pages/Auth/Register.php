<?php

namespace Filament\Jetstream\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Jetstream\Jetstream;
use Illuminate\Database\Eloquent\Model;

class Register extends \Filament\Auth\Pages\Register
{
    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);

        // Check if there's a pending team invitation
        $invitationId = session()->pull('team_invitation_id');

        if ($invitationId) {
            // Verify the invitation email matches the registered user
            $model = Jetstream::plugin()->teamInvitationModel();
            $invitation = $model::whereKey($invitationId)->first();

            if ($invitation && $invitation->email === $user->email) {
                // Accept the invitation after successful registration
                $this->acceptTeamInvitationAfterAuth($invitationId);

                // Don't create personal team if invitation was accepted
                return $user;
            }
        }

        // Create personal team if tenancy is enabled and no invitation was accepted
        if (Filament::hasTenancy() && ! $user->currentTeam) {
            $user->switchTeam($user->ownedTeams()->create([
                'name' => 'My Workspace',
                'personal_team' => true,
            ]));
        }

        return $user;
    }

    /**
     * Accept a team invitation after user has been authenticated.
     */
    protected function acceptTeamInvitationAfterAuth(string | int $invitationId): void
    {
        $model = Jetstream::plugin()->teamInvitationModel();
        $invitation = $model::whereKey($invitationId)->with('team')->first();

        if (! $invitation) {
            return;
        }

        $team = $invitation->team;
        $user = Filament::auth()->user();

        // Check if user is already on the team
        if ($team->hasUserWithEmail($user->email)) {
            $invitation->delete();

            return;
        }

        // Add user to team using AddsTeamMembers contract (Jetstream pattern)
        /** @var \Filament\Jetstream\Contracts\AddsTeamMembers $addTeamMemberAction */
        $addTeamMemberAction = app(\Filament\Jetstream\Contracts\AddsTeamMembers::class);

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
    }
}
