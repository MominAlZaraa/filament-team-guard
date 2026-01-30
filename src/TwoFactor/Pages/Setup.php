<?php

namespace Filament\Jetstream\TwoFactor\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Jetstream\TwoFactor\TwoFactorAuthenticationPlugin;
use Filament\Schemas\Components\Actions as ActionsComponent;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class Setup extends BaseSimplePage
{
    protected string $view = 'filament-team-guard::pages.auth.setup';

    public ?array $data = [];

    public function mount(): void
    {
        if (! Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }
    }

    public function getTitle(): string | Htmlable
    {
        return '';
    }

    public function utilityActionsForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                ActionsComponent::make([
                    Action::make('dashboard')
                        ->visible(
                            ! TwoFactorAuthenticationPlugin::get()->hasForcedTwoFactorSetup()
                            || filament()->auth()->user()->hasEnabledTwoFactorAuthentication()
                        )
                        ->label(__('filament-team-guard::two_factor.section.dashboard'))
                        ->url(fn () => filament()->getCurrentOrDefaultPanel()->getUrl())
                        ->color('gray')
                        ->icon('heroicon-o-home')
                        ->link(),
                ])->fullWidth(),
            ]);
    }
}
