<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

abstract class BaseExporter extends Exporter
{
    /**
     * Columns that should be excluded from auto-generation
     * (e.g. password, remember_token, attachment paths).
     */
    protected static array $excludedColumns = [
        'password',
        'remember_token',
        'email_verified_at',
        'referred_by_user_id',
    ];

    /**
     * Relationship columns to append after the model's own columns.
     * e.g. ['user.name', 'subscription.membership_card_number']
     */
    protected static array $relationshipColumns = [];

    /**
     * Custom labels for specific columns.
     * e.g. ['phone' => 'Phone Number', 'national_id' => 'National ID']
     */
    protected static array $columnLabels = [];

    public static function getColumns(): array
    {
        $model = static::getModel();
        $instance = new $model();
        $fillable = $instance->getFillable();

        $columns = [];

        foreach ($fillable as $field) {
            if (in_array($field, static::$excludedColumns, true)) {
                continue;
            }

            $rawLabel = static::$columnLabels[$field]
                ?? (string) str($field)->replace(['_', '-'], ' ')->ucfirst();

            // Pass through translation so the column header respects the current locale
            $label = __($rawLabel);

            $columns[] = ExportColumn::make($field)->label($label);
        }

        foreach (static::$relationshipColumns as $relColumn) {
            $rawLabel = static::$columnLabels[$relColumn]
                ?? (string) str($relColumn)->beforeLast('.')->afterLast('.')->replace(['_', '-'], ' ')->ucfirst()
                    . ' ' . str($relColumn)->afterLast('.')->ucfirst();

            $label = __($rawLabel);

            $columns[] = ExportColumn::make($relColumn)->label($label);
        }

        return $columns;
    }

    public static function getCompletedNotificationBody(Export $export): string
        {
            $body = trans_choice('filament-actions::export.notifications.completed.body', $export->successful_rows, [
                'successful' => number_format($export->successful_rows),
                'total' => number_format($export->total_rows),
            ]);

            if ($export->getFailedRowsCount() > 0) {
                $body .= ' ' . trans_choice('filament-actions::export.notifications.completed.errors', $export->getFailedRowsCount(), [
                    'count' => number_format($export->getFailedRowsCount()),
                ]);
            }

            return $body;
        }

    /**
     * Run exports synchronously instead of dispatching to a background queue.
     * This avoids requiring a queue worker to be running.
     */
    // public function getJobConnection(): ?string
    // {
    //     return 'sync';
    // }
}
