<div>
    <x-filament::section :aside="$aside">
        <x-slot name="heading">
            {{ __('filament-team-guard::two_factor.section.header') }}
        </x-slot>

        <x-slot name="description">
            {{ __('filament-team-guard::two_factor.section.description') }}
        </x-slot>

        <div class="fi-sc-form space-y-6">
            {{ $this->setupTwoFactorAuthenticationForm }}

            {{ $this->enableTwoFactorAuthenticationForm }}

            {{ $this->disableTwoFactorAuthenticationForm }}
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</div>

