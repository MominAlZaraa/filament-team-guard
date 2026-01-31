<?php

namespace Filament\Jetstream\Pages;

use Filament\Facades\Filament;
use Filament\Jetstream\Jetstream;

class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-user-circle';

    protected string $view = 'filament-team-guard::pages.edit-profile';

    protected static ?string $navigationLabel = 'Profile';

    public function mount(): void
    {
        parent::mount();

        if (! Jetstream::plugin()->hasTeamsFeatures()) {
            return;
        }

        $user = $this->getUser();
        if (! method_exists($user, 'currentTeam')) {
            return;
        }

        $team = $user->currentTeam;
        if ($team && ($id = $team->id)) {
            $model = Jetstream::plugin()->teamModel;
            once(fn () => Filament::setTenant($model::find($id)));
        }
    }

    public static function isSimple(): bool
    {
        return false;
    }
}
