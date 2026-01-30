<x-filament-panels::page.simple>
    <x-slot name="subheading">
        {{ __('filament-team-guard::two_factor.pages.subheading') . ' ' }}
        {{ $this->recoveryAction }}
    </x-slot>

    <form id="form" wire:submit="authenticate" class="fi-sc-form space-y-6">
        {{ $this->form }}

        @include('filament-team-guard::auth.turnstile')

        <x-filament::actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
    </form>

</x-filament-panels::page.simple>
