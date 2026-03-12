<?php

namespace App\Services;

use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Str;
use OpenSpout\Reader\Common\Creator\ReaderFactory;
use RuntimeException;
use Throwable;

class SpreadsheetRowReader
{
    /**
     * @param  array<string, string>  $headingAliases
     * @return array<int, array<string, string>>
     */
    public function readRows(string $path, array $headingAliases = []): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, ['csv', 'xlsx'], true)) {
            throw new RuntimeException('Only CSV and XLSX files are supported.');
        }

        $reader = ReaderFactory::createFromFile($path);
        $reader->open($path);

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $header = null;
                $rows = [];

                foreach ($sheet->getRowIterator() as $sheetRow) {
                    $cells = $sheetRow->toArray();

                    if ($header === null) {
                        $header = $this->prepareHeadings($cells, $headingAliases);

                        continue;
                    }

                    $row = [];

                    foreach ($header as $index => $heading) {
                        if ($heading === '') {
                            continue;
                        }

                        $row[$heading] = $this->normalizeCellValue($cells[$index] ?? null);
                    }

                    if ($this->rowHasValues($row)) {
                        $rows[] = $row;
                    }
                }

                return $rows;
            }

            return [];
        } catch (Throwable $exception) {
            throw new RuntimeException($exception->getMessage(), previous: $exception);
        } finally {
            $reader->close();
        }
    }

    /**
     * @param  array<int, mixed>  $cells
     * @param  array<string, string>  $headingAliases
     * @return array<int, string>
     */
    private function prepareHeadings(array $cells, array $headingAliases): array
    {
        $headings = [];

        foreach ($cells as $index => $value) {
            $headings[$index] = $this->normalizeHeading((string) $value, $headingAliases);
        }

        return $headings;
    }

    /**
     * @param  array<string, string>  $headingAliases
     */
    private function normalizeHeading(string $heading, array $headingAliases): string
    {
        $normalizedHeading = preg_replace('/^\xEF\xBB\xBF/u', '', trim($heading)) ?? '';
        $normalizedHeading = trim((string) preg_replace('/\s+/u', ' ', $normalizedHeading));

        if ($normalizedHeading === '') {
            return '';
        }

        $lowerHeading = mb_strtolower($normalizedHeading);

        if (array_key_exists($lowerHeading, $headingAliases)) {
            return $headingAliases[$lowerHeading];
        }

        $snakeHeading = trim(Str::snake(preg_replace('/[^[:alnum:]]+/u', ' ', $normalizedHeading) ?? ''));

        if ($snakeHeading !== '' && array_key_exists($snakeHeading, $headingAliases)) {
            return $headingAliases[$snakeHeading];
        }

        return $snakeHeading;
    }

    private function normalizeCellValue(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof DateInterval) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return rtrim(rtrim(number_format($value, 15, '.', ''), '0'), '.');
        }

        return trim((string) ($value ?? ''));
    }

    /**
     * @param  array<string, string>  $row
     */
    private function rowHasValues(array $row): bool
    {
        foreach ($row as $value) {
            if (filled($value)) {
                return true;
            }
        }

        return false;
    }
}
