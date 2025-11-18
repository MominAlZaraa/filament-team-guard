<?php

namespace Filament\Jetstream\Actions;

use Closure;
use Filament\Jetstream\Contracts\AddsTeamMembers;
use Filament\Jetstream\Events\AddingTeamMember;
use Filament\Jetstream\Events\TeamMemberAdded;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Rules\Role;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class AddTeamMember implements AddsTeamMembers
{
    /**
     * Add a new team member to the given team.
     */
    public function add(FilamentUser $user, Model $team, string $email, ?string $role = null): void
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        $this->validate($team, $email, $role);

        $userModel = Jetstream::plugin()->userModel();
        $newTeamMember = $userModel::where('email', $email)->firstOrFail();

        AddingTeamMember::dispatch($team, $newTeamMember);

        $team->users()->attach(
            $newTeamMember,
            ['role' => $role]
        );

        TeamMemberAdded::dispatch($team, $newTeamMember);
    }

    /**
     * Validate the add member operation.
     */
    protected function validate(Model $team, string $email, ?string $role): void
    {
        $userModel = Jetstream::plugin()->userModel();
        $userTable = (new $userModel)->getTable();

        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules($userTable), [
            'email.exists' => __('We were unable to find a registered user with this email address.'),
        ])->after(
            $this->ensureUserIsNotAlreadyOnTeam($team, $email)
        )->validateWithBag('addTeamMember');
    }

    /**
     * Get the validation rules for adding a team member.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function rules(string $userTable): array
    {
        $plugin = Jetstream::plugin();

        return array_filter([
            'email' => ['required', 'email', 'exists:'.$userTable.',email'],
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
                    __('This user already belongs to the team.')
                );
            }
        };
    }
}
