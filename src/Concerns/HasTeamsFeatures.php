<?php

namespace Filament\Jetstream\Concerns;

use Closure;
use Filament\Facades\Filament;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Models\Membership;
use Filament\Jetstream\Models\Team;
use Filament\Jetstream\Models\TeamInvitation;
use Filament\Jetstream\Role;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

trait HasTeamsFeatures
{
    public string $teamModel = Team::class;

    public string $roleModel = Role::class;

    public string $membershipModel = Membership::class;

    public string $teamInvitationModel = TeamInvitation::class;

    public Closure | bool $hasTeamFeature = false;

    public ?Closure $acceptTeamInvitation = null;

    public function hasTeamsFeatures(): bool
    {
        return $this->evaluate($this->hasTeamFeature) === true;
    }

    public function teams(Closure | bool $condition = true, ?Closure $acceptTeamInvitation = null): static
    {
        $this->hasTeamFeature = $condition;

        $this->acceptTeamInvitation = $acceptTeamInvitation;

        return $this;
    }

    /**
     * @return array<int, Role>
     */
    public function getTeamRolesAndPermissions(): array
    {
        return $this->roleModel::roles()->toArray();
    }

    public function teamsRoutes(): array
    {
        return [
            Route::get('/team-invitations/{invitation}', fn ($invitation) => $this->acceptTeamInvitation === null
                ? $this->defaultAcceptTeamInvitation($invitation)
                : $this->evaluate($this->acceptTeamInvitation, ['invitationId' => $invitation]))
                ->middleware(['signed'])
                ->name('team-invitations.accept'),
        ];
    }

    public function configureTeamModels(
        string $teamModel = Team::class,
        string $roleModel = Role::class,
        string $membershipModel = Membership::class,
        string $teamInvitationModel = TeamInvitation::class
    ): static {
        $this->teamModel = $teamModel;

        $this->roleModel = $roleModel;

        $this->membershipModel = $membershipModel;

        $this->teamInvitationModel = $teamInvitationModel;

        return $this;
    }

    public function teamModel(): string
    {
        return $this->teamModel;
    }

    public function membershipModel(): string
    {
        return $this->membershipModel;
    }

    public function teamInvitationModel(): string
    {
        return $this->teamInvitationModel;
    }

    public function roleModel(): string
    {
        return $this->roleModel;
    }

    public function defaultAcceptTeamInvitation(string | int $invitationId): RedirectResponse
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
            message: __('filament-jetstream::default.action.add_team_member.error_message.email_already_joined')
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
        /** @var \Filament\Jetstream\Contracts\AddsTeamMembers $addTeamMemberAction */
        $addTeamMemberAction = app(\Filament\Jetstream\Contracts\AddsTeamMembers::class);

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
            ->title(__('filament-jetstream::default.notification.accepted_invitation.success.title'))
            ->body(
                __('filament-jetstream::default.notification.accepted_invitation.success.message', [
                    'team' => $team->name,
                ])
            )
            ->send();

        return redirect()->to($panel->getHomeUrl());
    }

    /**
     * Check for and accept any pending team invitations after authentication.
     * This should be called after login or registration.
     */
    public function acceptPendingTeamInvitation(): void
    {
        if (! Filament::auth()->check()) {
            return;
        }

        $invitationId = session()->pull('team_invitation_id');

        if (! $invitationId) {
            return;
        }

        $model = Jetstream::plugin()->teamInvitationModel();
        $invitation = $model::whereKey($invitationId)->with('team')->first();

        if (! $invitation) {
            return;
        }

        $user = Filament::auth()->user();

        // Verify the invitation email matches the authenticated user
        if ($invitation->email !== $user->email) {
            return;
        }

        $team = $invitation->team;

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

        // Show success notification
        Notification::make()
            ->success()
            ->title(__('filament-jetstream::default.notification.accepted_invitation.success.title'))
            ->body(
                __('filament-jetstream::default.notification.accepted_invitation.success.message', [
                    'team' => $team->name,
                ])
            )
            ->send();
    }
}
