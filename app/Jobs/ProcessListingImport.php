<?php

namespace App\Jobs;

use App\Models\ListingImportRun;
use App\Services\ListingSpreadsheetImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProcessListingImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 0;

    public function __construct(
        public int $runId,
    ) {
    }

    public function handle(ListingSpreadsheetImporter $importer): void
    {
        $run = ListingImportRun::findOrFail($this->runId);

        $run->update([
            'status' => ListingImportRun::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        $absolutePath = $this->resolveAbsolutePath($run);
        $summary = ['errors' => []];

        try {
            $summary = $importer->import($absolutePath);

            $run->update([
                'status' => ListingImportRun::STATUS_COMPLETED,
                'summary_created' => $summary['created'],
                'summary_updated' => $summary['updated'],
                'summary_skipped' => $summary['skipped'],
                'summary_errors' => $summary['errors'],
                'failure_message' => null,
                'finished_at' => now(),
            ]);

            Log::info('Queued listing import completed', [
                'run_id' => $run->id,
                'summary' => $summary,
            ]);
        } catch (Throwable $exception) {
            $run->update([
                'status' => ListingImportRun::STATUS_FAILED,
                'summary_errors' => $summary['errors'] ?? $run->summary_errors ?? [],
                'failure_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('Queued listing import failed', [
                'run_id' => $run->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            $this->deleteUploadedFile($run);
        }
    }

    private function resolveAbsolutePath(ListingImportRun $run): string
    {
        $disk = Storage::disk($run->disk);

        $absolutePath = $disk->path($run->path);

        if (! is_string($absolutePath) || $absolutePath === '') {
            throw new RuntimeException('Could not determine the uploaded file path.');
        }

        if (! file_exists($absolutePath)) {
            throw new RuntimeException('The uploaded file could not be found.');
        }

        return $absolutePath;
    }

    private function deleteUploadedFile(ListingImportRun $run): void
    {
        Storage::disk($run->disk)->delete($run->path);
    }
}
