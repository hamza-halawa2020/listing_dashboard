<?php

namespace App\Filament\Widgets;

use App\Services\DatabaseBackupManager;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Throwable;

class DatabaseBackupsTable extends TableWidget
{
    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('backups.manage') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Database Backups'))
            ->description($this->getBackupsTableDescription())
            ->records(fn () => $this->manager()->backups())
            ->paginated(false)
            ->columns([
                TextColumn::make('filename')
                    ->label(__('File'))
                    ->copyable()
                    ->searchable(false),
                TextColumn::make('trigger')
                    ->label(__('Trigger'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $this->manager()->triggerLabel($state))
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'pre_restore' => 'warning',
                        default => 'primary',
                    }),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime('Y-m-d H:i:s'),
                TextColumn::make('size_human')
                    ->label(__('Size')),
            ])
            ->headerActions([
                Action::make('createBackupNow')
                    ->label(__('Create Backup Now'))
                    ->icon('heroicon-o-circle-stack')
                    ->color('primary')
                    ->action(function (): void {
                        $this->runManualBackup();
                    }),
                Action::make('refreshBackups')
                    ->label(__('Refresh'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(function (): void {
                        $this->resetTable();
                    }),
            ])
            ->recordActions([
                Action::make('restore')
                    ->label(__('Restore'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('Restore Database'))
                    ->modalDescription(__('This will replace the current database with the selected backup. A safety backup of the current database will be created first.'))
                    ->modalSubmitActionLabel(__('Restore Backup'))
                    ->action(function (array $record): void {
                        $this->runRestore($record);
                    }),
                Action::make('delete')
                    ->label(__('Delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('Delete Backup'))
                    ->modalDescription(__('This will permanently delete the selected backup file.'))
                    ->action(function (array $record): void {
                        $this->runDelete($record);
                    }),
            ])
            ->emptyStateHeading(__('No backups found'))
            ->emptyStateDescription(__('Create your first backup now, then the system will continue with the daily automatic schedule.'))
            ->emptyStateActions([
                Action::make('createFirstBackup')
                    ->label(__('Create Backup Now'))
                    ->icon('heroicon-o-circle-stack')
                    ->action(function (): void {
                        $this->runManualBackup();
                    }),
            ]);
    }

    private function getBackupsTableDescription(): string
    {
        $latestBackup = $this->manager()->latestBackup();

        $description = [
            __('Automatic backups run every day at :time Cairo time.', [
                'time' => $this->manager()->scheduleTime(),
            ]),
            __('Only the latest :count backup files are kept. The oldest file is deleted automatically.', [
                'count' => $this->manager()->maxFiles(),
            ]),
        ];

        if ($latestBackup !== null) {
            $description[] = __('Last backup: :date (:trigger, :size).', [
                'date' => $latestBackup['created_at']->format('Y-m-d H:i:s'),
                'trigger' => $latestBackup['trigger_label'],
                'size' => $latestBackup['size_human'],
            ]);
        } else {
            $description[] = __('No backups have been created yet.');
        }

        return implode(' ', $description);
    }

    private function manager(): DatabaseBackupManager
    {
        return app(DatabaseBackupManager::class);
    }

    private function runManualBackup(): void
    {
        try {
            $backup = $this->manager()->createBackup('manual');

            $this->resetTable();
            $this->dispatch('refresh-page');

            Notification::make()
                ->title(__('Database backup created successfully.'))
                ->body($backup['filename'])
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(__('Backup failed'))
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function runRestore(array $record): void
    {
        try {
            $this->manager()->restoreBackup($record['filename']);

            $this->resetTable();
            $this->dispatch('refresh-page');

            Notification::make()
                ->title(__('Database restore completed successfully.'))
                ->body((string) $record['filename'])
                ->success()
                ->persistent()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(__('Restore failed'))
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function runDelete(array $record): void
    {
        try {
            $this->manager()->deleteBackup($record['filename']);

            $this->resetTable();

            Notification::make()
                ->title(__('Backup deleted successfully.'))
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(__('Delete failed'))
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
