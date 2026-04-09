<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\ListingImportRunResource\Pages\ListListingImportRuns;
use App\Models\ListingImportRun;
use BackedEnum;
use Illuminate\Contracts\View\View;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class ListingImportRunResource extends AuthorizedResource
{
    protected static ?string $model = ListingImportRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?int $navigationSort = 35;
    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return __('Listing import');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Listing imports');
    }

    public static function table(Table $table): Table
    {
        $statusOptions = [
            ListingImportRun::STATUS_PENDING => __('Pending'),
            ListingImportRun::STATUS_PROCESSING => __('Processing'),
            ListingImportRun::STATUS_COMPLETED => __('Completed'),
            ListingImportRun::STATUS_FAILED => __('Failed'),
        ];

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label(__('ID'))
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->default(__('System'))
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn (?string $state): ?string => $state ? ($statusOptions[$state] ?? $state) : null)
                    ->colors([
                        'primary' => ListingImportRun::STATUS_PENDING,
                        'warning' => ListingImportRun::STATUS_PROCESSING,
                        'success' => ListingImportRun::STATUS_COMPLETED,
                        'danger' => ListingImportRun::STATUS_FAILED,
                    ])
                    ->sortable(),
                TextColumn::make('path')
                    ->label(__('File'))
                    ->wrap()
                    ->copyable()
                    ->tooltip(fn (ListingImportRun $record): ?string => $record->path),
                TextColumn::make('summary_created')
                    ->label(__('Created'))
                    ->sortable(),
                TextColumn::make('summary_updated')
                    ->label(__('Updated'))
                    ->sortable(),
                TextColumn::make('summary_skipped')
                    ->label(__('Skipped'))
                    ->sortable(),
                TextColumn::make('summary_errors')
                    ->label(__('Errors'))
                    ->formatStateUsing(fn (?array $errors): string => $errors ? __(':count errors', ['count' => count($errors)]) : __('0 errors')),
                TextColumn::make('failure_message')
                    ->label(__('Failure message'))
                    ->wrap()
                    ->limit(40),
                TextColumn::make('started_at')
                    ->label(__('Started at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->label(__('Finished at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options($statusOptions),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('View'))
                    ->modalHeading(__('Import run details'))
                    ->modalContent(fn (ListingImportRun $record): View => view(
                        'filament.listing-import-runs.view-run',
                        compact('record'),
                    )),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListingImportRuns::route('/'),
        ];
    }
}
