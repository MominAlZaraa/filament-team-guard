<?php

namespace Filament\Jetstream\Pages\Auth\EmailVerification;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;
use Filament\Jetstream\Jetstream;
use Filament\Jetstream\Turnstile\ValidatesTurnstile;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class EmailVerificationPrompt extends BaseEmailVerificationPrompt
{
    use ValidatesTurnstile;

    public ?string $turnstileResponse = null;

    public function resendNotificationAction(): Action
    {
        return Action::make('resendNotification')
            ->link()
            ->label(__('filament-panels::auth/pages/email-verification/email-verification-prompt.actions.resend_notification.label') . '.')
            ->size('sm')
            ->action(function (): void {
                $this->validateTurnstile();

                try {
                    $this->rateLimit(2);
                } catch (TooManyRequestsException $exception) {
                    $this->getRateLimitedNotification($exception)?->send();

                    return;
                }

                $this->sendEmailVerificationNotification($this->getVerifiable());

                Notification::make()
                    ->title(__('filament-panels::auth/pages/email-verification/email-verification-prompt.notifications.notification_resent.title'))
                    ->success()
                    ->send();
            });
    }

    protected function onValidationError(ValidationException $exception): void
    {
        $this->dispatchTurnstileReset();

        parent::onValidationError($exception);
    }

    public function content(Schema $schema): Schema
    {
        $user = $this->getVerifiable();

        $components = [
            Text::make(__('filament-panels::auth/pages/email-verification/email-verification-prompt.messages.notification_sent', [
                'email' => $user->getEmailForVerification(),
            ])),
            Text::make(new HtmlString(
                __('filament-panels::auth/pages/email-verification/email-verification-prompt.messages.notification_not_received') .
                    ' ' .
                    $this->resendNotificationAction->toHtml(),
            )),
        ];

        if (Jetstream::plugin()->usesTurnstile() && config('turnstile.enabled', true) && config('turnstile.sitekey')) {
            $components[] = View::make('filament-team-guard::auth.turnstile');
        }

        return $schema->components($components);
    }
}
