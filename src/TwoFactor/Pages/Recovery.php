<?php

namespace Filament\Jetstream\TwoFactor\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Jetstream\Turnstile\ValidatesTurnstile;
use Filament\Jetstream\TwoFactor\Events\ValidTwoFactorRecoveryCodeProvided;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class Recovery extends BaseSimplePage
{
    use ValidatesTurnstile;

    protected string $view = 'filament-team-guard::pages.auth.recovery';

    public ?array $data = [];

    public ?string $turnstileResponse = null;

    public function mount(): void
    {
        if (! Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());

            return;
        }

        $this->form->fill();
    }

    public function authenticate(?string $turnstileToken = null): ?\Symfony\Component\HttpFoundation\Response
    {
        try {
            $this->validateTurnstile($turnstileToken);
            $this->rateLimit(5);

            $this->form->getState();

            $user = Filament::auth()->user();

            $user->setTwoFactorChallengePassed();

            event(new ValidTwoFactorRecoveryCodeProvided($user));

            $this->redirectIntended(filament()->getCurrentOrDefaultPanel()->getUrl());

            return null;
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }
    }

    protected function onValidationError(ValidationException $exception): void
    {
        $this->dispatchTurnstileReset();

        parent::onValidationError($exception);
    }

    public function challengeAction(): Action
    {
        return Action::make('two_factor_challenge_login')
            ->link()
            ->label(__('filament-team-guard::two_factor.pages.recovery.action_label'))
            ->url(filament()->getCurrentOrDefaultPanel()->route('two-factor.challenge'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('recovery_code')
                    ->hiddenLabel()
                    ->hint(__('filament-team-guard::two_factor.pages.recovery.form_hint'))
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->rules([
                        fn () => function (string $attribute, $value, $fail): void {
                            $user = Filament::auth()->user();

                            $validCode = collect($user->recoveryCodes())->first(
                                fn ($code) => hash_equals($code, $value) ? $code : null
                            );

                            if (! $validCode) {
                                $fail(__('filament-team-guard::two_factor.pages.recovery.error'));
                            }
                        },
                    ]),
            ])
            ->statePath('data');
    }

    public function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-panels::auth/pages/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getTitle(): string | Htmlable
    {
        return __('filament-team-guard::two_factor.pages.recovery.title');
    }
}
