<?php

namespace App\Actions\FilamentJetstream;

use Filament\Jetstream\Actions\AddTeamMember as BaseAddTeamMember;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Rules\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class AddTeamMember extends BaseAddTeamMember
{
    /*
     You can customize the add team member logic here by overriding methods from the base class.

     Common customization scenarios:
     - Add custom validation rules
     - Add additional authorization checks
     - Modify the team member addition flow

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

     protected function rules(string $userTable): array
     {
         $plugin = Jetstream::plugin();

         return array_filter([
             'email' => ['required', 'email', 'exists:' . $userTable . ',email'],
             'role' => !empty($plugin->getTeamRolesAndPermissions())
                 ? ['required', 'string', new Role]
                 : null,
         ]);
     }
     */
}
