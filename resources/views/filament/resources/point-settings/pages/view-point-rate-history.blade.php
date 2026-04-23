<x-filament-panels::page>
<div class="space-y-6">

    {{-- STATS ROW --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 dark:bg-primary-500/10">
                <x-heroicon-o-currency-dollar class="h-5 w-5 text-primary-600 dark:text-primary-400" />
            </div>
            <div class="min-w-0">
                <p class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('point-settings.history.cards.current_rate.title') }}</p>
                <p class="mt-0.5 text-xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($currentRate, 4) }}
                    <span class="text-sm font-normal text-gray-400">{{ __('point-settings.history.table.rate_suffix') }}</span>
                </p>
                <p class="mt-0.5 text-xs text-gray-400">100 EGP {{ $pointsFor100 }} {{ __('point-settings.units.points') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-success-50 dark:bg-success-500/10">
                <x-heroicon-o-clock class="h-5 w-5 text-success-600 dark:text-success-400" />
            </div>
            <div class="min-w-0">
                <p class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('point-settings.history.cards.total_changes.title') }}</p>
                <p class="mt-0.5 text-xl font-bold text-gray-900 dark:text-white">{{ number_format($totalCount) }}</p>
                <p class="mt-0.5 text-xs text-gray-400">{{ __('point-settings.history.cards.total_changes.description') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-warning-50 dark:bg-warning-500/10">
                <x-heroicon-o-pencil-square class="h-5 w-5 text-warning-600 dark:text-warning-400" />
            </div>
            <div class="min-w-0">
                <p class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('point-settings.history.cards.last_change.title') }}</p>
                <p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $lastChange?->created_at?->diffForHumans() ?? __('point-settings.history.cards.last_change.none') }}
                </p>
                @if ($lastChange)
                    <p class="mt-0.5 truncate text-xs text-gray-400">{{ $lastChange->reason ?: __('point-settings.history.table.undefined') }}</p>
                @endif
            </div>
        </div>

    </div>

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">

        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-white/10">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('point-settings.history.timeline_title') }}</h3>
                <p class="mt-0.5 text-xs text-gray-400">{{ __('point-settings.history.timeline_description') }}</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                {{ $totalCount }}
            </span>
        </div>

        @if ($paginator->isEmpty())
            <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-white/5">
                    <x-heroicon-o-inbox class="h-7 w-7 text-gray-400" />
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('point-settings.history.cards.last_change.empty') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-white/10">
                            <th class="whitespace-nowrap px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('point-settings.history.table_col_type') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('point-settings.history.table.old_rate') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('point-settings.history.table.new_rate') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('point-settings.history.table.change') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('point-settings.history.table.details') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('point-settings.history.table.changed_by') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('point-settings.history.table.changed_at') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @foreach ($paginator as $row)
                            <tr class="group transition-colors hover:bg-gray-50/60 dark:hover:bg-white/[0.02]">

                                {{-- Type badge --}}
                                <td class="whitespace-nowrap px-6 py-3.5">
                                    @if ($row->type === 'rate')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                                            <x-heroicon-m-currency-dollar class="h-3 w-3" />{{ $row->label }}
                                        </span>
                                    @elseif ($row->type === 'reward')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 dark:bg-violet-500/10 dark:text-violet-400">
                                            <x-heroicon-m-gift class="h-3 w-3" />{{ $row->label }}
                                        </span>
                                    @elseif ($row->type === 'visit')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 dark:bg-teal-500/10 dark:text-teal-400">
                                            <x-heroicon-m-building-office-2 class="h-3 w-3" />{{ $row->label }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                            <x-heroicon-m-clipboard-document-list class="h-3 w-3" />{{ $row->label }}
                                        </span>
                                    @endif                                </td>

                                {{-- Old value --}}
                                <td class="whitespace-nowrap px-4 py-3.5 text-gray-500 dark:text-gray-400">
                                    {{ $row->old_value }} <span class="text-xs text-gray-400">{{ $row->suffix }}</span>
                                </td>

                                {{-- New value --}}
                                <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-gray-900 dark:text-white">
                                    {{ $row->new_value }} <span class="text-xs font-normal text-gray-400">{{ $row->suffix }}</span>
                                </td>

                                {{-- Change % --}}
                                <td class="whitespace-nowrap px-4 py-3.5">
                                    @if ($row->direction === 'up')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-danger-50 px-2 py-0.5 text-xs font-semibold text-danger-600 dark:bg-danger-500/10 dark:text-danger-400">
                                            <x-heroicon-m-arrow-trending-up class="h-3 w-3" />+{{ $row->pct }}%
                                        </span>
                                    @elseif ($row->direction === 'down')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-xs font-semibold text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                            <x-heroicon-m-arrow-trending-down class="h-3 w-3" />{{ $row->pct }}%
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-500 dark:bg-white/10 dark:text-gray-400">
                                            <x-heroicon-m-minus class="h-3 w-3" />0%
                                        </span>
                                    @endif
                                </td>

                                {{-- Details (plan name or reason) --}}
                                <td class="max-w-[200px] px-4 py-3.5 text-gray-500 dark:text-gray-400">
                                    @if ($row->extra)
                                        <div class="truncate font-medium text-gray-700 dark:text-gray-200" title="{{ $row->extra }}">
                                            {{ $row->extra }}
                                        </div>
                                    @endif
                                    @if ($row->reason)
                                        <div class="truncate text-xs text-gray-400" title="{{ $row->reason }}">{{ $row->reason }}</div>
                                    @else
                                        @if (!$row->extra) <span class="text-gray-300 dark:text-gray-600"></span> @endif
                                    @endif
                                </td>

                                {{-- Changed by --}}
                                <td class="whitespace-nowrap px-4 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                            {{ mb_substr($row->changed_by, 0, 1) }}
                                        </div>
                                        <span class="text-gray-700 dark:text-gray-300">{{ $row->changed_by }}</span>
                                    </div>
                                </td>

                                {{-- Date --}}
                                <td class="whitespace-nowrap px-4 py-3.5 text-gray-400" title="{{ $row->created_at?->format('Y-m-d H:i') }}">
                                    {{ $row->created_at?->diffForHumans() }}
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Filament native pagination --}}
            @if ($paginator->hasPages())
                <div class="border-t border-gray-100 px-6 py-4 dark:border-white/10">
                    <x-filament::pagination :paginator="$paginator" />
                </div>
            @endif

        @endif

    </div>

</div>
</x-filament-panels::page>
