<?php

namespace App\Actions\FilamentJetstream;

use Filament\Jetstream\Actions\DeleteTeam as BaseDeleteTeam;
use Illuminate\Database\Eloquent\Model;

class DeleteTeam extends BaseDeleteTeam
{
    /*
     You can customize the team deletion logic here by overriding methods from the base class.

     Common customization scenarios:
     - Add custom logic before or after team deletion
     - Perform additional cleanup
     - Log activity or send notifications

     public function delete(Model $team): void
     {
         $team->purge();
     }
     */
}
