<?php

namespace App\Filament\Resources\PointSettings\Pages;

use App\Filament\Resources\PointSettings\PointSettingResource;
use App\Models\PointRateHistory;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViewPointRateHistory extends ListRecords
{
    protected static string $resource = PointSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_settings')
                ->label(__('Back to Settings'))
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => PointSettingResource::getUrl()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return PointRateHistory::with('changedByAdmin')->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('old_rate')
                ->label(__('Old Rate'))
                ->formatStateUsing(fn ($state) => number_format($state, 4) . ' EGP/point'),

            Tables\Columns\TextColumn::make('new_rate')
                ->label(__('New Rate'))
                ->formatStateUsing(fn ($state) => number_format($state, 4) . ' EGP/point'),

            Tables\Columns\TextColumn::make('change_percentage')
                ->label(__('Change %'))
                ->formatStateUsing(function ($record) {
                    if ($record->old_rate == 0) return 'N/A';
                    $change = (($record->new_rate - $record->old_rate) / $record->old_rate) * 100;
                    $color = $change > 0 ? 'danger' : ($change < 0 ? 'success' : 'warning');
                    $icon = $change > 0 ? 'trending-up' : ($change < 0 ? 'trending-down' : 'minus');
                    return "{$icon} " . number_format($change, 2) . '%';
                }),

            Tables\Columns\TextColumn::make('reason')
                ->label(__('Reason'))
                ->limit(50)
                ->searchable(),

            Tables\Columns\TextColumn::make('changedByAdmin.name')
                ->label(__('Changed By'))
                ->default(__('System'))
                ->searchable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label(__('Date'))
                ->dateTime()
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [];
    }

    protected function getTableActions(): array
    {
        return [];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }
}
