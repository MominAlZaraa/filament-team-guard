@use('Filament\Jetstream\Jetstream')

<x-filament-panels::page>
    @if (Jetstream::plugin()?->enabledProfileInformationUpdate())
        @livewire('filament-team-guard.livewire.profile.update-profile-information')
    @endif

    @if (Jetstream::plugin()?->enabledPasswordUpdate())
        @livewire('filament-team-guard.livewire.profile.update-password')
    @endif

    @if (Jetstream::plugin()?->enabledTwoFactorAuthetication())
        @livewire('filament-team-guard.livewire.two-factor-authentication')
    @endif

    @if (Jetstream::plugin()?->enabledPasskeyAuthetication())
        @livewire('filament-team-guard.livewire.passkey-authentication')
    @endif

    @if (Jetstream::plugin()?->enabledLogoutOtherBrowserSessions())
        @livewire('filament-team-guard.livewire.profile.logout-other-browser-sessions')
    @endif

    @if (Jetstream::plugin()?->enabledDeleteAccount())
        @livewire('filament-team-guard.livewire.profile.delete-account')
    @endif
</x-filament-panels::page>
