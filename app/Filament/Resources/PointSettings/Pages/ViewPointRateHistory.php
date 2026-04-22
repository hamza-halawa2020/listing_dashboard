<?php

namespace App\Filament\Resources\PointSettings\Pages;

use App\Filament\Resources\PointSettings\PointSettingResource;
use App\Models\PointRateHistory;
use App\Models\RegistrationRewardHistory;
use App\Models\SubscriptionRewardHistory;
use App\Models\SubscriptionPlan;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;

class ViewPointRateHistory extends Page
{
    protected static string $resource = PointSettingResource::class;

    public int $perPage = 15;

    // Livewire pagination uses this property name by default
    public int $page = 1;

    public function getView(): string
    {
        return 'filament.resources.point-settings.pages.view-point-rate-history';
    }

    public function getTitle(): string
    {
        return __('point-settings.history.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_settings')
                ->label(__('point-settings.history.back'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => PointSettingResource::getUrl()),
        ];
    }

    // Called by <x-filament::pagination> via wire:click="previousPage('page')"
    public function previousPage(string $pageName = 'page'): void
    {
        $this->page = max(1, $this->page - 1);
    }

    // Called by <x-filament::pagination> via wire:click="nextPage('page')"
    public function nextPage(string $pageName = 'page'): void
    {
        $total = $this->getPaginator()->lastPage();
        $this->page = min($total, $this->page + 1);
    }

    // Called by <x-filament::pagination> via wire:click="gotoPage(N, 'page')"
    public function gotoPage(int $page, string $pageName = 'page'): void
    {
        $this->page = max(1, $page);
    }

    private function getAllChanges(): \Illuminate\Support\Collection
    {
        $rateChanges = PointRateHistory::query()
            ->with('changedByAdmin')
            ->latest()
            ->get()
            ->map(fn ($h) => (object) [
                'type'       => 'rate',
                'label'      => __('point-settings.history.type_rate'),
                'old_value'  => number_format((float) $h->old_rate, 4),
                'new_value'  => number_format((float) $h->new_rate, 4),
                'suffix'     => __('point-settings.history.table.rate_suffix'),
                'direction'  => $h->new_rate > $h->old_rate ? 'up' : ($h->new_rate < $h->old_rate ? 'down' : 'same'),
                'pct'        => $h->old_rate > 0 ? round((($h->new_rate - $h->old_rate) / $h->old_rate) * 100, 2) : 0,
                'reason'     => $h->reason,
                'changed_by' => $h->changedByAdmin?->name ?? __('point-settings.history.table.system'),
                'created_at' => $h->created_at,
                'extra'      => null,
            ]);

        $registrationChanges = RegistrationRewardHistory::query()
            ->with('changedByAdmin')
            ->latest()
            ->get()
            ->map(fn ($h) => (object) [
                'type'       => 'reward',
                'label'      => __('point-settings.history.type_reward'),
                'old_value'  => number_format((int) $h->old_points),
                'new_value'  => number_format((int) $h->new_points),
                'suffix'     => __('point-settings.units.points'),
                'direction'  => $h->new_points > $h->old_points ? 'up' : ($h->new_points < $h->old_points ? 'down' : 'same'),
                'pct'        => $h->old_points > 0 ? round((($h->new_points - $h->old_points) / $h->old_points) * 100, 2) : 0,
                'reason'     => $h->reason,
                'changed_by' => $h->changedByAdmin?->name ?? __('point-settings.history.table.system'),
                'created_at' => $h->created_at,
                'extra'      => null,
            ]);

        $subscriptionChanges = SubscriptionRewardHistory::query()
            ->with('changedByAdmin', 'subscriptionPlan')
            ->latest()
            ->get()
            ->map(fn ($h) => (object) [
                'type'       => 'subscription',
                'label'      => __('point-settings.history.type_subscription'),
                'old_value'  => number_format((int) $h->old_points),
                'new_value'  => number_format((int) $h->new_points),
                'suffix'     => __('point-settings.units.points'),
                'direction'  => $h->new_points > $h->old_points ? 'up' : ($h->new_points < $h->old_points ? 'down' : 'same'),
                'pct'        => $h->old_points > 0 ? round((($h->new_points - $h->old_points) / $h->old_points) * 100, 2) : 0,
                'reason'     => $h->reason,
                'changed_by' => $h->changedByAdmin?->name ?? __('point-settings.history.table.system'),
                'created_at' => $h->created_at,
                'extra'      => $h->subscriptionPlan?->name,
            ]);

        return $rateChanges
            ->concat($registrationChanges)
            ->concat($subscriptionChanges)
            ->sortByDesc('created_at')
            ->values();
    }

    private function getPaginator(): LengthAwarePaginator
    {
        $all = $this->getAllChanges();

        return new LengthAwarePaginator(
            items:       $all->forPage($this->page, $this->perPage),
            total:       $all->count(),
            perPage:     $this->perPage,
            currentPage: $this->page,
            options:     ['pageName' => 'page'],
        );
    }

    public function getViewData(): array
    {
        $all       = $this->getAllChanges();
        $paginator = $this->getPaginator();

        return [
            'currentRate'  => \App\Models\PointSetting::getCurrentRate(),
            'pointsFor100' => number_format(\App\Models\PointSetting::calculatePointsNeeded(100)),
            'totalCount'   => $all->count(),
            'lastChange'   => $all->first(),
            'paginator'    => $paginator,
        ];
    }
}
