<x-filament-panels::page>
    <x-filament-panels::header>
        <x-filament-panels::header.heading>
            Point Rate History
        </x-filament-panels::header.heading>
    </x-filament-panels::header>

    <x-filament-panels::content>
        <div class="space-y-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Current Rate</div>
                    <div class="text-2xl font-bold text-blue-600">
                        {{ number_format(\App\Models\PointSetting::getCurrentRate(), 4) }} EGP/point
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Total Changes</div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ \App\Models\PointRateHistory::count() }}
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Last Change</div>
                    <div class="text-lg font-semibold text-purple-600">
                        @php
                            $lastChange = \App\Models\PointRateHistory::latest()->first();
                        @endphp
                        @if($lastChange)
                            {{ $lastChange->created_at->diffForHumans() }}
                        @else
                            Never
                        @endif
                    </div>
                </div>
            </div>

            <!-- History Table -->
            {{ $this->table }}
        </div>
    </x-filament-panels::content>
</x-filament-panels::page>
