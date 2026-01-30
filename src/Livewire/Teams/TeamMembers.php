<?php

namespace Filament\Jetstream\Livewire\Teams;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Jetstream\Actions\UpdateTeamMemberRole;
use Filament\Jetstream\Contracts\RemovesTeamMembers;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Livewire\BaseLivewireComponent;
use Filament\Jetstream\Models\Team;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class TeamMembers extends BaseLivewireComponent implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    public function table(Table $table): Table
    {
        $model = Jetstream::plugin()->membershipModel();

        $teamForeignKeyColumn = Jetstream::getForeignKeyColumn(get_class($this->team));

        return $table
            ->query(fn () => $model::with('user')->where($teamForeignKeyColumn, $this->team->id))
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\ImageColumn::make('user')
                        ->defaultImageUrl(fn ($record): string => Filament::getUserAvatarUrl($record->user))
                        ->circular()
                        ->width(25)
                        ->height(25),
                    Tables\Columns\TextColumn::make('user.email'),
                ]),
            ])
            ->paginated(false)
            ->recordActions([
                Action::make('updateTeamRole')
                    ->visible(fn ($record): bool => Gate::check('updateTeamMember', $this->team))
                    ->label(fn ($record): string => Jetstream::plugin()->roleModel::find($record->role)?->name ?? __('N/A'))
                    ->modalWidth('lg')
                    ->modalHeading(__('filament-team-guard::default.action.update_team_role.title'))
                    ->modalSubmitActionLabel(__('filament-team-guard::default.action.save.label'))
                    ->modalCancelAction(false)
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->schema([
                        Grid::make()
                            ->columns(1)
                            ->schema(function () {
                                $roles = collect(Jetstream::plugin()?->getTeamRolesAndPermissions());

                                return [
                                    Radio::make('role')
                                        ->hiddenLabel()
                                        ->required()
                                        ->in($roles->pluck('key'))
                                        ->options($roles->pluck('name', 'key'))
                                        ->descriptions($roles->pluck('description', 'key'))
                                        ->default(fn ($record) => $record->role),
                                ];
                            }),
                    ])
                    ->action(fn ($record, array $data) => $this->updateTeamRole($this->team, $record, $data)),
                Action::make('removeTeamMember')
                    ->visible(
                        fn ($record): bool => $this->authUser()->id !== $record->id && Gate::check(
                            'removeTeamMember',
                            $this->team
                        )
                    )
                    ->label(__('filament-team-guard::default.action.remove_team_member.label'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $this->removeTeamMember($this->team, $record)),
                Action::make('leaveTeam')
                    ->visible(fn ($record): bool => $this->authUser()->id === $record->id)
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->color('danger')
                    ->label(__('filament-team-guard::default.action.leave_team.label'))
                    ->modalDescription(__('filament-team-guard::default.action.leave_team.notice'))
                    ->requiresConfirmation()
                    ->action(fn ($record) => $this->leaveTeam($record)),
            ]);
    }

    public function updateTeamRole(Model $team, Model $teamMember, array $data): void
    {
        /** @var UpdateTeamMemberRole $updater */
        $updater = app(UpdateTeamMemberRole::class);

        $updater->update(
            $this->authUser(),
            $team,
            $teamMember->user_id,
            $data['role']
        );

        $this->sendNotification();

        $team->fresh();
    }

    public function removeTeamMember(Team $team, Model $teamMember): void
    {
        /** @var RemovesTeamMembers $remover */
        $remover = app(RemovesTeamMembers::class);

        $remover->remove(
            $this->authUser(),
            $team,
            $teamMember->user
        );

        $this->sendNotification(__('filament-team-guard::default.notification.team_member_removed.success.message'));

        $team->fresh();
    }

    public function leaveTeam(Team $team): void
    {
        /** @var RemovesTeamMembers $remover */
        $remover = app(RemovesTeamMembers::class);

        $remover->remove(
            $this->authUser(),
            $team,
            $this->authUser()
        );

        $this->sendNotification(__('filament-team-guard::default.notification.leave_team.success'));

        $this->redirect(Filament::getHomeUrl());
    }

    public function render()
    {
        return view('filament-team-guard::livewire.teams.team-members');
    }
}
