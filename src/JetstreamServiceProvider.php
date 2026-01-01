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
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class JetstreamServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-jetstream';

    public static string $viewNamespace = 'filament-jetstream';

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
        ], 'filament-jetstream-migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/2025_08_22_134103_create_teams_table.php' => database_path('migrations/2025_08_22_134103_create_teams_table.php'),
        ], 'filament-jetstream-team-migrations');

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
        ], 'filament-jetstream-actions');

        // Publish email templates
        $this->publishes([
            __DIR__ . '/../resources/views/emails/team-invitation.blade.php' => resource_path('views/vendor/filament-jetstream/emails/team-invitation.blade.php'),
        ], 'filament-jetstream-email-templates');

        // Publish language files for customization
        // Publish to lang/{locale}/filament-jetstream.php for better locale organization
        $langFiles = [];
        $langPath = __DIR__ . '/../resources/lang';

        if (is_dir($langPath)) {
            foreach (glob($langPath . '/*', GLOB_ONLYDIR) as $localeDir) {
                $locale = basename($localeDir);
                $defaultFile = $localeDir . '/default.php';

                if (file_exists($defaultFile)) {
                    $langFiles[$defaultFile] = $this->app->langPath("{$locale}/filament-jetstream.php");
                }
            }
        }

        $this->publishes($langFiles, 'filament-jetstream-lang');
    }

    public function packageRegistered(): void
    {
        $this->registerContracts();
        $this->registerCustomTranslationPaths();
    }

    public function packageBooted()
    {
        $this->registerLivewireComponents();
    }

    /**
     * Register custom translation paths to support lang/{locale}/filament-jetstream.php structure.
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
                    // Intercept filament-jetstream::default namespace
                    if ($namespace === 'filament-jetstream' && $group === 'default') {
                        // Always load package translations first
                        $packageTranslations = $this->originalLoader->load($locale, $group, $namespace);

                        $customPath = $this->app->langPath("{$locale}/filament-jetstream.php");

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
         */
        Livewire::component('filament-jetstream::pages.edit-profile', EditProfile::class);
        Livewire::component(
            'filament-jetstream::livewire.profile.update-profile-information',
            UpdateProfileInformation::class
        );
        Livewire::component('filament-jetstream::livewire.profile.update-password', UpdatePassword::class);
        Livewire::component(
            'filament-jetstream::livewire.profile.logout-other-browser-sessions',
            LogoutOtherBrowserSessions::class
        );
        Livewire::component('filament-jetstream::livewire.profile.delete-account', DeleteAccount::class);

        /*
         * Api Token Components
         */
        Livewire::component('filament-jetstream::pages.api-tokens', ApiTokens::class);
        Livewire::component('filament-jetstream::livewire.api-tokens.create-api-token', CreateApiToken::class);
        Livewire::component('filament-jetstream::livewire.api-tokens.manage-api-tokens', ManageApiTokens::class);

        /*
         * Teams Components
         */
        Livewire::component('filament-jetstream::pages.edit-teams', EditTeam::class);
        Livewire::component('filament-jetstream::livewire.teams.update-team-name', UpdateTeamNameComponent::class);
        Livewire::component('filament-jetstream::livewire.teams.add-team-member', AddTeamMemberComponent::class);
        Livewire::component('filament-jetstream::livewire.teams.team-members', TeamMembers::class);
        Livewire::component(
            'filament-jetstream::livewire.teams.pending-team-invitations',
            PendingTeamInvitations::class
        );
        Livewire::component('filament-jetstream::livewire.teams.delete-team', DeleteTeamComponent::class);
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
            $this->app->singleton(AcceptsTeamInvitations::class, \App\Actions\FilamentJetstream\AcceptTeamInvitation::class);
        } else {
            $this->app->singleton(AcceptsTeamInvitations::class, AcceptTeamInvitation::class);
        }
    }
}
