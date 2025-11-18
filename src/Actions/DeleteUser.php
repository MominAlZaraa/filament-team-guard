<?php

namespace Filament\Jetstream\Actions;

use Filament\Jetstream\Contracts\DeletesUsers;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Models\Team;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Facades\DB;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(FilamentUser $user): void
    {
        DB::transaction(function () use ($user) {
            if (Jetstream::plugin()?->hasTeamsFeatures()) {
                $user->teams()->detach();

                $user->ownedTeams->each(function (Team $team) {
                    $team->delete();
                });
            }

            $user->deleteProfilePhoto();

            $user->tokens?->each->delete();

            $user->delete();
        });
    }
}
