<?php

namespace App\Console\Commands;

use App\Services\ListingSpreadsheetImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportListingsFromCsv extends Command
{
    protected $signature = 'app:import-listings {file : Path to a CSV or XLSX spreadsheet}';

    protected $description = 'Import or patch listings from a spreadsheet file.';

    public function handle(ListingSpreadsheetImporter $importer): int
    {
        $file = (string) $this->argument('file');

        if (! is_file($file)) {
            $this->error('The provided file path does not exist.');

            return self::FAILURE;
        }

        try {
            $summary = $importer->import($file);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Import completed.');
        $this->line('Created: ' . $summary['created']);
        $this->line('Updated: ' . $summary['updated']);
        $this->line('Skipped: ' . $summary['skipped']);

        foreach ($summary['errors'] as $error) {
            $this->warn($error);
        }

        return self::SUCCESS;
    }
}
