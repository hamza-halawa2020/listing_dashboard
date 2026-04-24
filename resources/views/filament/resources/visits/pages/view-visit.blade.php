<x-filament-panels::page>
@php $points = \App\Models\Visit::getVisitPoints(); @endphp
<div class="space-y-5">

    {{-- TOP ROW --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">

        {{-- Status --}}
        <div @class([
            'sm:col-span-2 flex items-start gap-3 rounded-xl border p-4',
            'bg-warning-50 border-warning-200 dark:bg-warning-500/10 dark:border-warning-500/20' => $visit->status === 'pending',
            'bg-success-50 border-success-200 dark:bg-success-500/10 dark:border-success-500/20' => $visit->status === 'approved',
            'bg-danger-50  border-danger-200  dark:bg-danger-500/10  dark:border-danger-500/20'  => $visit->status === 'rejected',
        ])>
            <div @class([
                'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                'bg-warning-100 dark:bg-warning-500/20' => $visit->status === 'pending',
                'bg-success-100 dark:bg-success-500/20' => $visit->status === 'approved',
                'bg-danger-100  dark:bg-danger-500/20'  => $visit->status === 'rejected',
            ])>
                @if ($visit->status === 'approved')
                    <x-heroicon-m-check-circle class="h-4 w-4 text-success-600 dark:text-success-400" />
                @elseif ($visit->status === 'rejected')
                    <x-heroicon-m-x-circle class="h-4 w-4 text-danger-600 dark:text-danger-400" />
                @else
                    <x-heroicon-m-clock class="h-4 w-4 text-warning-600 dark:text-warning-400" />
                @endif
            </div>
            <div class="min-w-0">
                <p @class([
                    'text-xs font-bold uppercase tracking-wide',
                    'text-warning-700 dark:text-warning-400' => $visit->status === 'pending',
                    'text-success-700 dark:text-success-400' => $visit->status === 'approved',
                    'text-danger-700  dark:text-danger-400'  => $visit->status === 'rejected',
                ])>
                    {{ match($visit->status) { 'approved' => __('Approved'), 'rejected' => __('Rejected'), default => __('Pending Review') } }}
                </p>
                <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
                    @if ($visit->status === 'approved')
                        {{ __('By') }} <span class="font-semibold">{{ $visit->approvedByAdmin?->name }}</span>
                        &middot; {{ $visit->approved_at?->diffForHumans() }}
                    @elseif ($visit->status === 'rejected')
                        {{ $visit->rejection_reason }}
                    @else
                        {{ __('Waiting for admin review. Points will be added upon approval.') }}
                    @endif
                </p>
            </div>
        </div>

        {{-- Points --}}
        <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div @class([
                'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                'bg-success-50 dark:bg-success-500/10' => $visit->status === 'approved',
                'bg-gray-100   dark:bg-white/5'        => $visit->status !== 'approved',
            ])>
                <x-heroicon-m-currency-dollar @class([
                    'h-4 w-4',
                    'text-success-600 dark:text-success-400' => $visit->status === 'approved',
                    'text-gray-400'                          => $visit->status !== 'approved',
                ]) />
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Points') }}</p>
                <p @class([
                    'text-xl font-black leading-tight',
                    'text-success-600 dark:text-success-400' => $visit->status === 'approved',
                    'text-gray-900 dark:text-white'          => $visit->status !== 'approved',
                ])>
                    {{ $visit->status === 'approved' ? '+' : '' }}{{ $points }}
                    <span class="text-xs font-normal text-gray-400">pts</span>
                </p>
                <p class="text-xs text-gray-400">
                    @if ($visit->status === 'approved') {{ __('Granted') }}
                    @elseif ($visit->status === 'rejected') {{ __('Not granted') }}
                    @else {{ __('Pending') }}
                    @endif
                </p>
            </div>
        </div>

        {{-- Files count --}}
        <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                <x-heroicon-m-paper-clip class="h-4 w-4 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Files') }}</p>
                <p class="text-xl font-black leading-tight text-gray-900 dark:text-white">{{ $visit->attachments->count() }}</p>
                <p class="text-xs text-gray-400">{{ __('uploaded') }}</p>
            </div>
        </div>

    </div>

    {{-- MIDDLE ROW --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        {{-- User --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">{{ __('User') }}</p>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-xs font-black text-white shadow">
                    {{ mb_strtoupper(mb_substr($visit->user?->name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ $visit->user?->name }}</p>
                    <p class="text-xs text-gray-500">{{ $visit->user?->phone }}</p>
                    @if ($visit->user?->email)
                        <p class="truncate text-xs text-gray-400">{{ $visit->user->email }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Listing --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">{{ __('Listing') }}</p>
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info-50 dark:bg-info-500/10">
                    <x-heroicon-m-building-office-2 class="h-4 w-4 text-info-600 dark:text-info-400" />
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $visit->listing?->name }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $visit->listing?->address }}</p>
                </div>
            </div>
        </div>

        {{-- Date --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">{{ __('Visit Date') }}</p>
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                    <x-heroicon-m-calendar-days class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                    <span>{{ $visit->visited_at?->format('d M Y - H:i') }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <x-heroicon-m-clock class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                    <span>{{ __('Submitted') }} {{ $visit->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Notes --}}
    @if ($visit->notes)
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="mb-2 flex items-center gap-2">
                <x-heroicon-m-chat-bubble-left-ellipsis class="h-3.5 w-3.5 text-gray-400" />
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">{{ __('Notes') }}</p>
            </div>
            <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $visit->notes }}</p>
        </div>
    @endif

    {{-- ATTACHMENTS --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">

        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-white/10">
            <div class="flex items-center gap-2">
                <x-heroicon-m-paper-clip class="h-3.5 w-3.5 text-gray-400" />
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('Attachments') }}</h3>
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                    {{ $visit->attachments->count() }}
                </span>
            </div>
            @if ($visit->attachments->isNotEmpty())
                <p class="text-xs text-gray-400">{{ __('Click to open') }}</p>
            @endif
        </div>

        @if ($visit->attachments->isEmpty())
            <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                <x-heroicon-o-inbox class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                <p class="text-sm text-gray-400">{{ __('No attachments') }}</p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                @foreach ($visit->attachments as $attachment)
                    @php $isImage = str_contains($attachment->mime_type ?? '', 'image'); @endphp
                    <a href="{{ asset('files/' . $attachment->file_path) }}"
                       target="_blank"
                       class="group relative flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-gray-50 transition hover:border-primary-400 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div class="relative h-24 w-full overflow-hidden bg-gray-100 dark:bg-white/5">
                            @if ($isImage)
                                <img src="{{ asset('files/' . $attachment->file_path) }}"
                                     alt="{{ $attachment->file_name }}"
                                     class="h-full w-full object-cover transition group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <x-heroicon-o-document-text class="h-8 w-8 text-gray-400" />
                                </div>
                            @endif
                            <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition group-hover:opacity-100">
                                <x-heroicon-m-arrow-top-right-on-square class="h-5 w-5 text-white" />
                            </div>
                        </div>
                        <div class="px-2 py-1.5">
                            <p class="truncate text-xs font-medium text-gray-600 dark:text-gray-400">{{ $attachment->file_name }}</p>
                            <p class="text-xs text-gray-400">{{ strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>

</div>
</x-filament-panels::page>
