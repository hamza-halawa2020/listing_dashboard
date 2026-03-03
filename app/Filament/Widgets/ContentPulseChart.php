<?php

namespace App\Filament\Widgets;

use App\Models\FamilyMember;
use App\Models\Post;
use App\Models\Review;
use App\Support\DashboardDateRange;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Support\Htmlable;

class ContentPulseChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string | Htmlable | null
    {
        return __('dashboard.content.heading');
    }

    public function getDescription(): ?string
    {
        return __('dashboard.content.description', [
            'range' => DashboardDateRange::describe($this->pageFilters),
        ]);
    }

    protected function getData(): array
    {
        $labels = DashboardDateRange::labels($this->pageFilters);

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.content.published_posts'),
                    'data' => DashboardDateRange::dailyCounts(
                        Post::query()->where('status', true),
                        $this->pageFilters,
                        $labels,
                    ),
                    'backgroundColor' => '#2563eb',
                ],
                [
                    'label' => __('dashboard.content.approved_reviews'),
                    'data' => DashboardDateRange::dailyCounts(
                        Review::query()->where('status', true),
                        $this->pageFilters,
                        $labels,
                    ),
                    'backgroundColor' => '#16a34a',
                ],
                [
                    'label' => __('dashboard.content.family_members'),
                    'data' => DashboardDateRange::dailyCounts(
                        FamilyMember::query(),
                        $this->pageFilters,
                        $labels,
                    ),
                    'backgroundColor' => '#d97706',
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
        return 'bar';
    }
}
