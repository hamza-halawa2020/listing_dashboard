<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Services\SubscriptionSpreadsheetImporter;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadImportTemplate')
                ->label(__('Download Import Template'))
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn (): bool => SubscriptionResource::canViewAny())
                ->url(route('subscriptions.import-template.download')),
            Actions\Action::make('importSubscriptions')
                ->label(__('Import Subscriptions'))
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn (): bool => SubscriptionResource::canViewAny())
                ->form([
                    FileUpload::make('file')
                        ->label(__('Spreadsheet File'))
                        ->disk('local')
                        ->directory('imports/subscriptions')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->rules(['mimes:csv,txt,xlsx'])
                        ->required()
                        ->helperText(__('Upload a CSV or XLSX file only. The template contains user_phone, subscription_plan_code, membership_card_number, starts_at, and ends_at. status is filled automatically.')),
                ])
                ->action(function (array $data): void {
                    $file = Arr::first(Arr::wrap($data['file'] ?? null));

                    if (! is_string($file) || blank($file)) {
                        return;
                    }

                    try {
                        $summary = app(SubscriptionSpreadsheetImporter::class)->import(
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
                ->visible(fn (): bool => SubscriptionResource::canCreate()),
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
