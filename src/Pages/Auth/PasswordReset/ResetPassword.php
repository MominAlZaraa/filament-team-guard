<?php

namespace Filament\Jetstream\Pages\Auth\PasswordReset;

use Filament\Auth\Http\Responses\Contracts\PasswordResetResponse;
use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Filament\Jetstream\Turnstile\ValidatesTurnstile;
use Illuminate\Validation\ValidationException;

class ResetPassword extends BaseResetPassword
{
    use ValidatesTurnstile;

    public ?string $turnstileResponse = null;

    public function resetPassword(?string $turnstileToken = null): ?PasswordResetResponse
    {
        $this->validateTurnstile($turnstileToken);

        return parent::resetPassword();
    }

    protected function onValidationError(ValidationException $exception): void
    {
        $this->dispatchTurnstileReset();

        parent::onValidationError($exception);
    }
}
