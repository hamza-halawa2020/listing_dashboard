<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DatabaseBackupManager
{
    private const ALLOWED_TRIGGERS = [
        'manual',
        'scheduled',
        'pre_restore',
    ];

    public function backups(): Collection
    {
        $disk = $this->disk();
        $this->ensureBackupDirectoryExists();

        return collect($disk->files($this->directory()))
            ->filter(fn (string $path): bool => $this->isBackupFile($path))
            ->map(fn (string $path): array => $this->mapBackup($path))
            ->sortByDesc(fn (array $backup): int => $backup['created_at']->getTimestamp())
            ->values();
    }

    public function latestBackup(): ?array
    {
        return $this->backups()->first();
    }

    public function createBackup(string $trigger = 'manual'): array
    {
        return $this->withLock(
            fn (): array => $this->doCreateBackup(
                $this->normalizeTrigger($trigger),
                pruneAfterCreation: true,
            ),
        );
    }

    public function restoreBackup(string $filename, bool $createSafetyBackup = true): void
    {
        $this->withLock(function () use ($filename, $createSafetyBackup): void {
            $this->ensureMysqlConnection();
            $this->disableExecutionTimeout();

            $backup = $this->findBackup($filename);
            $safetyBackup = null;
            $broughtApplicationDown = false;

            try {
                if ($createSafetyBackup) {
                    $safetyBackup = $this->doCreateBackup(
                        'pre_restore',
                        pruneAfterCreation: false,
                    );
                }

                if (! app()->isDownForMaintenance()) {
                    Artisan::call('down');
                    $broughtApplicationDown = true;
                }

                DB::disconnect($this->connectionName());
                DB::purge($this->connectionName());

                $connection = $this->connection();

                foreach ($this->readStatements($this->absolutePath($backup['relative_path'])) as $statement) {
                    $connection->unprepared($statement);
                }
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    __('Failed to restore database backup: :message', ['message' => $exception->getMessage()]),
                    previous: $exception,
                );
            } finally {
                try {
                    $this->pruneBackups(
                        protectedRelativePaths: array_filter([
                            $backup['relative_path'],
                            $safetyBackup['relative_path'] ?? null,
                        ]),
                    );
                } catch (Throwable) {
                    // Keep the original restore exception if one exists.
                }

                DB::disconnect($this->connectionName());
                DB::purge($this->connectionName());

                if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
                }

                if ($broughtApplicationDown) {
                    Artisan::call('up');
                }
            }
        });
    }

    public function deleteBackup(string $filename): void
    {
        $this->withLock(function () use ($filename): void {
            $backup = $this->findBackup($filename);

            $this->disk()->delete($backup['relative_path']);
        });
    }
    public function pruneOldBackups(array $protectedRelativePaths = []): Collection
    {
        return $this->withLock(
            fn (): Collection => $this->pruneBackups(
                protectedRelativePaths: $protectedRelativePaths,
            ),
        );
    }

    public function nextScheduledRun(): CarbonImmutable
    {
        $timezone = $this->scheduleTimezone();
        $scheduleTime = $this->scheduleTime();

        $now = CarbonImmutable::now($timezone);
        $nextRun = $now->setTimeFromTimeString($scheduleTime);

        if ($nextRun->lessThanOrEqualTo($now)) {
            $nextRun = $nextRun->addDay();
        }

        return $nextRun;
    }

    public function maxFiles(): int
    {
        return max(1, (int) config('database_backups.max_files', 20));
    }

    public function scheduleTime(): string
    {
        return (string) config('database_backups.schedule_time', '04:00');
    }

    public function scheduleTimezone(): string
    {
        return (string) config('database_backups.schedule_timezone', 'Africa/Cairo');
    }

    public function triggerLabel(string $trigger): string
    {
        return match ($trigger) {
            'scheduled' => __('Scheduled'),
            'pre_restore' => __('Before Restore'),
            default => __('Manual'),
        };
    }

    private function pruneBackups(array $protectedRelativePaths = []): Collection
    {
        $protectedRelativePaths = array_values(array_unique(array_filter($protectedRelativePaths)));

        $backups = $this->backups();

        if ($backups->count() <= $this->maxFiles()) {
            return collect();
        }

        $deleted = collect();

        foreach ($backups->reverse() as $backup) {
            if ($backups->count() - $deleted->count() <= $this->maxFiles()) {
                break;
            }

            if (in_array($backup['relative_path'], $protectedRelativePaths, true)) {
                continue;
            }

            $this->disk()->delete($backup['relative_path']);
            $deleted->push($backup);
        }

        return $deleted;
    }

    private function doCreateBackup(string $trigger, bool $pruneAfterCreation): array
    {
        $this->ensureMysqlConnection();
        $this->disableExecutionTimeout();
        $this->ensureBackupDirectoryExists();

        $relativePath = $this->relativePath($this->generateFilename($trigger));
        $absolutePath = $this->absolutePath($relativePath);
        $writer = null;

        try {
            $connection = $this->connection();
            $writer = $this->openWriter($absolutePath);

            $createdAt = CarbonImmutable::now($this->scheduleTimezone());

            $this->writeLine($writer, '-- ' . __('Database backup generated by :app', ['app' => config('app.name')]));
            $this->writeLine($writer, '-- ' . __('Database') . ': ' . $this->databaseName());
            $this->writeLine($writer, '-- ' . __('Trigger') . ': ' . $trigger);
            $this->writeLine($writer, '-- ' . __('Created At') . ': ' . $createdAt->format('Y-m-d H:i:s'));
            $this->writeLine($writer, '');
            $this->writeLine($writer, 'SET NAMES utf8mb4;');
            $this->writeLine($writer, 'SET FOREIGN_KEY_CHECKS=0;');

            foreach ($this->tableNames($connection) as $table) {
                $this->dumpTable($connection, $table, $writer);
            }

            $this->writeLine($writer, 'SET FOREIGN_KEY_CHECKS=1;');
            $this->closeWriter($writer);

            if ($pruneAfterCreation) {
                $this->pruneBackups();
            }

            clearstatcache(true, $absolutePath);

            return $this->mapBackup($relativePath);
        } catch (Throwable $exception) {
            if ($writer !== null) {
                $this->closeWriter($writer);
            }

            if (File::exists($absolutePath)) {
                File::delete($absolutePath);
            }

            throw new RuntimeException(
                __('Failed to create database backup: :message', ['message' => $exception->getMessage()]),
                previous: $exception,
            );
        }
    }

    private function dumpTable(Connection $connection, string $table, array $writer): void
    {
        $escapedTable = $this->quoteIdentifier($table);
        $createStatement = $this->showCreateTable($connection, $table);
        $columns = $this->columnDefinitions($connection, $table);

        $this->writeLine($writer, '');
        $this->writeLine($writer, '-- ' . __('Table') . ': ' . $table);
        $this->writeLine($writer, "DROP TABLE IF EXISTS {$escapedTable};");
        $this->writeLine($writer, $this->normalizeStatement($createStatement));

        if ($columns->isEmpty()) {
            return;
        }

        $orderColumn = $this->primaryKeyColumns($connection, $table)->first() ?? $columns->first()['name'];
        $columnList = $columns
            ->pluck('name')
            ->map(fn (string $column): string => $this->quoteIdentifier($column))
            ->implode(', ');

        $connection
            ->table($table)
            ->orderBy($orderColumn)
            ->chunk($this->rowsPerInsert(), function (Collection $rows) use ($columns, $columnList, $escapedTable, $writer): void {
                $values = $rows
                    ->map(function (object $row) use ($columns): string {
                        $rowArray = (array) $row;

                        $sqlValues = $columns
                            ->map(fn (array $column): string => $this->toSqlLiteral(
                                $rowArray[$column['name']] ?? null,
                                $column,
                            ))
                            ->implode(', ');

                        return '(' . $sqlValues . ')';
                    })
                    ->implode(', ');

                if ($values !== '') {
                    $this->writeLine(
                        $writer,
                        "INSERT INTO {$escapedTable} ({$columnList}) VALUES {$values};",
                    );
                }
            });
    }

    private function tableNames(Connection $connection): Collection
    {
        return collect($connection->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"))
            ->map(function (object $row): ?string {
                foreach ((array) $row as $key => $value) {
                    if (str_starts_with((string) $key, 'Tables_in_')) {
                        return (string) $value;
                    }
                }

                return null;
            })
            ->filter()
            ->values();
    }

    private function columnDefinitions(Connection $connection, string $table): Collection
    {
        return collect($connection->select('SHOW COLUMNS FROM ' . $this->quoteIdentifier($table)))
            ->map(function (object $column): array {
                $columnData = (array) $column;
                $type = Str::lower((string) ($columnData['Type'] ?? ''));

                return [
                    'name' => (string) ($columnData['Field'] ?? ''),
                    'type' => $type,
                    'is_binary' => $this->isBinaryType($type),
                    'is_numeric' => $this->isNumericType($type),
                ];
            })
            ->filter(fn (array $column): bool => $column['name'] !== '')
            ->values();
    }

    private function primaryKeyColumns(Connection $connection, string $table): Collection
    {
        return collect($connection->select(
            'SHOW KEYS FROM ' . $this->quoteIdentifier($table) . " WHERE Key_name = 'PRIMARY'",
        ))
            ->sortBy(fn (object $key): int => (int) (((array) $key)['Seq_in_index'] ?? 0))
            ->map(fn (object $key): string => (string) (((array) $key)['Column_name'] ?? ''))
            ->filter()
            ->values();
    }

    private function showCreateTable(Connection $connection, string $table): string
    {
        $statement = $connection->selectOne('SHOW CREATE TABLE ' . $this->quoteIdentifier($table));
        $statementData = (array) $statement;

        return (string) ($statementData['Create Table'] ?? array_values($statementData)[1] ?? '');
    }

    private function toSqlLiteral(mixed $value, array $column): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if ($column['is_binary']) {
            $binaryValue = is_resource($value) ? stream_get_contents($value) : (string) $value;

            return '0x' . bin2hex($binaryValue);
        }

        if ($column['is_numeric']) {
            if (is_bool($value)) {
                return $value ? '1' : '0';
            }

            $numericValue = trim((string) $value);

            return $numericValue === '' ? 'NULL' : $numericValue;
        }

        return $this->quoteString((string) $value);
    }

    private function quoteString(string $value): string
    {
        return "'" . strtr($value, [
            '\\' => '\\\\',
            "'" => "\\'",
            "\0" => '\\0',
            "\n" => '\\n',
            "\r" => '\\r',
            "\x1a" => '\\Z',
        ]) . "'";
    }

    private function normalizeStatement(string $statement): string
    {
        $statement = trim(str_replace(["\r\n", "\r", "\n"], ' ', $statement));

        return str_ends_with($statement, ';') ? $statement : $statement . ';';
    }

    private function isNumericType(string $type): bool
    {
        return (bool) preg_match('/^(tinyint|smallint|mediumint|int|integer|bigint|decimal|dec|float|double|real|year|serial|boolean)\b/', $type);
    }

    private function isBinaryType(string $type): bool
    {
        return (bool) preg_match('/\b(blob|binary|varbinary)\b/', $type);
    }

    private function normalizeTrigger(string $trigger): string
    {
        $trigger = Str::snake($trigger);

        if (! in_array($trigger, self::ALLOWED_TRIGGERS, true)) {
            throw new RuntimeException(__('Unsupported backup trigger [:trigger].', ['trigger' => $trigger]));
        }

        return $trigger;
    }

    private function ensureMysqlConnection(): void
    {
        if ($this->connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException(__('Database backups currently support MySQL connections only.'));
        }
    }

    private function connection(): Connection
    {
        return DB::connection($this->connectionName());
    }

    private function connectionName(): string
    {
        return (string) config('database.default');
    }

    private function databaseName(): string
    {
        return (string) config('database.connections.' . $this->connectionName() . '.database', 'database');
    }

    private function generateFilename(string $trigger): string
    {
        $database = Str::slug($this->databaseName(), '_');
        $database = $database !== '' ? $database : 'database';
        $timestamp = CarbonImmutable::now($this->scheduleTimezone())->format('Ymd_His');
        $extension = $this->shouldUseGzip() ? '.sql.gz' : '.sql';

        return "{$database}_{$trigger}_{$timestamp}{$extension}";
    }

    private function shouldUseGzip(): bool
    {
        return (bool) config('database_backups.gzip', true) && function_exists('gzopen');
    }

    private function rowsPerInsert(): int
    {
        return max(1, (int) config('database_backups.rows_per_insert', 50));
    }

    private function ensureBackupDirectoryExists(): void
    {
        $this->disk()->makeDirectory($this->directory());
    }

    private function directory(): string
    {
        return trim((string) config('database_backups.directory', 'database-backups'), '/');
    }

    private function relativePath(string $filename): string
    {
        return $this->directory() . '/' . $filename;
    }

    private function absolutePath(string $relativePath): string
    {
        return $this->disk()->path($relativePath);
    }

    private function disk()
    {
        return Storage::disk((string) config('database_backups.disk', 'local'));
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function lockFilePath(): string
    {
        return storage_path('app/database-backups.lock');
    }

    private function disableExecutionTimeout(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
    }

    private function withLock(callable $callback): mixed
    {
        File::ensureDirectoryExists(dirname($this->lockFilePath()));

        $handle = fopen($this->lockFilePath(), 'c+');

        if (! is_resource($handle)) {
            throw new RuntimeException(__('Unable to open the backup lock file.'));
        }

        try {
            if (! flock($handle, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException(__('Another backup or restore process is already running.'));
            }

            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function openWriter(string $absolutePath): array
    {
        File::ensureDirectoryExists(dirname($absolutePath));

        if ($this->shouldUseGzip()) {
            $handle = gzopen($absolutePath, 'wb9');

            if (! is_resource($handle)) {
                throw new RuntimeException(__('Unable to create the compressed backup file.'));
            }

            return [
                'handle' => $handle,
                'gzip' => true,
            ];
        }

        $handle = fopen($absolutePath, 'wb');

        if (! is_resource($handle)) {
            throw new RuntimeException(__('Unable to create the backup file.'));
        }

        return [
            'handle' => $handle,
            'gzip' => false,
        ];
    }

    private function closeWriter(array $writer): void
    {
        if ($writer['gzip']) {
            gzclose($writer['handle']);

            return;
        }

        fclose($writer['handle']);
    }

    private function writeLine(array $writer, string $line): void
    {
        $line .= PHP_EOL;

        if ($writer['gzip']) {
            gzwrite($writer['handle'], $line);

            return;
        }

        fwrite($writer['handle'], $line);
    }

    private function readStatements(string $absolutePath): \Generator
    {
        $isCompressed = str_ends_with(Str::lower($absolutePath), '.gz');
        $handle = $isCompressed ? gzopen($absolutePath, 'rb') : fopen($absolutePath, 'rb');

        if (! is_resource($handle)) {
            throw new RuntimeException(__('Unable to read the backup file.'));
        }

        try {
            while ($isCompressed ? ! gzeof($handle) : ! feof($handle)) {
                $line = $isCompressed ? gzgets($handle) : fgets($handle);

                if ($line === false) {
                    continue;
                }

                $statement = trim($line);

                if ($statement === '' || str_starts_with($statement, '--')) {
                    continue;
                }

                yield $statement;
            }
        } finally {
            $isCompressed ? gzclose($handle) : fclose($handle);
        }
    }

    private function isBackupFile(string $path): bool
    {
        $path = Str::lower($path);

        return str_ends_with($path, '.sql') || str_ends_with($path, '.sql.gz');
    }

    private function mapBackup(string $relativePath): array
    {
        $disk = $this->disk();
        $filename = basename($relativePath);
        $size = (int) $disk->size($relativePath);
        $parsed = $this->parseFilename($filename);
        $createdAt = $parsed['created_at'] ?? CarbonImmutable::createFromTimestamp(
            $disk->lastModified($relativePath),
            $this->scheduleTimezone(),
        );
        $trigger = $parsed['trigger'] ?? 'manual';

        return [
            'key' => sha1($relativePath),
            'filename' => $filename,
            'relative_path' => $relativePath,
            'created_at' => $createdAt,
            'trigger' => $trigger,
            'trigger_label' => $this->triggerLabel($trigger),
            'size_bytes' => $size,
            'size_human' => $this->formatBytes($size),
            'is_compressed' => str_ends_with(Str::lower($filename), '.gz'),
        ];
    }

    private function parseFilename(string $filename): ?array
    {
        if (! preg_match(
            '/^[a-z0-9_]+_(?<trigger>manual|scheduled|pre_restore)_(?<timestamp>\d{8}_\d{6})\.sql(?:\.gz)?$/i',
            $filename,
            $matches,
        )) {
            return null;
        }

        $createdAt = CarbonImmutable::createFromFormat(
            'Ymd_His',
            $matches['timestamp'],
            $this->scheduleTimezone(),
        );

        if ($createdAt === false) {
            return null;
        }

        return [
            'trigger' => Str::lower($matches['trigger']),
            'created_at' => $createdAt,
        ];
    }

    private function findBackup(string $filename): array
    {
        $filename = basename($filename);

        $backup = $this->backups()->firstWhere('filename', $filename);

        if (! is_array($backup)) {
            throw new RuntimeException(__('The selected backup file could not be found.'));
        }

        return $backup;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = max(0, $bytes);
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        return number_format($value, $unitIndex === 0 ? 0 : 2) . ' ' . $units[$unitIndex];
    }
}
