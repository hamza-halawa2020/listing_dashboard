<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DashboardDateRange
{
    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function resolve(?array $filters): array
    {
        $today = now()->toDateString();
        $fromDate = data_get($filters, 'from_date');
        $toDate = data_get($filters, 'to_date');

        $start = Carbon::parse(filled($fromDate) ? $fromDate : $today)->startOfDay();
        $end = Carbon::parse(filled($toDate) ? $toDate : $today)->endOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [
                $end->copy()->startOfDay(),
                $start->copy()->endOfDay(),
            ];
        }

        return [$start, $end];
    }

    public static function apply(Builder $query, ?array $filters, string $column = 'created_at'): Builder
    {
        [$start, $end] = static::resolve($filters);

        return $query->whereBetween($column, [$start, $end]);
    }

    /**
     * @return array<int, string>
     */
    public static function labels(?array $filters): array
    {
        [$start, $end] = static::resolve($filters);

        $cursor = $start->copy()->startOfDay();
        $lastDay = $end->copy()->startOfDay();
        $labels = [];

        while ($cursor->lte($lastDay)) {
            $labels[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        return $labels;
    }

    /**
     * @param  array<int, string>  $labels
     * @return array<int, int>
     */
    public static function dailyCounts(Builder $query, ?array $filters, array $labels = []): array
    {
        $labels = $labels ?: static::labels($filters);

        $totals = static::apply($query, $filters)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->all();

        return array_map(
            static fn (string $label): int => (int) ($totals[$label] ?? 0),
            $labels,
        );
    }

    public static function describe(?array $filters): string
    {
        [$start, $end] = static::resolve($filters);

        if ($start->isSameDay($end)) {
            return $start->format('Y-m-d');
        }

        return $start->format('Y-m-d') . ' ' . __('dashboard.range.separator') . ' ' . $end->format('Y-m-d');
    }
}
