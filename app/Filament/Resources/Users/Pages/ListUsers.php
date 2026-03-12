<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Services\UserSpreadsheetImporter;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadImportTemplate')
                ->label(__('Download Import Template'))
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn (): bool => UserResource::canViewAny())
                ->url(route('users.import-template.download')),
            Actions\Action::make('importUsers')
                ->label(__('Import Users'))
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn (): bool => UserResource::canCreate())
                ->form([
                    FileUpload::make('file')
                        ->label(__('Spreadsheet File'))
                        ->disk('local')
                        ->directory('imports/users')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->rules(['mimes:csv,txt,xlsx'])
                        ->required()
                        ->helperText(__('Upload a CSV or XLSX file only. The template contains name, email, phone, and national_id only. New users are imported as members, and the default password is their phone number.')),
                ])
                ->action(function (array $data): void {
                    $file = Arr::first(Arr::wrap($data['file'] ?? null));

                    if (! is_string($file) || blank($file)) {
                        return;
                    }

                    try {
                        $summary = app(UserSpreadsheetImporter::class)->import(
                            Storage::disk('local')->path($file),
                        );

                        Storage::disk('local')->delete($file);

                        $notification = Notification::make()
                            ->body($this->buildImportSummary($summary));

                        if ($summary['errors'] === []) {
                            $notification->title(__('Import completed'));
                            $notification->success();
                        } elseif (($summary['created'] + $summary['updated']) > 0) {
                            $notification->title(__('Import completed with issues'));
                            $notification->warning()->persistent();
                        } else {
                            $notification->title(__('Import failed'));
                            $notification->danger()->persistent();
                        }

                        $notification->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        if (filled($file)) {
                            Storage::disk('local')->delete($file);
                        }

                        Notification::make()
                            ->title(__('Import failed'))
                            ->body($exception->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
            Actions\CreateAction::make()
                ->visible(fn (): bool => UserResource::canCreate()),
        ];
    }

    /**
     * @param  array{
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     errors: array<int, string>
     * }  $summary
     */
    private function buildImportSummary(array $summary): string
    {
        $lines = [
            __('Created: :created, Updated: :updated, Skipped: :skipped', [
                'created' => $summary['created'],
                'updated' => $summary['updated'],
                'skipped' => $summary['skipped'],
            ]),
        ];

        $errors = array_values($summary['errors']);

        if ($errors === []) {
            return implode(PHP_EOL, $lines);
        }

        $lines[] = __('Import errors:');

        foreach (array_slice($errors, 0, 5) as $error) {
            $lines[] = '- ' . $error;
        }

        $remainingErrors = count($errors) - 5;

        if ($remainingErrors > 0) {
            $lines[] = __('And :count more errors.', ['count' => $remainingErrors]);
        }

        return implode(PHP_EOL, $lines);
    }
}
