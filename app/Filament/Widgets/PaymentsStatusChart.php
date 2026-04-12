<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Support\DashboardDateRange;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Support\Htmlable;

class PaymentsStatusChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected int | string | array $columnSpan = 1;

    public function getHeading(): string | Htmlable | null
    {
        return __('dashboard.payments.heading');
    }

    public function getDescription(): ?string
    {
        $completedRevenue = DashboardDateRange::apply(
            Payment::query()->where('status', 'completed'),
            $this->pageFilters,
        )->sum('amount');

        return __('dashboard.payments.description', [
            'range' => DashboardDateRange::describe($this->pageFilters),
            'amount' => number_format((float) $completedRevenue, 2),
        ]);
    }

    protected function getData(): array
    {
        $statuses = ['pending', 'completed', 'failed', 'refunded'];

        $totals = DashboardDateRange::apply(Payment::query(), $this->pageFilters)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.payments.dataset'),
                    'data' => array_map(
                        static fn (string $status): int => (int) ($totals[$status] ?? 0),
                        $statuses,
                    ),
                    'backgroundColor' => [
                        '#f59e0b',
                        '#16a34a',
                        '#dc2626',
                        '#2563eb',
                    ],
                ],
            ],
            'labels' => array_map(
                [$this, 'paymentStatusLabel'],
                $statuses,
            ),
        ];
    }

    protected function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => __('dashboard.payments.statuses.pending'),
            'completed' => __('dashboard.payments.statuses.completed'),
            'failed' => __('dashboard.payments.statuses.failed'),
            'refunded' => __('dashboard.payments.statuses.refunded'),
            default => $status,
        };
    }

    protected function getOptions(): ?array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
