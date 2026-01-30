<?php

namespace Filament\Jetstream\TwoFactor\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Jetstream\TwoFactor\Actions\ConfirmTwoFactorAuthentication;
use Filament\Jetstream\TwoFactor\Actions\DisableTwoFactorAuthentication;
use Filament\Jetstream\TwoFactor\Actions\EnableTwoFactorAuthentication;
use Filament\Jetstream\TwoFactor\Actions\GenerateNewRecoveryCodes;
use Filament\Jetstream\TwoFactor\TwoFactorAuthenticationPlugin;
use Filament\Schemas\Components\Actions as ActionsComponent;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class TwoFactorAuthentication extends Component implements HasActions, HasForms
{
    use Defaults;

    public ?array $data = [];

    public bool $aside = true;

    public ?string $redirectTo = null;

    public bool $showSetupCode = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function render()
    {
        return view('filament-team-guard::livewire.two-factor-authentication');
    }

    public function setupTwoFactorAuthenticationForm(Schema $schema): Schema
    {
        return $schema
            ->live()
            ->statePath('data')
            ->model($this->getUser())
            ->hidden(fn () => ! $this->showSetupCode)
            ->components([
                TextEntry::make('header')
                    ->hiddenLabel()
                    ->state(__('filament-team-guard::two_factor.components.setup_confirmation.header')),
                TextEntry::make('description')
                    ->hiddenLabel()
                    ->state(__('filament-team-guard::two_factor.components.setup_confirmation.description')),
                TextEntry::make('notice')
                    ->hiddenLabel()
                    ->state(__('filament-team-guard::two_factor.components.setup_confirmation.scan_qr_code')),
                TextEntry::make('qrcode')
                    ->hiddenLabel()
                    ->state(
                        fn ($record) => $record->two_factor_secret ? new HtmlString($record->twoFactorQrCodeSvg()) : ''
                    ),
                TextEntry::make('setup_key')
                    ->label(fn ($record) => __('filament-team-guard::two_factor.components.2fa.setup_key', [
                        'setup_key' => $record->two_factor_secret ? decrypt($record->two_factor_secret) : '',
                    ])),
                TextInput::make('code')
                    ->label(__('filament-team-guard::two_factor.components.2fa.code'))
                    ->required(),
                ActionsComponent::make([
                    Action::make('confirm')
                        ->label(__('filament-team-guard::two_factor.components.2fa.confirm'))
                        ->action(function ($record): void {
                            $data = $this->setupTwoFactorAuthenticationForm->getState();

                            app(ConfirmTwoFactorAuthentication::class)($record, $data['code']);

                            $this->showSetupCode = false;
                            $this->dispatch('refresh-page');
                        }),
                    Action::make('cancel')
                        ->label(__('filament-team-guard::two_factor.components.2fa.cancel'))
                        ->outlined()
                        ->action(function ($record): void {
                            $this->showSetupCode = false;

                            app(DisableTwoFactorAuthentication::class)($record);
                        }),
                ]),
            ]);
    }

    public function enableTwoFactorAuthenticationForm(Schema $schema): Schema
    {
        return $schema
            ->live()
            ->statePath('data')
            ->model($this->getUser())
            ->hidden(fn ($record) => $record->hasEnabledTwoFactorAuthentication() || $this->showSetupCode)
            ->components([
                TextEntry::make('header')
                    ->hiddenLabel()
                    ->state(__('filament-team-guard::two_factor.components.enable.header')),
                TextEntry::make('description')
                    ->hiddenLabel()
                    ->state(__('filament-team-guard::two_factor.components.enable.description')),
                ActionsComponent::make([
                    Action::make('enableTwoFactorAuthentication')
                        ->modalWidth('md')
                        ->label(__('filament-team-guard::two_factor.components.2fa.enable'))
                        ->modalSubmitActionLabel(__('filament-team-guard::two_factor.components.2fa.confirm'))
                        ->action(function ($record): void {
                            $this->showSetupCode = true;

                            app(EnableTwoFactorAuthentication::class)($record);
                        })
                        ->schema(function () {
                            if (! TwoFactorAuthenticationPlugin::get()->twoFactorSetupRequiresPassword()) {
                                return null;
                            }

                            return [
                                TextInput::make('confirmPassword')
                                    ->label(__('filament-team-guard::two_factor.components.2fa.confirm_password'))
                                    ->password()
                                    ->revealable(filament()->arePasswordsRevealable())
                                    ->required()
                                    ->autocomplete('confirm-password')
                                    ->rules([
                                        fn () => function (string $attribute, $value, $fail): mixed {
                                            if (! Hash::check($value, $this->getUser()->password)) {
                                                $fail(
                                                    __('filament-team-guard::two_factor.components.2fa.wrong_password')
                                                );
                                            }

                                            return null;
                                        },
                                    ]),
                            ];
                        }),
                ]),
            ]);
    }

    public function disableTwoFactorAuthenticationForm(Schema $schema): Schema
    {
        return $schema
            ->live()
            ->statePath('data')
            ->model($this->getUser())
            ->hidden(fn () => ! $this->getUser()->hasEnabledTwoFactorAuthentication())
            ->components([
                TextEntry::make('recoveryCode')
                    ->hiddenLabel()
                    ->listWithLineBreaks()
                    ->copyable()
                    ->state(
                        fn () => $this->getUser()->hasEnabledTwoFactorAuthentication()
                            ? $this->getUser()->recoveryCodes()
                            : []
                    ),
                ActionsComponent::make([
                    Action::make('generateNewRecoveryCodes')
                        ->label(__('filament-team-guard::two_factor.components.2fa.regenerate_recovery_codes'))
                        ->outlined()
                        ->requiresConfirmation(! TwoFactorAuthenticationPlugin::get()->twoFactorSetupRequiresPassword())
                        ->modalWidth('md')
                        ->modalSubmitActionLabel(__('filament-team-guard::two_factor.components.2fa.confirm'))
                        ->action(function ($record): mixed {
                            app(GenerateNewRecoveryCodes::class)($record);

                            return null;
                        })
                        ->schema(function () {
                            if (! TwoFactorAuthenticationPlugin::get()->twoFactorSetupRequiresPassword()) {
                                return null;
                            }

                            return [
                                TextInput::make('currentPassword')
                                    ->label(__('filament-team-guard::two_factor.components.2fa.current_password'))
                                    ->password()
                                    ->revealable(filament()->arePasswordsRevealable())
                                    ->required()
                                    ->autocomplete('current-password')
                                    ->rules([
                                        fn () => function (string $attribute, $value, $fail): mixed {
                                            if (! Hash::check($value, $this->getUser()->password)) {
                                                $fail(
                                                    __('filament-team-guard::two_factor.components.2fa.wrong_password')
                                                );
                                            }

                                            return null;
                                        },
                                    ]),
                            ];
                        }),
                    Action::make('disableTwoFactorAuthentication')
                        ->label(__('filament-team-guard::two_factor.components.2fa.disable'))
                        ->color('danger')
                        ->modalWidth('md')
                        ->modalSubmitActionLabel(__('filament-team-guard::two_factor.components.2fa.confirm'))
                        ->action(function ($record): mixed {
                            app(DisableTwoFactorAuthentication::class)($record);

                            return null;
                        })
                        ->requiresConfirmation()
                        ->schema(function () {
                            if (! TwoFactorAuthenticationPlugin::get()->twoFactorSetupRequiresPassword()) {
                                return null;
                            }

                            return [
                                TextInput::make('currentPassword')
                                    ->label(__('filament-team-guard::two_factor.components.2fa.current_password'))
                                    ->password()
                                    ->revealable(filament()->arePasswordsRevealable())
                                    ->required()
                                    ->autocomplete('current-password')
                                    ->rules([
                                        fn () => function (string $attribute, $value, $fail): mixed {
                                            if (! Hash::check($value, $this->getUser()->password)) {
                                                $fail(
                                                    __('filament-team-guard::two_factor.components.2fa.wrong_password')
                                                );
                                            }

                                            return null;
                                        },
                                    ]),
                            ];
                        }),
                ]),
            ]);
    }
}
