<?php

namespace App\Filament\Widgets;

use App\Models\Contact;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Support\DashboardDateRange;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemOverviewStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected int | string | array $columnSpan = 'full';

    protected function getHeading(): ?string
    {
        return __('dashboard.overview.heading');
    }

    protected function getDescription(): ?string
    {
        return __('dashboard.overview.description', [
            'range' => DashboardDateRange::describe($this->pageFilters),
        ]);
    }

    protected function getStats(): array
    {
        $filters = $this->pageFilters;

        $completedPaymentsQuery = DashboardDateRange::apply(
            Payment::query()->where('status', 'completed'),
            $filters,
        );

        return [
            Stat::make(__('dashboard.overview.new_users'), DashboardDateRange::apply(User::query(), $filters)->count())
                ->color('primary'),
            // Stat::make(
            //     __('dashboard.overview.service_providers'),
            //     DashboardDateRange::apply(
            //         User::query()->where('role', 'service_provider'),
            //         $filters,
            //     )->count(),
            // )->color('info'),
            Stat::make(__('dashboard.overview.new_listings'), DashboardDateRange::apply(Listing::query(), $filters)->count())
                ->color('success'),
            Stat::make(
                __('dashboard.overview.active_offers'),
                DashboardDateRange::apply(
                    Offer::query()->where('is_active', true),
                    $filters,
                )->count(),
            )->color('warning'),
            Stat::make(__('dashboard.overview.new_subscriptions'), DashboardDateRange::apply(Subscription::query(), $filters)->count())
                ->color('primary'),
            Stat::make(__('dashboard.overview.completed_payments'), (clone $completedPaymentsQuery)->count())
                ->color('success'),
            Stat::make(
                __('dashboard.overview.completed_revenue'),
                number_format((float) ((clone $completedPaymentsQuery)->sum('amount')), 2) . ' EGP',
            )->color('success'),
            Stat::make(__('dashboard.overview.contact_messages'), DashboardDateRange::apply(Contact::query(), $filters)->count())
                ->color('gray'),
        ];
    }
}
