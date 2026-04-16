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

    public function getTitle(): string
    {
        return __('point-settings.history.title');
    }

    public function getSubheading(): ?string
    {
        return __('point-settings.history.subheading');
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

    protected function getTableQuery(): Builder
    {
        return PointRateHistory::with('changedByAdmin')->latest();
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('old_rate')
                    ->label(__('point-settings.history.table.old_rate'))
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 4) . ' ' . __('point-settings.history.table.rate_suffix'))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('new_rate')
                    ->label(__('point-settings.history.table.new_rate'))
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 4) . ' ' . __('point-settings.history.table.rate_suffix'))
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('change')
                    ->label(__('point-settings.history.table.change'))
                    ->state(function (PointRateHistory $record) {
                        if ($record->old_rate == 0) {
                            return 0;
                        }

                        return (($record->new_rate - $record->old_rate) / $record->old_rate) * 100;
                    })
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : ($state < 0 ? 'success' : 'warning'))
                    ->icon(fn ($state) => $state > 0 ? 'heroicon-m-arrow-trending-up' : ($state < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '') . number_format((float) $state, 2) . '%'),

                Tables\Columns\TextColumn::make('reason')
                    ->label(__('point-settings.history.table.reason'))
                    ->limit(60)
                    ->tooltip(fn (PointRateHistory $record): ?string => $record->reason)
                    ->placeholder(__('point-settings.history.table.undefined'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('changedByAdmin.name')
                    ->label(__('point-settings.history.table.changed_by'))
                    ->default(__('point-settings.history.table.system'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('point-settings.history.table.changed_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([])
            ->recordAction(null)
            ->recordUrl(null)
            ->defaultSort('created_at', 'desc');
    }
}
