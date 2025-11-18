<?php

namespace Filament\Jetstream\Actions;

use Filament\Jetstream\Contracts\DeletesTeams;
use Illuminate\Database\Eloquent\Model;

class DeleteTeam implements DeletesTeams
{
    /**
     * Delete the given team.
     */
    public function delete(Model $team): void
    {
        $team->purge();
    }
}
