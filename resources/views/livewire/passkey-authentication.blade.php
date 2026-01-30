<div>
    <x-filament::section :aside="$aside">
        <x-slot name="heading">
            {{ __('filament-team-guard::two_factor.section.passkey.header') }}
        </x-slot>

        <x-slot name="description">
            {{ __('filament-team-guard::two_factor.section.passkey.description') }}
        </x-slot>

        <div class="fi-sc-form space-y-6">
            {{ $this->createPasskeyForm }}

            {{ $this->table }}
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</div>

@includeWhen(class_exists(\Spatie\LaravelPasskeys\Livewire\PasskeysComponent::class), 'passkeys::livewire.partials.createScript')

