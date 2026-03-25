<?php

namespace Filament\Jetstream\Tests\Feature;

use Filament\Jetstream\Livewire\Teams\PendingTeamInvitations;
use Filament\Jetstream\Models\Team;
use Filament\Jetstream\Tests\TestCase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class SecurityHardeningTest extends TestCase
{
    public function test_resend_invitation_requires_authorization(): void
    {
        $component = new PendingTeamInvitations;
        $team = $this->makeTeam(1);
        $invitation = $this->makeInvitation(1, 'member@example.com');
        $component->team = $team;

        Gate::shouldReceive('authorize')
            ->once()
            ->with('updateTeamMember', $team)
            ->andThrow(new AuthorizationException);

        $this->expectException(AuthorizationException::class);

        $component->resendTeamInvitation($invitation);
    }

    public function test_cancel_invitation_requires_authorization(): void
    {
        $component = new PendingTeamInvitations;
        $team = $this->makeTeam(1);
        $invitation = $this->makeInvitation(1, 'member@example.com');
        $component->team = $team;

        Gate::shouldReceive('authorize')
            ->once()
            ->with('removeTeamMember', $team)
            ->andThrow(new AuthorizationException);

        $this->expectException(AuthorizationException::class);

        $component->cancelTeamInvitation($team, $invitation);
    }

    public function test_resend_invitation_rejects_cross_team_invitation(): void
    {
        Mail::fake();

        $component = new PendingTeamInvitations;
        $team = $this->makeTeam(1);
        $invitation = $this->makeInvitation(2, 'member@example.com');
        $component->team = $team;

        Gate::shouldReceive('authorize')
            ->once()
            ->with('updateTeamMember', $team)
            ->andReturnTrue();

        $this->expectException(AuthorizationException::class);

        $component->resendTeamInvitation($invitation);
    }

    public function test_cancel_invitation_rejects_cross_team_invitation(): void
    {
        $component = new PendingTeamInvitations;
        $team = $this->makeTeam(1);
        $invitation = $this->makeInvitation(2, 'member@example.com');
        $component->team = $team;

        Gate::shouldReceive('authorize')
            ->once()
            ->with('removeTeamMember', $team)
            ->andReturnTrue();

        $this->expectException(AuthorizationException::class);

        $component->cancelTeamInvitation($team, $invitation);
    }

    protected function makeTeam(int $id): Team
    {
        $team = new Team;
        $team->setAttribute('id', $id);

        return $team;
    }

    protected function makeInvitation(int $teamId, string $email): Model
    {
        $invitation = new class extends Model {};
        $invitation->setAttribute('team_id', $teamId);
        $invitation->setAttribute('email', $email);

        return $invitation;
    }
}
