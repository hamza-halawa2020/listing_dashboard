<?php

namespace App\Filament\Resources\ListingResource\Pages;

use App\Filament\Resources\ListingImportRunResource;
use App\Filament\Resources\ListingResource;
use App\Jobs\ProcessListingImport;
use App\Models\ListingImportRun;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;
use Illuminate\Support\Arr;
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
                ->url(route('listings.import-template.download')),
            Actions\Action::make('importHistory')
                ->label(__('Import History'))
                ->icon('heroicon-o-clipboard-document-list')
                ->visible(fn (): bool => ListingImportRunResource::canViewAny())
                ->url(ListingImportRunResource::getUrl('index')),
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
                        ->rules(['mimes:csv,txt,xlsx'])
                        ->required()
                        ->helperText(__('Upload a CSV or XLSX file only. Download the template first, keep the header names unchanged, and use category/location names exactly as they exist in the system.')),
                ])
                ->action(function (array $data): void {
                    $file = Arr::first(Arr::wrap($data['file'] ?? null));

                    if (! is_string($file) || blank($file)) {
                        return;
                    }

                    $run = ListingImportRun::create([
                        'user_id' => Filament::auth()?->id(),
                        'disk' => 'local',
                        'path' => $file,
                        'status' => ListingImportRun::STATUS_PENDING,
                    ]);

                    try {
                        ProcessListingImport::dispatch($run->id);

                        Notification::make()
                            ->title(__('Import queued'))
                            ->body(__('The spreadsheet was queued for background processing. Check the import logs after the job finishes.'))
                            ->info()
                            ->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title(__('Import failed'))
                            ->body($exception->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
            Actions\CreateAction::make()
                ->visible(fn (): bool => ListingResource::canCreate()),
        ];
    }

}
