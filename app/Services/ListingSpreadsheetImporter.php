<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use Throwable;
use ZipArchive;

class ListingSpreadsheetImporter
{
    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     errors: array<int, string>
     * }
     */
    public function import(string $path): array
    {
        $rows = $this->readRows($path);

        $summary = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($rows as $index => $row) {
            try {
                $result = $this->importRow($row);

                if ($result === 'created') {
                    $summary['created']++;

                    continue;
                }

                if ($result === 'updated') {
                    $summary['updated']++;

                    continue;
                }

                $summary['skipped']++;
            } catch (Throwable $exception) {
                $summary['skipped']++;
                $summary['errors'][] = 'Row ' . ($index + 2) . ': ' . $exception->getMessage();
            }
        }

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function importRow(array $row): string
    {
        $row = $this->normalizeRow($row);

        if (! $this->rowHasValues($row)) {
            return 'skipped';
        }

        [$listing, $exists] = $this->resolveListing($row);

        $attributes = $this->extractAttributes($row);

        if (! $exists && $attributes === []) {
            return 'skipped';
        }

        if (! $exists) {
            foreach (['name', 'category_id', 'location_id'] as $requiredField) {
                if (! array_key_exists($requiredField, $attributes) || blank($attributes[$requiredField])) {
                    throw new RuntimeException("Missing required field [{$requiredField}] for a new listing.");
                }
            }
        }

        if ($exists && $attributes === []) {
            return 'skipped';
        }

        $listing->fill($attributes);

        if ($exists && ! $listing->isDirty()) {
            return 'skipped';
        }

        $listing->save();

        return $exists ? 'updated' : 'created';
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{0: Listing, 1: bool}
     */
    private function resolveListing(array $row): array
    {
        if ($this->hasFilledValue($row, 'name')) {
            $listing = $this->applyExactNameSearch(Listing::query(), (string) $row['name'])->first();

            if ($listing) {
                return [$listing, true];
            }
        }

        if ($this->hasFilledValue($row, 'id')) {
            $listing = Listing::find((int) $row['id']);

            if ($listing) {
                return [$listing, true];
            }
        }

        return [new Listing(), false];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function extractAttributes(array $row): array
    {
        $attributes = [];

        foreach (['name', 'address', 'description'] as $field) {
            if ($this->hasFilledValue($row, $field)) {
                $attributes[$field] = trim((string) $row[$field]);
            }
        }

        foreach (['latitude', 'longitude'] as $field) {
            if ($this->hasFilledValue($row, $field)) {
                if (! is_numeric($row[$field])) {
                    throw new RuntimeException("The [{$field}] value must be numeric.");
                }

                $attributes[$field] = (float) $row[$field];
            }
        }

        $attributes['is_active'] = true;

        $categoryId = $this->resolveCategoryId($row);

        if ($categoryId !== null) {
            $attributes['category_id'] = $categoryId;
        }

        $locationId = $this->resolveLocationId($row);

        if ($locationId !== null) {
            $attributes['location_id'] = $locationId;
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveCategoryId(array $row): ?int
    {
        if ($this->hasFilledValue($row, 'category_id')) {
            if (! ctype_digit((string) $row['category_id'])) {
                throw new RuntimeException('The provided category_id must be a valid integer.');
            }

            $category = Category::find((int) $row['category_id']);

            if ($category) {
                return $category->id;
            }

            throw new RuntimeException('The provided category_id was not found.');
        }

        $category = null;

        if ($this->hasFilledValue($row, 'category_path')) {
            $category = $this->findCategoryByPath((string) $row['category_path']);
        }

        if (! $category && $this->hasFilledValue($row, 'category_name')) {
            $category = $this->applyExactNameSearch(Category::query(), (string) $row['category_name'])->first();
        }

        if ($category) {
            return $category->id;
        }

        if ($this->hasFilledValue($row, 'category_name') || $this->hasFilledValue($row, 'category_path')) {
            throw new RuntimeException('The related category could not be resolved from the provided name.');
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveLocationId(array $row): ?int
    {
        if ($this->hasFilledValue($row, 'governorate_name') || $this->hasFilledValue($row, 'area_name')) {
            if (! $this->hasFilledValue($row, 'governorate_name') || ! $this->hasFilledValue($row, 'area_name')) {
                throw new RuntimeException('Both governorate_name and area_name are required to resolve the location.');
            }

            $location = $this->findLocationByGovernorateAndArea(
                (string) $row['governorate_name'],
                (string) $row['area_name'],
            );

            if (! $location) {
                throw new RuntimeException('The related location could not be resolved from the provided governorate and area.');
            }

            return $location->id;
        }

        if ($this->hasFilledValue($row, 'location_id')) {
            if (! ctype_digit((string) $row['location_id'])) {
                throw new RuntimeException('The provided location_id must be a valid integer.');
            }

            $location = Location::find((int) $row['location_id']);

            if ($location) {
                return $location->id;
            }

            throw new RuntimeException('The provided location_id was not found.');
        }

        $location = null;

        if ($this->hasFilledValue($row, 'location_path')) {
            $location = $this->findLocationByPath((string) $row['location_path']);
        }

        if (! $location && $this->hasFilledValue($row, 'location_name')) {
            $location = $this->applyExactNameSearch(Location::query(), (string) $row['location_name'])->first();
        }

        if ($location) {
            return $location->id;
        }

        if ($this->hasFilledValue($row, 'location_name') || $this->hasFilledValue($row, 'location_path')) {
            throw new RuntimeException('The related location could not be resolved from the provided name.');
        }

        return null;
    }

    private function findLocationByGovernorateAndArea(string $governorateName, string $areaName): ?Location
    {
        $governorate = $this->applyExactNameSearch(
            Location::query()->whereNull('parent_id'),
            $governorateName,
        )->first();

        if (! $governorate) {
            return null;
        }

        return $this->applyExactNameSearch(
            Location::query()->where('parent_id', $governorate->id),
            $areaName,
        )->first();
    }

    private function findCategoryByPath(string $path): ?Category
    {
        return $this->findByPath(
            Category::class,
            $path,
            static fn (Builder $query): Builder => $query->whereNull('parent_id'),
        );
    }

    private function findLocationByPath(string $path): ?Location
    {
        return $this->findByPath(
            Location::class,
            $path,
            static fn (Builder $query): Builder => $query->whereNull('parent_id'),
        );
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TModel>  $modelClass
     * @param  callable(Builder): Builder  $rootConstraint
     * @return TModel|null
     */
    private function findByPath(string $modelClass, string $path, callable $rootConstraint): mixed
    {
        $segments = array_values(array_filter(
            preg_split('/\s*(?:>|\/|\\\\)\s*/u', trim($path)) ?: [],
            static fn (?string $segment): bool => filled($segment),
        ));

        if ($segments === []) {
            return null;
        }

        $record = null;

        foreach ($segments as $index => $segment) {
            $query = $this->applyExactNameSearch($modelClass::query(), $segment);

            if ($index === 0) {
                $query = $rootConstraint($query);
            } elseif ($record) {
                $query->where('parent_id', $record->id);
            }

            $record = $query->first();

            if (! $record) {
                return null;
            }
        }

        return $record;
    }

    private function applyExactNameSearch(Builder $query, string $value): Builder
    {
        $normalizedValue = mb_strtolower(trim($value));

        return $query->whereRaw('LOWER(name) = ?', [$normalizedValue]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function readRows(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv' => $this->readCsv($path),
            'xlsx' => $this->readXlsx($path),
            default => throw new RuntimeException('Only CSV and XLSX files are supported.'),
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');

        if (! $handle) {
            throw new RuntimeException('The uploaded file could not be read.');
        }

        $firstLine = fgets($handle);

        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        rewind($handle);

        $header = null;
        $rows = [];

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($header === null) {
                $header = $this->prepareHeadings($line);

                continue;
            }

            $row = [];

            foreach ($header as $index => $heading) {
                if ($heading === '') {
                    continue;
                }

                $row[$heading] = isset($line[$index]) ? trim((string) $line[$index]) : '';
            }

            if ($this->rowHasValues($row)) {
                $rows[] = $row;
            }
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function readXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('XLSX import is not available because ZipArchive is missing.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('The uploaded XLSX file could not be opened.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            $sheetPath = $this->resolveFirstWorksheetPath($zip);
            $worksheetContents = $zip->getFromName($sheetPath);

            if ($worksheetContents === false) {
                throw new RuntimeException('The worksheet could not be loaded from the XLSX file.');
            }

            return $this->extractRowsFromWorksheet($worksheetContents, $sharedStrings);
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $contents = $zip->getFromName('xl/sharedStrings.xml');

        if ($contents === false) {
            return [];
        }

        $xml = $this->loadXml($contents);
        $xml->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $strings = [];

        foreach ($xml->xpath('//main:si') ?: [] as $item) {
            $strings[] = $this->collectTextNodes($item);
        }

        return $strings;
    }

    private function resolveFirstWorksheetPath(ZipArchive $zip): string
    {
        $workbookContents = $zip->getFromName('xl/workbook.xml');
        $relationshipsContents = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookContents === false || $relationshipsContents === false) {
            throw new RuntimeException('The workbook structure is incomplete.');
        }

        $workbook = $this->loadXml($workbookContents);
        $workbook->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

        $sheet = ($workbook->xpath('//main:sheets/main:sheet') ?: [])[0] ?? null;

        if (! $sheet) {
            throw new RuntimeException('The XLSX file does not contain any worksheets.');
        }

        $relationshipId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;

        $relationships = $this->loadXml($relationshipsContents);
        $relationships->registerXPathNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');

        foreach ($relationships->xpath('//rel:Relationship') ?: [] as $relationship) {
            if ((string) $relationship['Id'] !== $relationshipId) {
                continue;
            }

            $target = (string) $relationship['Target'];

            if (str_starts_with($target, '/')) {
                return ltrim($target, '/');
            }

            if (str_starts_with($target, 'xl/')) {
                return $target;
            }

            return 'xl/' . ltrim($target, '/');
        }

        throw new RuntimeException('The first worksheet could not be resolved.');
    }

    /**
     * @param  array<int, string>  $sharedStrings
     * @return array<int, array<string, string>>
     */
    private function extractRowsFromWorksheet(string $worksheetContents, array $sharedStrings): array
    {
        $xml = $this->loadXml($worksheetContents);
        $xml->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = [];
        $header = null;

        foreach ($xml->xpath('//main:sheetData/main:row') ?: [] as $rowNode) {
            $rowNode->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            $cells = [];

            foreach ($rowNode->xpath('main:c') ?: [] as $cell) {
                $reference = (string) $cell['r'];
                $columnIndex = $this->columnIndexFromReference($reference);
                $type = (string) $cell['t'];
                $value = $this->extractCellValue($cell, $type, $sharedStrings);

                $cells[$columnIndex] = $value;
            }

            if ($cells === []) {
                continue;
            }

            ksort($cells);

            if ($header === null) {
                $header = $this->prepareHeadings($cells);

                continue;
            }

            $row = [];

            foreach ($header as $index => $heading) {
                if ($heading === '') {
                    continue;
                }

                $row[$heading] = trim((string) ($cells[$index] ?? ''));
            }

            if ($this->rowHasValues($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private function extractCellValue(SimpleXMLElement $cell, string $type, array $sharedStrings): string
    {
        if ($type === 'inlineStr') {
            $cell->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            $inlineString = ($cell->xpath('main:is') ?: [])[0] ?? null;

            return $inlineString ? $this->collectTextNodes($inlineString) : '';
        }

        $cell->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $valueNode = ($cell->xpath('main:v') ?: [])[0] ?? null;
        $rawValue = $valueNode ? (string) $valueNode : '';

        if ($type === 's') {
            return $sharedStrings[(int) $rawValue] ?? '';
        }

        return $rawValue;
    }

    /**
     * @param  array<int, mixed>  $cells
     * @return array<int, string>
     */
    private function prepareHeadings(array $cells): array
    {
        $headings = [];

        foreach ($cells as $index => $value) {
            $heading = preg_replace('/^\xEF\xBB\xBF/u', '', trim((string) $value));
            $heading = trim(Str::snake(preg_replace('/[^A-Za-z0-9]+/u', ' ', $heading) ?? ''));

            $headings[$index] = $heading;
        }

        return $headings;
    }

    private function columnIndexFromReference(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference)) ?: 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function loadXml(string $contents): SimpleXMLElement
    {
        $xml = simplexml_load_string($contents);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('The spreadsheet contains invalid XML.');
        }

        return $xml;
    }

    private function collectTextNodes(SimpleXMLElement $node): string
    {
        $node->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $parts = [];

        foreach ($node->xpath('.//main:t') ?: [] as $textNode) {
            $parts[] = (string) $textNode;
        }

        return implode('', $parts);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalizedKey = trim(Str::snake((string) $key));

            if ($normalizedKey === '') {
                continue;
            }

            $normalized[$normalizedKey] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $row
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

    /**
     * @param  array<string, mixed>  $row
     */
    private function hasFilledValue(array $row, string $key): bool
    {
        return array_key_exists($key, $row) && filled($row[$key]);
    }

}
