<?php

namespace App\Filament\Resources\ListingResource\Pages;

use App\Filament\Resources\ListingResource;
use App\Services\ListingSpreadsheetImporter;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ListListings extends ListRecords
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadImportTemplate')
                ->label(__('Download Import Template'))
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn (): bool => ListingResource::canViewAny())
                ->url(asset('templates/listings-import-template.csv'), shouldOpenInNewTab: true),
            Actions\Action::make('importListings')
                ->label(__('Import Listings'))
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn (): bool => ListingResource::canCreate())
                ->form([
                    FileUpload::make('file')
                        ->label(__('Spreadsheet File'))
                        ->disk('local')
                        ->directory('imports/listings')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->required()
                        ->helperText(__('Upload a CSV or XLSX file. Listings are matched by name, category by category name, and location by governorate + area. is_active is set automatically.')),
                ])
                ->action(function (array $data): void {
                    $file = $data['file'] ?? null;

                    if (blank($file)) {
                        return;
                    }

                    try {
                        $summary = app(ListingSpreadsheetImporter::class)->import(
                            Storage::disk('local')->path($file),
                        );

                        Storage::disk('local')->delete($file);

                        $notification = Notification::make()
                            ->title(__('Import completed'))
                            ->body(
                                __('Created: :created, Updated: :updated, Skipped: :skipped', [
                                    'created' => $summary['created'],
                                    'updated' => $summary['updated'],
                                    'skipped' => $summary['skipped'],
                                ]) .
                                (
                                    $summary['errors'] !== []
                                        ? PHP_EOL . __('Errors: :count', ['count' => count($summary['errors'])])
                                        : ''
                                )
                            );

                        if ($summary['errors'] === []) {
                            $notification->success();
                        } else {
                            $notification->warning();
                        }

                        $notification->send();
                    } catch (Throwable $exception) {
                        if (filled($file)) {
                            Storage::disk('local')->delete($file);
                        }

                        Notification::make()
                            ->title(__('Import failed'))
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\CreateAction::make()
                ->visible(fn (): bool => ListingResource::canCreate()),
        ];
    }
}
