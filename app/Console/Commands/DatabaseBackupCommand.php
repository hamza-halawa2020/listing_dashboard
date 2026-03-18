<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupManager;
use Illuminate\Console\Command;
use Throwable;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'database:backup {--trigger=manual : The source of the backup run (manual, scheduled, pre_restore)}';

    protected $description = 'Create a full SQL backup for the configured database.';

    public function handle(DatabaseBackupManager $backupManager): int
    {
        try {
            $backup = $backupManager->createBackup(
                trigger: (string) $this->option('trigger'),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(__('Database backup created successfully.'));
        $this->line(__('File') . ': ' . $backup['filename']);
        $this->line(__('Created At') . ': ' . $backup['created_at']->format('Y-m-d H:i:s'));
        $this->line(__('Size') . ': ' . $backup['size_human']);

        return self::SUCCESS;
    }
}
