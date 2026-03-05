<?php

namespace Filament\Jetstream;

use Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Contracts\Plugin;
use Filament\Events\TenantSet;
use Filament\Jetstream\Concerns\HasApiTokensFeatures;
use Filament\Jetstream\Concerns\HasProfileFeatures;
use Filament\Jetstream\Concerns\HasTeamsFeatures;
use Filament\Jetstream\Listeners\SwitchTeam;
use Filament\Jetstream\Models\Team;
use Filament\Jetstream\Pages\ApiTokens;
use Filament\Jetstream\Pages\Auth\EmailVerification\EmailVerificationPrompt as TurnstileEmailVerificationPrompt;
use Filament\Jetstream\Pages\Auth\Login as TurnstileLogin;
use Filament\Jetstream\Pages\Auth\PasswordReset\RequestPasswordReset as TurnstileRequestPasswordReset;
use Filament\Jetstream\Pages\Auth\PasswordReset\ResetPassword as TurnstileResetPassword;
use Filament\Jetstream\Pages\Auth\Register as TurnstileRegister;
use Filament\Jetstream\Pages\CreateTeam;
use Filament\Jetstream\Pages\EditProfile;
use Filament\Jetstream\Pages\EditTeam;
use Filament\Jetstream\Policies\TeamPolicy;
use Filament\Jetstream\TwoFactor\TwoFactorAuthenticationPlugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;

class JetstreamPlugin implements Plugin
{
    use EvaluatesClosures;
    use HasApiTokensFeatures;
    use HasProfileFeatures;
    use HasTeamsFeatures;

    protected \Closure | bool $useTurnstile = false;

    public function getId(): string
    {
        return 'filament-team-guard';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        return filament(app(static::class)->getId());
    }

    /**
     * Enable Cloudflare Turnstile on auth forms that are explicitly enabled on the panel.
     * Turnstile is only injected where the panel already has those auth pages (e.g. login,
     * register, password reset, 2FA challenge/recovery). It does not enable login/register
     * or other auth routes automatically. Omit the argument or pass true to enable; pass false to disable.
     */
    public function turnstile(\Closure | bool $condition = true): static
    {
        $this->useTurnstile = $condition;

        return $this;
    }

    public function usesTurnstile(): bool
    {
        return $this->evaluate($this->useTurnstile) === true;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->homeUrl(fn () => str(filament()->getCurrentOrDefaultPanel()->getUrl())->append('/dashboard'))
            ->profile(EditProfile::class)
            ->plugins([
                TwoFactorAuthenticationPlugin::make()
                    ->enableTwoFactorAuthentication(condition: fn () => $this->enabledTwoFactorAuthetication())
                    ->enablePasskeyAuthentication(condition: fn () => $this->enabledPasskeyAuthetication())
                    ->forceTwoFactorSetup(
                        condition: fn () => $this->forceTwoFactorAuthetication(),
                        requiresPassword: $this->requiresPasswordForAuthenticationSetup()
                    ),
            ]);

        // When ->turnstile() is enabled, inject widget only on auth pages that are already enabled.
        // Do NOT enable login/register/password reset/email verification automatically—only add
        // the turnstile render hook so it applies when those pages are explicitly set by the app.
        if ($this->usesTurnstile()) {
            $this->swapDefaultAuthPagesWithTurnstilePages($panel);

            $panel
                ->renderHook(
                    PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                    fn (): string => view('filament-team-guard::auth.turnstile')->render()
                )
                ->renderHook(
                    PanelsRenderHook::AUTH_REGISTER_FORM_AFTER,
                    fn (): string => view('filament-team-guard::auth.turnstile')->render()
                )
                ->renderHook(
                    PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_AFTER,
                    fn (): string => view('filament-team-guard::auth.turnstile')->render()
                )
                ->renderHook(
                    PanelsRenderHook::AUTH_PASSWORD_RESET_RESET_FORM_AFTER,
                    fn (): string => view('filament-team-guard::auth.turnstile')->render()
                );
        }

        if ($this->hasApiTokensFeatures()) {
            $panel
                ->pages([ApiTokens::class])
                ->userMenuItems([$this->apiTokenMenuItem($panel)]);
        }

        if ($this->hasTeamsFeatures()) {
            $panel
                ->registration(TurnstileRegister::class)
                ->tenant($this->teamModel())
                ->tenantRegistration(CreateTeam::class)
                ->tenantProfile(EditTeam::class)
                ->routes(fn () => $this->teamsRoutes());
        }
    }

    public function boot(Panel $panel): void
    {
        /**
         * Listen and switch team if tenant was changed
         */
        Event::listen(TenantSet::class, SwitchTeam::class);

        /**
         * Register team policies
         */
        Gate::policy(Team::class, TeamPolicy::class);
    }

    protected function swapDefaultAuthPagesWithTurnstilePages(Panel $panel): void
    {
        $loginRouteAction = $panel->getLoginRouteAction();
        if (
            $panel->hasLogin() &&
            is_string($loginRouteAction) &&
            $loginRouteAction === BaseLogin::class
        ) {
            $panel->login(TurnstileLogin::class);
        }

        $registrationRouteAction = $panel->getRegistrationRouteAction();
        if (
            $panel->hasRegistration() &&
            is_string($registrationRouteAction) &&
            $registrationRouteAction === BaseRegister::class
        ) {
            $panel->registration(TurnstileRegister::class);
        }

        if ($panel->hasPasswordReset()) {
            $requestPasswordResetRouteAction = $panel->getRequestPasswordResetRouteAction();
            $resetPasswordRouteAction = $panel->getResetPasswordRouteAction();

            $panel->passwordReset(
                (
                    is_string($requestPasswordResetRouteAction) &&
                    $requestPasswordResetRouteAction === BaseRequestPasswordReset::class
                )
                    ? TurnstileRequestPasswordReset::class
                    : $requestPasswordResetRouteAction,
                (
                    is_string($resetPasswordRouteAction) &&
                    $resetPasswordRouteAction === BaseResetPassword::class
                )
                    ? TurnstileResetPassword::class
                    : $resetPasswordRouteAction,
            );
        }

        $emailVerificationRouteAction = $panel->getEmailVerificationPromptRouteAction();
        if (
            $panel->hasEmailVerification() &&
            is_string($emailVerificationRouteAction) &&
            $emailVerificationRouteAction === BaseEmailVerificationPrompt::class
        ) {
            $panel->emailVerification(TurnstileEmailVerificationPrompt::class, $panel->isEmailVerificationRequired());
        }
    }
}
