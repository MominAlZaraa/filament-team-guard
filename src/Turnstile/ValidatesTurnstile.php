<?php

namespace Filament\Jetstream\Turnstile;

use Filament\Jetstream\Jetstream;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use NjoguAmos\Turnstile\Rules\TurnstileRule;

trait ValidatesTurnstile
{
    /**
     * Validate Turnstile token using njoguamos/laravel-turnstile. When the package is disabled (TURNSTILE_ENABLED=false), validation is skipped.
     *
     * @param  string|null  $token  Token from the request (preferred over $this->turnstileResponse, since Livewire does not sync hidden inputs set by JS).
     */
    protected function validateTurnstile(?string $token = null): void
    {
        $plugin = Jetstream::plugin();
        if (! $plugin->usesTurnstile()) {
            return;
        }

        if (! config('turnstile.enabled', true)) {
            return;
        }

        $value = $token ?? $this->turnstileResponse ?? '';

        $validator = Validator::make(
            ['turnstileResponse' => $value],
            ['turnstileResponse' => ['required', new TurnstileRule]],
            [
                'turnstileResponse.required' => __('filament-team-guard::turnstile.required'),
            ]
        );

        if ($validator->fails()) {
            $message = $validator->errors()->first('turnstileResponse');
            $this->flashTurnstileError($message);
            $this->sendTurnstileFailureNotification($message);

            throw new ValidationException($validator);
        }
    }

    /**
     * Flash the error to session so the turnstile view can display it inline.
     */
    protected function flashTurnstileError(string $message): void
    {
        session()->flash('filament-team-guard.turnstile_error', $message);
    }

    /**
     * Send a danger notification for Turnstile-related errors.
     */
    protected function sendTurnstileFailureNotification(string $message): void
    {
        Notification::make()
            ->title($message)
            ->danger()
            ->send();
    }

    /**
     * Dispatch the Turnstile reset event and clear the response so the widget can be re-solved.
     */
    protected function dispatchTurnstileReset(): void
    {
        if (! Jetstream::plugin()->usesTurnstile()) {
            return;
        }

        $eventName = config('filament-team-guard.turnstile.reset_event', 'filament-team-guard-turnstile-reset');
        $this->dispatch($eventName);

        $this->turnstileResponse = null;
    }
}
