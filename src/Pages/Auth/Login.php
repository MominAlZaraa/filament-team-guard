<?php

namespace Filament\Jetstream\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Jetstream\Turnstile\ValidatesTurnstile;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    use ValidatesTurnstile;

    public ?string $turnstileResponse = null;

    public function authenticate(?string $turnstileToken = null): ?\Filament\Auth\Http\Responses\Contracts\LoginResponse
    {
        $this->validateTurnstile($turnstileToken);

        return parent::authenticate();
    }

    protected function onValidationError(ValidationException $exception): void
    {
        $this->dispatchTurnstileReset();

        parent::onValidationError($exception);
    }

    protected function throwFailureValidationException(): never
    {
        $this->dispatchTurnstileReset();

        $message = __('filament-panels::auth/pages/login.messages.failed');
        session()->flash('filament-team-guard.login_error', $message);
        Notification::make()
            ->title($message)
            ->danger()
            ->send();

        parent::throwFailureValidationException();
    }
}
