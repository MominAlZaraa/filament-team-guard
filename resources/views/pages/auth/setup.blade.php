@php
    use Filament\Jetstream\TwoFactor\TwoFactorAuthenticationPlugin;

    $plugin = TwoFactorAuthenticationPlugin::get();
@endphp

<x-filament-panels::page.simple>
    @if ($plugin->hasEnabledTwoFactorAuthentication())
        @livewire('filament-team-guard.livewire.two-factor-authentication', ['aside' => false])
    @endif

    @if ($plugin->hasEnabledPasskeyAuthentication())
        @livewire('filament-team-guard.livewire.passkey-authentication', ['aside' => false])
    @endif

    {{ $this->utilityActionsForm }}
</x-filament-panels::page.simple>

