<x-filament::section aside>
    <x-slot name="heading">
        {{ __('filament-team-guard::default.pending_team_invitations.section.title') }}
    </x-slot>
    <x-slot name="description">
        {{ __('filament-team-guard::default.pending_team_invitations.section.description') }}
    </x-slot>

    {{ $this->table }}

    <x-filament-actions::modals/>

</x-filament::section>
