<?php

namespace App\Filament\Widgets;

use App\Models\Contact;
use App\Models\Listing;
use App\Models\Subscription;
use App\Models\User;
use App\Support\DashboardDateRange;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Support\Htmlable;

class SystemGrowthChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected int | string | array $columnSpan = 1;

    public function getHeading(): string | Htmlable | null
    {
        return __('dashboard.growth.heading');
    }

    public function getDescription(): ?string
    {
        return __('dashboard.growth.description', [
            'range' => DashboardDateRange::describe($this->pageFilters),
        ]);
    }

    protected function getData(): array
    {
        $labels = DashboardDateRange::labels($this->pageFilters);

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.growth.users'),
                    'data' => DashboardDateRange::dailyCounts(User::query(), $this->pageFilters, $labels),
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'tension' => 0.35,
                ],
                [
                    'label' => __('dashboard.growth.listings'),
                    'data' => DashboardDateRange::dailyCounts(Listing::query(), $this->pageFilters, $labels),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.12)',
                    'tension' => 0.35,
                ],
                [
                    'label' => __('dashboard.growth.subscriptions'),
                    'data' => DashboardDateRange::dailyCounts(Subscription::query(), $this->pageFilters, $labels),
                    'borderColor' => '#d97706',
                    'backgroundColor' => 'rgba(217, 119, 6, 0.12)',
                    'tension' => 0.35,
                ],
                [
                    'label' => __('dashboard.growth.contacts'),
                    'data' => DashboardDateRange::dailyCounts(Contact::query(), $this->pageFilters, $labels),
                    'borderColor' => '#7c3aed',
                    'backgroundColor' => 'rgba(124, 58, 237, 0.12)',
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): ?array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
