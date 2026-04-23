<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Filament\Resources\Visits\VisitResource;
use App\Models\Visit;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListVisits extends ListRecords
{
    protected static string $resource = VisitResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All')),

            Visit::STATUS_PENDING => Tab::make(__('Pending'))
                ->badge(fn () => Visit::where('status', Visit::STATUS_PENDING)->count() ?: null)
                ->badgeColor('warning'),

            Visit::STATUS_APPROVED => Tab::make(__('Approved'))
                ->badge(fn () => Visit::where('status', Visit::STATUS_APPROVED)->count() ?: null)
                ->badgeColor('success'),

            Visit::STATUS_REJECTED => Tab::make(__('Rejected'))
                ->badge(fn () => Visit::where('status', Visit::STATUS_REJECTED)->count() ?: null)
                ->badgeColor('danger'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = Visit::query()->with(['user', 'listing', 'attachments']);

        $tab = $this->activeTab ?? 'all';

        if (in_array($tab, [Visit::STATUS_PENDING, Visit::STATUS_APPROVED, Visit::STATUS_REJECTED])) {
            $query->where('status', $tab);
        }

        return $query;
    }
}
