<?php

namespace Filament\Jetstream\Pages\Auth;

use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Facades\Filament;
use Filament\Jetstream\Contracts\AddsTeamMembers;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Turnstile\ValidatesTurnstile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Register extends \Filament\Auth\Pages\Register
{
    use ValidatesTurnstile;

    public ?string $turnstileResponse = null;

    public function register(?string $turnstileToken = null): ?RegistrationResponse
    {
        $this->validateTurnstile($turnstileToken);

        return parent::register();
    }

    protected function onValidationError(ValidationException $exception): void
    {
        $this->dispatchTurnstileReset();

        parent::onValidationError($exception);
    }

    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);

        $invitationId = session()->pull('team_invitation_id');

        if ($invitationId) {
            $model = Jetstream::plugin()->teamInvitationModel();
            $invitation = $model::whereKey($invitationId)->first();

            if ($invitation && $invitation->email === $user->email) {
                $this->acceptTeamInvitationAfterAuth($invitationId);

                return $user;
            }
        }

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

        if ($team->hasUserWithEmail($user->email)) {
            $invitation->delete();

            return;
        }

        /** @var AddsTeamMembers $addTeamMemberAction */
        $addTeamMemberAction = app(AddsTeamMembers::class);

        $addTeamMemberAction->add(
            $team->owner,
            $team,
            $invitation->email,
            $invitation->role
        );

        $user->switchTeam($team);

        $invitation->delete();
    }
}
