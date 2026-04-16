@php
    $currentRate = \App\Models\PointSetting::getCurrentRate();
    $changesCount = \App\Models\PointRateHistory::count();
    $lastChange = \App\Models\PointRateHistory::latest()->first();
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-primary-600 via-primary-500 to-info-500 p-6 text-white shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-sm font-medium text-white/80">{{ __('point-settings.history.hero_eyebrow') }}</p>
                    <h2 class="mt-2 text-2xl font-bold">{{ __('point-settings.history.hero_title') }}</h2>
                    <p class="mt-3 text-sm leading-6 text-white/85">
                        {{ __('point-settings.history.hero_description') }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white/15 px-5 py-4 backdrop-blur">
                    <p class="text-sm text-white/80">{{ __('point-settings.history.current_rate_card') }}</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($currentRate, 4) }}</p>
                    <p class="mt-1 text-sm text-white/80">{{ __('point-settings.history.current_rate_suffix') }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('point-settings.history.cards.current_rate.title') }}</p>
                <p class="mt-3 text-2xl font-semibold text-primary-600 dark:text-primary-400">
                    {{ number_format($currentRate, 4) }} {{ __('point-settings.history.cards.current_rate.suffix') }}
                </p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('point-settings.history.cards.current_rate.description', ['points' => number_format(\App\Models\PointSetting::calculatePointsNeeded(100))]) }}
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('point-settings.history.cards.total_changes.title') }}</p>
                <p class="mt-3 text-2xl font-semibold text-success-600 dark:text-success-400">
                    {{ number_format($changesCount) }}
                </p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('point-settings.history.cards.total_changes.description') }}
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('point-settings.history.cards.last_change.title') }}</p>
                <p class="mt-3 text-2xl font-semibold text-warning-600 dark:text-warning-400">
                    {{ $lastChange ? $lastChange->created_at->diffForHumans() : __('point-settings.history.cards.last_change.none') }}
                </p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ $lastChange ? __('point-settings.history.cards.last_change.reason', ['reason' => $lastChange->reason ?: __('point-settings.history.table.undefined')]) : __('point-settings.history.cards.last_change.empty') }}
                </p>
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-5 dark:border-white/10">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">{{ __('point-settings.history.timeline_title') }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('point-settings.history.timeline_description') }}
                </p>
            </div>

            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
