<?php

namespace Filament\Jetstream;

use Filament\Jetstream\Actions\AcceptTeamInvitation;
use Filament\Jetstream\Actions\AddTeamMember;
use Filament\Jetstream\Actions\CreateTeam;
use Filament\Jetstream\Actions\DeleteTeam;
use Filament\Jetstream\Actions\DeleteUser;
use Filament\Jetstream\Actions\InviteTeamMember;
use Filament\Jetstream\Actions\RemoveTeamMember;
use Filament\Jetstream\Actions\UpdateTeamName;
use Filament\Jetstream\Actions\UpdateUserProfileInformation;
use Filament\Jetstream\Commands\InstallCommand;
use Filament\Jetstream\Contracts\AcceptsTeamInvitations;
use Filament\Jetstream\Contracts\AddsTeamMembers;
use Filament\Jetstream\Contracts\CreatesTeams;
use Filament\Jetstream\Contracts\DeletesTeams;
use Filament\Jetstream\Contracts\DeletesUsers;
use Filament\Jetstream\Contracts\InvitesTeamMembers;
use Filament\Jetstream\Contracts\RemovesTeamMembers;
use Filament\Jetstream\Contracts\UpdatesTeamNames;
use Filament\Jetstream\Contracts\UpdatesUserProfileInformation as UpdatesUserProfileInformationContract;
use Filament\Jetstream\Livewire\ApiTokens\CreateApiToken;
use Filament\Jetstream\Livewire\ApiTokens\ManageApiTokens;
use Filament\Jetstream\Livewire\Profile\DeleteAccount;
use Filament\Jetstream\Livewire\Profile\LogoutOtherBrowserSessions;
use Filament\Jetstream\Livewire\Profile\UpdatePassword;
use Filament\Jetstream\Livewire\Profile\UpdateProfileInformation;
use Filament\Jetstream\Livewire\Teams\AddTeamMember as AddTeamMemberComponent;
use Filament\Jetstream\Livewire\Teams\DeleteTeam as DeleteTeamComponent;
use Filament\Jetstream\Livewire\Teams\PendingTeamInvitations;
use Filament\Jetstream\Livewire\Teams\TeamMembers;
use Filament\Jetstream\Livewire\Teams\UpdateTeamName as UpdateTeamNameComponent;
use Filament\Jetstream\Pages\ApiTokens;
use Filament\Jetstream\Pages\EditProfile;
use Filament\Jetstream\Pages\EditTeam;
use Filament\Jetstream\TwoFactor\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Filament\Jetstream\TwoFactor\Livewire\PasskeyAuthentication as JetstreamPasskeyAuthentication;
use Filament\Jetstream\TwoFactor\Livewire\TwoFactorAuthentication as JetstreamTwoFactorAuthentication;
use Filament\Jetstream\TwoFactor\Pages\Challenge as TwoFactorChallengePage;
use Filament\Jetstream\TwoFactor\Pages\Recovery as TwoFactorRecoveryPage;
use Filament\Jetstream\TwoFactor\Pages\Setup as TwoFactorSetupPage;
use Filament\Jetstream\TwoFactor\TwoFactorAuthenticationProvider as JetstreamTwoFactorAuthenticationProvider;
use Illuminate\Contracts\Cache\Repository;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class JetstreamServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-team-guard';

    public static string $viewNamespace = 'filament-team-guard';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasViews()
            ->hasTranslations()
            ->hasConfigFile(static::$name)
            ->hasCommands([InstallCommand::class]);

        $this->publishes([
            __DIR__ . '/../database/migrations/2025_08_22_134103_add_profile_photo_column_to_users_table.php' => database_path('migrations/2025_08_22_134103_add_profile_photo_column_to_users_table.php'),
        ], 'filament-team-guard-migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/2025_08_22_134103_create_teams_table.php' => database_path('migrations/2025_08_22_134103_create_teams_table.php'),
        ], 'filament-team-guard-team-migrations');

        // Publish Action stubs
        $this->publishes([
            __DIR__ . '/../stubs/app/Actions/FilamentJetstream/UpdateUserProfileInformation.php' => app_path('Actions/FilamentJetstream/UpdateUserProfileInformation.php'),
            __DIR__ . '/../stubs/app/Actions/FilamentJetstream/InviteTeamMember.php' => app_path('Actions/FilamentJetstream/InviteTeamMember.php'),
            __DIR__ . '/../stubs/app/Actions/FilamentJetstream/AddTeamMember.php' => app_path('Actions/FilamentJetstream/AddTeamMember.php'),
            __DIR__ . '/../stubs/app/Actions/FilamentJetstream/RemoveTeamMember.php' => app_path('Actions/FilamentJetstream/RemoveTeamMember.php'),
            __DIR__ . '/../stubs/app/Actions/FilamentJetstream/CreateTeam.php' => app_path('Actions/FilamentJetstream/CreateTeam.php'),
            __DIR__ . '/../stubs/app/Actions/FilamentJetstream/UpdateTeamName.php' => app_path('Actions/FilamentJetstream/UpdateTeamName.php'),
            __DIR__ . '/../stubs/app/Actions/FilamentJetstream/DeleteTeam.php' => app_path('Actions/FilamentJetstream/DeleteTeam.php'),
            __DIR__ . '/../stubs/app/Actions/FilamentJetstream/DeleteUser.php' => app_path('Actions/FilamentJetstream/DeleteUser.php'),
            __DIR__ . '/../stubs/app/Actions/FilamentJetstream/AcceptTeamInvitation.php' => app_path('Actions/FilamentJetstream/AcceptTeamInvitation.php'),
        ], 'filament-team-guard-actions');

        // Publish email templates
        $this->publishes([
            __DIR__ . '/../resources/views/emails/team-invitation.blade.php' => resource_path('views/vendor/filament-team-guard/emails/team-invitation.blade.php'),
        ], 'filament-team-guard-email-templates');

        // Publish language files for customization
        // Publish to lang/{locale}/filament-team-guard.php for better locale organization
        $langFiles = [];
        $langPath = __DIR__ . '/../resources/lang';

        if (is_dir($langPath)) {
            foreach (glob($langPath . '/*', GLOB_ONLYDIR) as $localeDir) {
                $locale = basename($localeDir);
                $defaultFile = $localeDir . '/default.php';

                if (file_exists($defaultFile)) {
                    $langFiles[$defaultFile] = $this->app->langPath("{$locale}/filament-team-guard.php");
                }
            }
        }

        $this->publishes($langFiles, 'filament-team-guard-lang');
    }

    public function packageRegistered(): void
    {
        $this->registerContracts();
        $this->registerTwoFactorProvider();
        $this->registerCustomTranslationPaths();
    }

    public function packageBooted(): void
    {
        // Register Livewire components so aliases are available (Livewire v4).
        // Runs in packageBooted to ensure Livewire Finder is ready.
        $this->registerLivewireComponents();
    }

    /**
     * Register custom translation paths to support lang/{locale}/filament-team-guard.php structure.
     * This extends the translation loader service to merge custom translations.
     */
    protected function registerCustomTranslationPaths(): void
    {
        // Extend the translation.loader service (FileLoader) instead of Translator
        // Must be registered in packageRegistered() to ensure it runs before service resolution
        $this->app->extend('translation.loader', function ($loader, $app) {
            return new class($loader, $app) implements \Illuminate\Contracts\Translation\Loader
            {
                public function __construct(
                    protected \Illuminate\Contracts\Translation\Loader $originalLoader,
                    protected \Illuminate\Contracts\Foundation\Application $app
                ) {}

                public function load($locale, $group, $namespace = null)
                {
                    // Intercept filament-team-guard::default namespace
                    if ($namespace === 'filament-team-guard' && $group === 'default') {
                        // Always load package translations first
                        $packageTranslations = $this->originalLoader->load($locale, $group, $namespace);

                        $customPath = $this->app->langPath("{$locale}/filament-team-guard.php");

                        // If custom file exists, merge with package translations
                        if (file_exists($customPath) && is_readable($customPath)) {
                            try {
                                $customTranslations = require $customPath;

                                if (is_array($customTranslations)) {
                                    // Merge: custom overrides package
                                    // First merge to ensure all keys are included, then replace to override
                                    $merged = array_merge_recursive($packageTranslations ?? [], $customTranslations);

                                    return array_replace_recursive($merged, $customTranslations);
                                }
                            } catch (\Throwable $e) {
                                // If custom file fails, fall back to package translations
                                // Log in development only
                                if ($this->app->environment(['local', 'testing'])) {
                                    \Log::debug('Filament Jetstream: Error loading custom translations', [
                                        'path' => $customPath,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }
                        }

                        // Return package translations if no custom file or if custom file failed
                        return $packageTranslations ?? [];
                    }

                    // For all other cases, use original loader
                    return $this->originalLoader->load($locale, $group, $namespace);
                }

                public function addNamespace($namespace, $hint)
                {
                    return $this->originalLoader->addNamespace($namespace, $hint);
                }

                public function addJsonPath($path)
                {
                    return $this->originalLoader->addJsonPath($path);
                }

                public function namespaces()
                {
                    return $this->originalLoader->namespaces();
                }
            };
        });
    }

    private function registerLivewireComponents(): void
    {
        /*
         * Profile Components
         * Use dot names (no ::) so Livewire v4 Finder checks classComponents for explicit aliases.
         */
        Livewire::component('filament-team-guard.pages.edit-profile', EditProfile::class);
        Livewire::component(
            'filament-team-guard.livewire.profile.update-profile-information',
            UpdateProfileInformation::class
        );
        Livewire::component('filament-team-guard.livewire.profile.update-password', UpdatePassword::class);
        Livewire::component(
            'filament-team-guard.livewire.profile.logout-other-browser-sessions',
            LogoutOtherBrowserSessions::class
        );
        Livewire::component('filament-team-guard.livewire.profile.delete-account', DeleteAccount::class);

        /*
         * Api Token Components
         */
        Livewire::component('filament-team-guard.pages.api-tokens', ApiTokens::class);
        Livewire::component('filament-team-guard.livewire.api-tokens.create-api-token', CreateApiToken::class);
        Livewire::component('filament-team-guard.livewire.api-tokens.manage-api-tokens', ManageApiTokens::class);

        /*
         * Teams Components
         */
        Livewire::component('filament-team-guard.pages.edit-teams', EditTeam::class);
        Livewire::component('filament-team-guard.livewire.teams.update-team-name', UpdateTeamNameComponent::class);
        Livewire::component('filament-team-guard.livewire.teams.add-team-member', AddTeamMemberComponent::class);
        Livewire::component('filament-team-guard.livewire.teams.team-members', TeamMembers::class);
        Livewire::component(
            'filament-team-guard.livewire.teams.pending-team-invitations',
            PendingTeamInvitations::class
        );
        Livewire::component('filament-team-guard.livewire.teams.delete-team', DeleteTeamComponent::class);

        /*
         * Two-factor Components
         */
        Livewire::component('filament-team-guard.pages.auth.challenge', TwoFactorChallengePage::class);
        Livewire::component('filament-team-guard.pages.auth.recovery', TwoFactorRecoveryPage::class);
        Livewire::component('filament-team-guard.pages.auth.setup', TwoFactorSetupPage::class);
        Livewire::component(
            'filament-team-guard.livewire.two-factor-authentication',
            JetstreamTwoFactorAuthentication::class
        );
        Livewire::component(
            'filament-team-guard.livewire.passkey-authentication',
            JetstreamPasskeyAuthentication::class
        );
    }

    /**
     * Register the contract bindings with their default implementations.
     */
    protected function registerContracts(): void
    {
        // Check if custom implementations exist, otherwise use defaults
        if (class_exists(\App\Actions\FilamentJetstream\UpdateUserProfileInformation::class)) {
            $this->app->singleton(UpdatesUserProfileInformationContract::class, \App\Actions\FilamentJetstream\UpdateUserProfileInformation::class);
        } else {
            $this->app->singleton(UpdatesUserProfileInformationContract::class, UpdateUserProfileInformation::class);
        }

        if (class_exists(\App\Actions\FilamentJetstream\InviteTeamMember::class)) {
            $this->app->singleton(InvitesTeamMembers::class, \App\Actions\FilamentJetstream\InviteTeamMember::class);
        } else {
            $this->app->singleton(InvitesTeamMembers::class, InviteTeamMember::class);
        }

        if (class_exists(\App\Actions\FilamentJetstream\AddTeamMember::class)) {
            $this->app->singleton(AddsTeamMembers::class, \App\Actions\FilamentJetstream\AddTeamMember::class);
        } else {
            $this->app->singleton(AddsTeamMembers::class, AddTeamMember::class);
        }

        if (class_exists(\App\Actions\FilamentJetstream\CreateTeam::class)) {
            $this->app->singleton(CreatesTeams::class, \App\Actions\FilamentJetstream\CreateTeam::class);
        } else {
            $this->app->singleton(CreatesTeams::class, CreateTeam::class);
        }

        if (class_exists(\App\Actions\FilamentJetstream\UpdateTeamName::class)) {
            $this->app->singleton(UpdatesTeamNames::class, \App\Actions\FilamentJetstream\UpdateTeamName::class);
        } else {
            $this->app->singleton(UpdatesTeamNames::class, UpdateTeamName::class);
        }

        if (class_exists(\App\Actions\FilamentJetstream\DeleteTeam::class)) {
            $this->app->singleton(DeletesTeams::class, \App\Actions\FilamentJetstream\DeleteTeam::class);
        } else {
            $this->app->singleton(DeletesTeams::class, DeleteTeam::class);
        }

        if (class_exists(\App\Actions\FilamentJetstream\DeleteUser::class)) {
            $this->app->singleton(DeletesUsers::class, \App\Actions\FilamentJetstream\DeleteUser::class);
        } else {
            $this->app->singleton(DeletesUsers::class, DeleteUser::class);
        }

        if (class_exists(\App\Actions\FilamentJetstream\RemoveTeamMember::class)) {
            $this->app->singleton(RemovesTeamMembers::class, \App\Actions\FilamentJetstream\RemoveTeamMember::class);
        } else {
            $this->app->singleton(RemovesTeamMembers::class, RemoveTeamMember::class);
        }

        if (class_exists(\App\Actions\FilamentJetstream\AcceptTeamInvitation::class)) {
            $this->app->singleton(
                AcceptsTeamInvitations::class,
                \App\Actions\FilamentJetstream\AcceptTeamInvitation::class
            );
        } else {
            $this->app->singleton(AcceptsTeamInvitations::class, AcceptTeamInvitation::class);
        }
    }

    protected function registerTwoFactorProvider(): void
    {
        $this->app->singleton(TwoFactorAuthenticationProviderContract::class, function ($app) {
            return new JetstreamTwoFactorAuthenticationProvider(
                $app->make(Google2FA::class),
                $app->make(Repository::class)
            );
        });
    }
}
