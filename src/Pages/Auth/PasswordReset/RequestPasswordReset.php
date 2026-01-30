<?php

namespace Filament\Jetstream\Pages\Auth\PasswordReset;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Jetstream\Turnstile\ValidatesTurnstile;
use Illuminate\Validation\ValidationException;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    use ValidatesTurnstile;

    public ?string $turnstileResponse = null;

    public function request(?string $turnstileToken = null): void
    {
        $this->validateTurnstile($turnstileToken);

        parent::request();
    }

    protected function onValidationError(ValidationException $exception): void
    {
        $this->dispatchTurnstileReset();

        parent::onValidationError($exception);
    }
}
