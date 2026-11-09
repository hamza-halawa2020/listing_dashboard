<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <div class="mt-6 flex items-center gap-3">
            <x-filament::button type="submit" size="lg" icon="heroicon-o-check">
                {{ __('Create Subscription') }}
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Resources\Subscriptions\SubscriptionResource::getUrl('index') }}"
                color="gray"
                size="lg"
                icon="heroicon-o-x-mark"
            >
                {{ __('Cancel') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
