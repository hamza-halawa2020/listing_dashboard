<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top: 20px;">
            <x-filament::button type="submit" size="lg">
                {{ __('Save') }}
            </x-filament::button>
        </div>
    </form>

    <div style="margin-top: 32px;">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
