<?php

namespace Filament\Jetstream\Rules;

use Filament\Jetstream\Jetstream;
use Illuminate\Contracts\Validation\Rule;

class Role implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $plugin = Jetstream::plugin();

        if (! $plugin->hasTeamsFeatures()) {
            return false;
        }

        $roles = collect($plugin->getTeamRolesAndPermissions());

        return $roles->contains('key', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The :attribute must be a valid role.');
    }
}
