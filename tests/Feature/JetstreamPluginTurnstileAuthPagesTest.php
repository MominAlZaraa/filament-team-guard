<?php

namespace Filament\Jetstream\Tests\Feature;

use Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Jetstream\JetstreamPlugin;
use Filament\Jetstream\Pages\Auth\EmailVerification\EmailVerificationPrompt as TurnstileEmailVerificationPrompt;
use Filament\Jetstream\Pages\Auth\Login as TurnstileLogin;
use Filament\Jetstream\Pages\Auth\PasswordReset\RequestPasswordReset as TurnstileRequestPasswordReset;
use Filament\Jetstream\Pages\Auth\PasswordReset\ResetPassword as TurnstileResetPassword;
use Filament\Jetstream\Pages\Auth\Register as TurnstileRegister;
use Filament\Jetstream\Tests\TestCase;
use Filament\Panel;

class JetstreamPluginTurnstileAuthPagesTest extends TestCase
{
    public function test_it_swaps_filament_default_auth_pages_when_turnstile_is_enabled(): void
    {
        $panel = (new Panel)
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification();

        JetstreamPlugin::make()
            ->turnstile()
            ->register($panel);

        $this->assertSame(TurnstileLogin::class, $panel->getLoginRouteAction());
        $this->assertSame(TurnstileRegister::class, $panel->getRegistrationRouteAction());
        $this->assertSame(TurnstileRequestPasswordReset::class, $panel->getRequestPasswordResetRouteAction());
        $this->assertSame(TurnstileResetPassword::class, $panel->getResetPasswordRouteAction());
        $this->assertSame(TurnstileEmailVerificationPrompt::class, $panel->getEmailVerificationPromptRouteAction());
    }

    public function test_it_does_not_override_custom_auth_pages_when_turnstile_is_enabled(): void
    {
        $panel = (new Panel)
            ->login(CustomLoginPage::class)
            ->registration(CustomRegisterPage::class)
            ->passwordReset(CustomRequestPasswordResetPage::class, CustomResetPasswordPage::class)
            ->emailVerification(CustomEmailVerificationPromptPage::class);

        JetstreamPlugin::make()
            ->turnstile()
            ->register($panel);

        $this->assertSame(CustomLoginPage::class, $panel->getLoginRouteAction());
        $this->assertSame(CustomRegisterPage::class, $panel->getRegistrationRouteAction());
        $this->assertSame(CustomRequestPasswordResetPage::class, $panel->getRequestPasswordResetRouteAction());
        $this->assertSame(CustomResetPasswordPage::class, $panel->getResetPasswordRouteAction());
        $this->assertSame(CustomEmailVerificationPromptPage::class, $panel->getEmailVerificationPromptRouteAction());
    }
}

class CustomLoginPage extends BaseLogin {}
class CustomRegisterPage extends BaseRegister {}
class CustomRequestPasswordResetPage extends BaseRequestPasswordReset {}
class CustomResetPasswordPage extends BaseResetPassword {}
class CustomEmailVerificationPromptPage extends BaseEmailVerificationPrompt {}
