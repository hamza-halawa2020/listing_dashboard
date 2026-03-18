<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupManager;
use Illuminate\Console\Command;
use Throwable;

class DatabaseRestoreCommand extends Command
{
    protected $signature = 'database:restore {backup : The backup file name to restore} {--without-safety-backup : Skip creating a pre-restore safety backup}';

    protected $description = 'Restore the database from a previously generated SQL backup.';

    public function handle(DatabaseBackupManager $backupManager): int
    {
        try {
            $backupManager->restoreBackup(
                filename: (string) $this->argument('backup'),
                createSafetyBackup: ! $this->option('without-safety-backup'),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(__('Database restore completed successfully.'));

        return self::SUCCESS;
    }
}
