@php use Filament\Jetstream\Jetstream; @endphp
<x-filament-panels::page>
    @if (Jetstream::plugin()?->hasApiTokensFeatures())
@livewire('filament-team-guard.livewire.api-tokens.create-api-token')
@livewire('filament-team-guard.livewire.api-tokens.manage-api-tokens')
    @endif
</x-filament-panels::page>
