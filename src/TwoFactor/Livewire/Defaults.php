<?php

namespace Filament\Jetstream\TwoFactor\Livewire;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Exception;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

trait Defaults
{
    use InteractsWithActions;
    use InteractsWithForms;
    use WithRateLimiting;

    public function getUser(): User
    {
        $user = Filament::auth()->user();

        if (! $user instanceof Model) {
            throw new Exception(
                __('filament-team-guard::two_factor.components.base.wrong_user')
            );
        }

        return $user;
    }

    protected function sendRateLimitedNotification(TooManyRequestsException $exception): void
    {
        Notification::make()
            ->title(__('filament-team-guard::two_factor.components.base.rate_limit_exceeded'))
            ->body(
                __('filament-team-guard::two_factor.components.base.try_again', [
                    'seconds' => $exception->secondsUntilAvailable,
                ])
            )
            ->danger()
            ->send();
    }
}
