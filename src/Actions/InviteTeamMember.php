<?php

namespace Filament\Jetstream\Actions;

use Closure;
use Filament\Jetstream\Contracts\InvitesTeamMembers;
use Filament\Jetstream\Events\InvitingTeamMember;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Mail\TeamInvitation;
use Filament\Jetstream\Rules\Role;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InviteTeamMember implements InvitesTeamMembers
{
    /**
     * Invite a new team member to the given team.
     */
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

    /**
     * Validate the invite member operation.
     */
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

    /**
     * Get the validation rules for inviting a team member.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function rules(Model $team): array
    {
        $plugin = Jetstream::plugin();

        return array_filter([
            'email' => [
                'required',
                'email',
                Rule::unique($plugin->teamInvitationModel())->where(function (Builder $query) use ($team, $plugin) {
                    $query->where(Jetstream::getForeignKeyColumn($plugin->teamModel()), $team->id);
                }),
            ],
            'role' => ! empty($plugin->getTeamRolesAndPermissions())
                ? ['required', 'string', new Role]
                : null,
        ]);
    }

    /**
     * Ensure that the user is not already on the team.
     */
    protected function ensureUserIsNotAlreadyOnTeam(Model $team, string $email): Closure
    {
        return function ($validator) use ($team, $email) {
            if (method_exists($team, 'hasUserWithEmail') && $team->hasUserWithEmail($email)) {
                $validator->errors()->add(
                    'email',
                    __('filament-jetstream::default.action.add_team_member.error_message.email_already_invited')
                );
            }
        };
    }
}
