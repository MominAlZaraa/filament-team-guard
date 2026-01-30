<?php

namespace Filament\Jetstream\Livewire\Teams;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Models\Team;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;

class UpdateTeamName extends BaseLivewireComponent
{
    public ?array $data = [];

    public Team $team;

    public int $teamId;

    public function mount(Team $team): void
    {
        $this->team = $team;
        $this->teamId = $team->id;

        $this->form->fill($team->only(['name']));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label(__('filament-team-guard::default.form.team_name.label'))
                    ->string()
                    ->maxLength(255)
                    ->required(),
                Actions::make([
                    Action::make('save')
                        ->label(__('filament-team-guard::default.action.save.label'))
                        ->action(fn () => $this->updateTeamName($this->team)),
                ])->alignEnd(),
            ])
            ->statePath('data');
    }

    public function updateTeamName(Team $team): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }

        $data = $this->form->getState();

        // Always refresh the team from the database using the stored team ID
        // This ensures we have a fresh instance that exists in the database
        $team = get_class($this->team)::findOrFail($this->teamId);

        // Update the team name using update() which ensures we're updating an existing record
        $team->update([
            'name' => $data['name'],
        ]);

        $this->sendNotification();
    }

    public function render()
    {
        return view('filament-team-guard::livewire.teams.update-team-name');
    }
}
