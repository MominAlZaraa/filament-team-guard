<?php

namespace Filament\Jetstream\TwoFactor;

use Closure;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Jetstream\TwoFactor\Middleware\ForceTwoFactorSetup;
use Filament\Jetstream\TwoFactor\Middleware\TwoFactorChallenge;
use Filament\Jetstream\TwoFactor\Pages\Challenge;
use Filament\Jetstream\TwoFactor\Pages\Recovery;
use Filament\Jetstream\TwoFactor\Pages\Setup;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPasskeys\Events\PasskeyUsedToAuthenticateEvent;
use Spatie\LaravelPasskeys\Http\Controllers\AuthenticateUsingPasskeyController;
use Spatie\LaravelPasskeys\Http\Controllers\GeneratePasskeyAuthenticationOptionsController;

/**
 * Embedded two-factor authentication plugin for Filament Jetstream.
 *
 * This class is heavily based on Stephen Jude's
 * stephenjude/filament-two-factor-authentication plugin, adapted to live
 * inside this package with its own translations and configuration, with
 * full credit to the original author.
 */
class TwoFactorAuthenticationPlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool $enablePasskeyAuthentication = false;

    protected bool $enableTwoFactorAuthentication = false;

    protected bool $hasForcedTwoFactorSetup = false;

    protected bool $twoFactorSetupRequiresPassword = false;

    protected string $enforceTwoFactorSetupMiddleware = ForceTwoFactorSetup::class;

    protected string | bool $twoFactorChallengeMiddleware = TwoFactorChallenge::class;

    protected bool $hasTwoFactorMenuItem = false;

    protected ?string $twoFactorMenuItemLabel = 'filament-team-guard::two_factor.plugin.user_menu_item_label';

    protected ?string $twoFactorMenuItemIcon = 'heroicon-o-lock-closed';

    public function getId(): string
    {
        return 'filament-team-guard-two-factor-authentication';
    }

    public function register(Panel $panel): void
    {
        if (! $this->hasEnabledTwoFactorAuthentication() && ! $this->hasEnabledPasskeyAuthentication()) {
            return;
        }

        if ($this->hasEnabledPasskeyAuthentication() && class_exists(PasskeyUsedToAuthenticateEvent::class)) {
            $this->registerPasskeyAuthenticationHook($panel);
        }

        $panel
            ->routes(fn (): array => [
                Route::get('/two-factor-challenge', Challenge::class)->name('two-factor.challenge'),
                Route::get('/two-factor-recovery', Recovery::class)->name('two-factor.recovery'),
                Route::get('/two-factor-setup', Setup::class)
                    ->name('two-factor.setup')
                    ->middleware($this->getTwoFactorChallengeMiddleware()),
                Route::prefix('passkeys')
                    ->group(function (): void {
                        Route::get('authentication-options', GeneratePasskeyAuthenticationOptionsController::class)
                            ->name('passkeys.authentication_options');
                        Route::post('authenticate', AuthenticateUsingPasskeyController::class)
                            ->name('passkeys.login');
                    }),
            ])
            ->userMenuItems([
                Action::make('two-factor-settings')
                    ->visible($this->hasTwoFactorMenuItem())
                    ->url(fn (): string => $panel->route('two-factor.setup'))
                    ->label(fn () => __($this->getTwoFactorMenuItemLabel()))
                    ->icon(fn () => $this->getTwoFactorMenuItemIcon()),
            ])
            ->authMiddleware(
                array_filter([
                    $this->getTwoFactorChallengeMiddleware(),
                    $this->hasForcedTwoFactorSetup() ? $this->getForcedTwoFactorSetupMiddleware() : null,
                ])
            );
    }

    public function enableTwoFactorAuthentication(
        Closure | bool $condition = true,
        Closure | string $challengeMiddleware = TwoFactorChallenge::class,
    ): static {
        $this->enableTwoFactorAuthentication = $this->evaluate($condition);

        $this->twoFactorChallengeMiddleware = $this->evaluate($challengeMiddleware);

        return $this;
    }

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return $this->enableTwoFactorAuthentication;
    }

    public function hasEnabledPasskeyAuthentication(): bool
    {
        return $this->enablePasskeyAuthentication;
    }

    public function enablePasskeyAuthentication(Closure | bool $condition = true): static
    {
        $this->enablePasskeyAuthentication = $this->evaluate($condition);

        return $this;
    }

    public function forceTwoFactorSetup(
        Closure | bool $condition = true,
        Closure | bool $requiresPassword = true,
        Closure | string $middleware = ForceTwoFactorSetup::class,
    ): static {
        $this->hasForcedTwoFactorSetup = $this->evaluate($condition);

        $this->enforceTwoFactorSetupMiddleware = $this->evaluate($middleware);

        $this->twoFactorSetupRequiresPassword = $this->evaluate($requiresPassword);

        return $this;
    }

    public function twoFactorSetupRequiresPassword(): bool
    {
        return $this->twoFactorSetupRequiresPassword;
    }

    public function getTwoFactorChallengeMiddleware(): string
    {
        return $this->twoFactorChallengeMiddleware;
    }

    public function getForcedTwoFactorSetupMiddleware(): string
    {
        return $this->enforceTwoFactorSetupMiddleware;
    }

    public function hasForcedTwoFactorSetup(): bool
    {
        return $this->hasForcedTwoFactorSetup;
    }

    public function addTwoFactorMenuItem(
        Closure | bool $condition = true,
        Closure | string | null $label = null,
        Closure | string | null $icon = null,
    ): static {
        $this->hasTwoFactorMenuItem = $this->evaluate($condition);

        $this->twoFactorMenuItemLabel = $this->evaluate($label) ?? $this->twoFactorMenuItemLabel;

        $this->twoFactorMenuItemIcon = $this->evaluate($icon) ?? $this->twoFactorMenuItemIcon;

        return $this;
    }

    public function hasTwoFactorMenuItem(): bool
    {
        return $this->hasTwoFactorMenuItem;
    }

    public function getTwoFactorMenuItemLabel(): ?string
    {
        return $this->twoFactorMenuItemLabel;
    }

    public function getTwoFactorMenuItemIcon(): ?string
    {
        return $this->twoFactorMenuItemIcon;
    }

    public function registerPasskeyAuthenticationHook(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
            fn (): string => Blade::render('<x-filament-team-guard::passkey-login />'),
        );
    }

    public function boot(Panel $panel): void
    {
        if (! class_exists(PasskeyUsedToAuthenticateEvent::class)) {
            return;
        }

        Event::listen(function (PasskeyUsedToAuthenticateEvent $event): void {
            Cache::remember(
                "passkey::auth::{$event->passkey->authenticatable->id}",
                now()->addMinutes(3),
                fn () => true
            );
        });
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
