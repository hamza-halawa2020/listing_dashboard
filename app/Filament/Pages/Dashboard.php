<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ContentPulseChart;
use App\Filament\Widgets\PaymentsStatusChart;
use App\Filament\Widgets\SystemGrowthChart;
use App\Filament\Widgets\SystemOverviewStats;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function mount(): void
    {
        $today = now()->toDateString();
        $fromDate = data_get($this->filters, 'from_date');
        $toDate = data_get($this->filters, 'to_date');

        $this->filters = [
            'from_date' => filled($fromDate) ? $fromDate : $today,
            'to_date' => filled($toDate) ? $toDate : $today,
        ];

        $this->getFiltersForm()->fill($this->filters);
    }

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('dashboard.filters.title'))
                    ->description(__('dashboard.filters.description'))
                    ->schema([
                        DatePicker::make('from_date')
                            ->label(__('dashboard.filters.from'))
                            ->default(now()->toDateString())
                            ->locale(app()->getLocale()),
                        DatePicker::make('to_date')
                            ->label(__('dashboard.filters.to'))
                            ->default(now()->toDateString())
                            ->locale(app()->getLocale()),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            SystemOverviewStats::class,
            SystemGrowthChart::class,
            PaymentsStatusChart::class,
            // ContentPulseChart::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }
}
