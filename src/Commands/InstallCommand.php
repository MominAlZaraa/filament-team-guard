<?php

namespace Filament\Jetstream\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class InstallCommand extends Command
{
    public $signature = 'filament-team-guard:install {--teams : Indicates if team support should be installed}
                                              {--api : Indicates if API support should be installed}';

    public $description = 'Install the Laravel Jetstream and Filament Panel components.';

    /**
     * The console command description.
     */
    public function handle(): int
    {
        // Add Filament Default Panel to Service Provider...
        (new Filesystem)->ensureDirectoryExists(app_path('Providers/Filament'));
        ServiceProvider::addProviderToBootstrapFile('App\Providers\Filament\AppPanelProvider');

        if (file_exists(resource_path('views/welcome.blade.php'))) {
            $this->replaceInFile(
                "Route::has('login')",
                'filament()->hasLogin()',
                resource_path('views/welcome.blade.php')
            );

            $this->replaceInFile(
                "{{ route('login') }}",
                '{{ filament()->getLoginUrl() }}',
                resource_path('views/welcome.blade.php')
            );

            $this->replaceInFile(
                "Route::has('register')",
                'filament()->hasRegistration()',
                resource_path('views/welcome.blade.php')
            );

            $this->replaceInFile(
                "{{ route('register') }}",
                '{{ filament()->getRegistrationUrl() }}',
                resource_path('views/welcome.blade.php')
            );

            $this->replaceInFile(
                "{{ url('/dashboard') }}",
                '{{ filament()->getHomeUrl() }}',
                resource_path('views/welcome.blade.php')
            );
        }

        // Factories...
        copy(__DIR__ . '/../../database/factories/UserFactory.php', base_path('database/factories/UserFactory.php'));

        // User Model...
        copy(__DIR__ . '/../../stubs/app/Models/User.php', app_path('Models/User.php'));

        // Default Filament Panel...
        copy(
            __DIR__ . '/../../stubs/app/Providers/AppPanelProvider.php',
            app_path('Providers/Filament/AppPanelProvider.php')
        );

        // Setup Team
        if ($this->option('teams')) {
            $this->call('vendor:publish', ['--tag' => 'filament-team-guard-team-migrations']);

            // Factories
            copy(__DIR__ . '/../../database/factories/TeamFactory.php', base_path('database/factories/TeamFactory.php'));

            $this->replaceInFile(
                '// use Filament\Models\Contracts\HasTenants;',
                'use Filament\Models\Contracts\HasTenants;',
                app_path('Models/User.php')
            );

            $this->replaceInFile(
                '// use Filament\Jetstream\InteractsWithTeams',
                'use Filament\Jetstream\InteractsWithTeams',
                app_path('Models/User.php')
            );

            $this->replaceInFile(
                ', MustVerifyEmail',
                ', MustVerifyEmail, HasTenants',
                app_path('Models/User.php')
            );

            $this->replaceInFile(
                '// use InteractsWithTeams;',
                'use InteractsWithTeams;',
                app_path('Models/User.php')
            );

            // Add Teams features to Filament Panel
            $this->replaceInFile(
                '->twoFactorAuthentication()',
                '->twoFactorAuthentication()
                 ->teams()
                 ',
                app_path('Providers/Filament/AppPanelProvider.php')
            );
        }

        // API Tokens
        if ($this->option('api')) {
            // Add HasApiTokens trait to User Model...
            $this->replaceInFile(
                '// use Laravel\Sanctum\HasApiTokens;',
                'use Laravel\Sanctum\HasApiTokens;',
                app_path('Models/User.php')
            );

            $this->replaceInFile(
                '// use HasApiTokens;',
                'use HasApiTokens;',
                app_path('Models/User.php')
            );

            // Add API token feature to Filament Panel...
            $this->replaceInFile(
                '->twoFactorAuthentication()',
                '->twoFactorAuthentication()
                 ->apiTokens()
                 ',
                app_path('Providers/Filament/AppPanelProvider.php')
            );

            $this->call('install:api', ['--without-migration-prompt' => true]);
        }

        // Publish filament assets
        $this->call('filament:install', ['--scaffold' => true, '--notifications' => true]);

        // Publish passkey migrations
        $this->call('vendor:publish', ['--tag' => 'passkeys-migrations']);

        // Publish jetstream migrations
        $this->call('vendor:publish', ['--tag' => 'filament-team-guard-migrations']);

        // Publish Action stubs for customization
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/FilamentJetstream'));
        $this->call('vendor:publish', ['--tag' => 'filament-team-guard-actions']);

        // Publish email templates for customization
        (new Filesystem)->ensureDirectoryExists(resource_path('views/vendor/filament-team-guard/emails'));
        $this->call('vendor:publish', ['--tag' => 'filament-team-guard-email-templates']);

        // Publish language files for customization
        // Ensure locale directories exist (e.g., lang/en, lang/fr, etc.)
        $langPath = $this->laravel->langPath();
        $defaultLocale = $this->laravel->getLocale();
        (new Filesystem)->ensureDirectoryExists($langPath . '/' . $defaultLocale);
        $this->call('vendor:publish', ['--tag' => 'filament-team-guard-lang']);

        // Link local storage
        $this->call('storage:link');

        $this->info('DONE: Filament Jetstream starter kit installed successfully.');
        $this->info('Action classes have been published to app/Actions/FilamentJetstream/ for customization.');
        $this->info('Email templates have been published to resources/views/vendor/filament-team-guard/emails/ for customization.');
        $this->info('Language files have been published to lang/vendor/filament-team-guard/ for customization.');

        return self::SUCCESS;
    }

    /**
     * Replace a given string within a given file.
     *
     * @param  string  $replace
     * @param  string|array  $search
     * @param  string  $path
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
}
