<x-filament::card>
    <div class="space-y-6 text-sm">
        <div>
            <div class="text-xs font-semibold text-gray-500">
                {{ __('Summary') }}
            </div>
            <div class="mt-2 grid grid-cols-2 gap-4 text-base font-medium">
                <div>{{ __('Created') }}: {{ $record->summary_created }}</div>
                <div>{{ __('Updated') }}: {{ $record->summary_updated }}</div>
                <div>{{ __('Skipped') }}: {{ $record->summary_skipped }}</div>
                <div>{{ __('Errors') }}: {{ count($record->summary_errors ?? []) }}</div>
            </div>
        </div>

        @if (filled($record->failure_message))
            <div>
                <div class="text-xs font-semibold text-gray-500">
                    {{ __('Failure message') }}
                </div>
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ $record->failure_message }}
                </p>
            </div>
        @endif

        @if ($record->summary_errors)
            <div>
                <div class="text-xs font-semibold text-gray-500">
                    {{ __('Errors detail') }}
                </div>
                <ul class="mt-2 space-y-1 text-xs text-gray-700 dark:text-gray-200 list-disc pl-5">
                    @foreach(array_slice($record->summary_errors, 0, 10) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                @if (count($record->summary_errors) > 10)
                    <p class="mt-2 text-xs text-gray-500">
                        {{ __('And :count more errors.', ['count' => count($record->summary_errors) - 10]) }}
                    </p>
                @endif
            </div>
        @endif
    </div>
</x-filament::card>
