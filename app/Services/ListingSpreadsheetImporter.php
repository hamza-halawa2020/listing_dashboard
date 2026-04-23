<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Location;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use Throwable;
use ZipArchive;

class ListingSpreadsheetImporter
{
    private GeocodingService $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    private const DEFAULT_GOVERNORATE_SHIPPING_COST = 90;

    private const HEADING_ALIASES = [
        'name' => 'name',
        'title' => 'name',
        'listing' => 'name',
        'provider' => 'name',
        'listing_name' => 'name',
        'category' => 'category_name',
        'category_name' => 'category_name',
        'category_title' => 'category_name',
        'specialization' => 'specialization_name',
        'specialty' => 'specialization_name',
        'sub_category' => 'specialization_name',
        'sub_category_name' => 'specialization_name',
        'subcategory' => 'specialization_name',
        'governorate' => 'governorate_name',
        'governorate_name' => 'governorate_name',
        'province' => 'governorate_name',
        'province_name' => 'governorate_name',
        'state' => 'governorate_name',
        'area' => 'area_name',
        'area_name' => 'area_name',
        'city' => 'area_name',
        'location' => 'area_name',
        'district' => 'area_name',
        'neighborhood' => 'area_name',
        'address' => 'address',
        'description' => 'description',
        'notes' => 'description',
        'phone' => 'phone',
        'phones' => 'phone',
        'phone_number' => 'phone',
        'phone_numbers' => 'phone',
        'mobile' => 'phone',
        'discount' => 'discount_percentage',
        'discount_percentage' => 'discount_percentage',
        'offer_discount' => 'discount_percentage',
        'offer' => 'discount_percentage',
        'المحافظة' => 'governorate_name',
        'المحافطة' => 'governorate_name',
        'المنطقة' => 'area_name',
        'المنطه' => 'area_name',
        'مقدم الخدمة' => 'name',
        'الفئة' => 'category_name',
        'الفئه' => 'category_name',
        'التخصص' => 'specialization_name',
        'العنوان' => 'address',
        'رقم الهاتف' => 'phone',
        'رقم التليفون' => 'phone',
        'الهاتف' => 'phone',
        'الخصم' => 'discount_percentage',
    ];

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
        $totalRows = count($rows);

        Log::info('Listing import started', [
            'file'       => basename($path),
            'total_rows' => $totalRows,
            'memory_mb'  => round(memory_get_usage(true) / 1024 / 1024, 1),
        ]);

        $summary = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors'  => [],
        ];

        $logEvery = max(1, (int) ceil($totalRows / 20)); // log every 5%

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $result = $this->importRow($row, $index);

                if ($result === 'created') {
                    $summary['created']++;
                } elseif ($result === 'updated') {
                    $summary['updated']++;
                } else {
                    $summary['skipped']++;
                }
            } catch (Throwable $exception) {
                $summary['skipped']++;
                $errorMsg = 'Row ' . $rowNumber . ': ' . $exception->getMessage();
                $summary['errors'][] = $errorMsg;

                Log::warning('Listing import row error', [
                    'row'     => $rowNumber,
                    'name'    => $row['name'] ?? $row['مقدم الخدمة'] ?? '?',
                    'error'   => $exception->getMessage(),
                ]);
            }

            // Progress log every 5%
            if (($index + 1) % $logEvery === 0 || ($index + 1) === $totalRows) {
                Log::info('Listing import progress', [
                    'processed'  => $index + 1,
                    'total'      => $totalRows,
                    'pct'        => round(($index + 1) / $totalRows * 100) . '%',
                    'created'    => $summary['created'],
                    'updated'    => $summary['updated'],
                    'skipped'    => $summary['skipped'],
                    'errors'     => count($summary['errors']),
                    'memory_mb'  => round(memory_get_usage(true) / 1024 / 1024, 1),
                ]);
            }
        }

        Log::info('Listing import finished', [
            'file'      => basename($path),
            'created'   => $summary['created'],
            'updated'   => $summary['updated'],
            'skipped'   => $summary['skipped'],
            'errors'    => count($summary['errors']),
            'memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 1) . ' peak',
        ]);

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function importRow(array $row, int $rowIndex): string
    {
        $row = $this->normalizeRow($row);

        if (! $this->rowHasValues($row)) {
            return 'skipped';
        }

        [$listing, $exists] = $this->resolveListing($row);

        $attributes = $this->extractAttributes($row);
        $this->fillMissingCoordinates($row, $attributes, $rowIndex);
        $this->logCoordinateGap($row, $attributes, $rowIndex);

        if (! $exists && $attributes === []) {
            Log::debug('Listing import: row skipped — no attributes extracted', [
                'row' => $rowIndex + 2,
                'name' => $row['name'] ?? '?',
            ]);
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

        if (! $exists) {
            $listing->save();
            $result = 'created';
        } elseif ($listing->isDirty()) {
            $listing->save();
            $result = 'updated';
        } else {
            $result = 'skipped';
        }

        $this->syncPhones($listing, $row['phone'] ?? null);
        $this->syncOffer($listing, $row['discount_percentage'] ?? null);
        $this->ensureDefaultWorkingHours($listing);

        return $result;
    }

    private function ensureDefaultWorkingHours(Listing $listing): void
    {
        if ($listing->workingHours()->exists()) {
            return;
        }

        $days = [
            'saturday',
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
        ];

        $hours = array_map(static fn (string $day): array => [
            'day' => $day,
            'open_time' => '09:00:00',
            'close_time' => '21:00:00',
            'is_closed' => false,
        ], $days);

        $listing->workingHours()->createMany($hours);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{0: Listing, 1: bool}
     */
    private function resolveListing(array $row): array
    {
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

        $specializationName = trim((string) ($row['specialization_name'] ?? ''));
        $categoryName = trim((string) ($row['category_name'] ?? ''));

        if ($specializationName !== '') {
            $category = $this->findOrCreateCategory($specializationName, $categoryName ?: null);

            return $category->id;
        }

        if ($categoryName !== '') {
            $category = $this->findOrCreateCategory($categoryName);

            return $category->id;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveLocationId(array $row): ?int
    {
        if ($this->hasFilledValue($row, 'governorate_name')) {
            $governorateName = (string) $row['governorate_name'];
            $areaName = $this->hasFilledValue($row, 'area_name') ? (string) $row['area_name'] : null;

            $location = $this->findOrCreateLocation($governorateName, $areaName);

            return $location->id;
        }

        if ($this->hasFilledValue($row, 'area_name')) {
            throw new RuntimeException('A governorate_name is required when resolving the location by area_name.');
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

    private function findOrCreateCategory(string $name, ?string $parentName = null): Category
    {
        $normalized = trim($name);

        if ($normalized === '') {
            throw new RuntimeException('A category name is required to create a category.');
        }

        $parent = null;

        if ($parentName !== null && trim($parentName) !== '') {
            $parent = $this->findCategoryByName($parentName);

            if (! $parent) {
                $parent = Category::create([
                    'name' => $parentName,
                    'slug' => $this->generateUniqueCategorySlug($parentName),
                ]);
            }
        }

        $parentId = $parent ? $parent->id : null;

        $category = $this->findCategoryByName($name, $parentId);

        if ($category) {
            return $category;
        }

        return Category::create([
            'name' => $name,
            'slug' => $this->generateUniqueCategorySlug($name),
            'parent_id' => $parentId,
        ]);
    }

    private function findCategoryByName(string $name, ?int $parentId = null): ?Category
    {
        $query = $this->applyExactNameSearch(Category::query(), $name);

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        return $query->first();
    }

    private function findOrCreateLocation(string $governorateName, ?string $areaName): Location
    {
        $governorate = $this->findLocationByName($governorateName);

        if (! $governorate) {
            Log::info('Listing import: creating new governorate', [
                'name' => $governorateName,
            ]);
            $governorate = Location::create([
                'name'          => $governorateName,
                'type'          => 'governorate',
                'shipping_cost' => self::DEFAULT_GOVERNORATE_SHIPPING_COST,
            ]);
        }

        if (! $areaName) {
            return $governorate;
        }

        $area = $this->findLocationByName($areaName, $governorate->id);

        if (! $area) {
            Log::info('Listing import: creating new area', [
                'name'        => $areaName,
                'governorate' => $governorateName,
            ]);
            $area = Location::create([
                'name'      => $areaName,
                'parent_id' => $governorate->id,
                'type'      => 'zone',
            ]);
        }

        return $area;
    }

    private function findLocationByName(string $name, ?int $parentId = null): ?Location
    {
        $query = $this->applyExactNameSearch(Location::query(), $name);

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        return $query->first();
    }

    private function generateUniqueCategorySlug(string $name): string
    {
        $slug = Str::slug($name) ?: 'category';
        $current = $slug;
        $counter = 1;

        while (Category::where('slug', $current)->exists()) {
            $current = "{$slug}-{$counter}";
            $counter++;
        }

        return $current;
    }

    private function syncPhones(Listing $listing, ?string $phoneValue): void
    {
        if (! filled($phoneValue)) {
            return;
        }

        $numbers = $this->splitPhoneNumbers($phoneValue);

        if ($numbers === []) {
            return;
        }

        $listing->phones()->whereNotIn('phone_number', $numbers)->delete();

        foreach ($numbers as $number) {
            $listing->phones()->updateOrCreate(
                ['phone_number' => $number],
                ['type' => 'mobile'],
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function splitPhoneNumbers(string $value): array
    {
        $parts = preg_split('/[\r\n\/|,;]+/', $value) ?: [];

        $numbers = [];

        foreach ($parts as $part) {
            $number = trim($part);

            if ($number === '') {
                continue;
            }

            $numbers[] = $number;
        }

        return array_values(array_unique($numbers));
    }

    private function syncOffer(Listing $listing, ?string $discountValue): void
    {
        if (! filled($discountValue)) {
            return;
        }

        $percentage = $this->normalizeDiscountPercentage($discountValue);

        if ($percentage === null) {
            return;
        }

        $attributes = [
            'title' => '',
            'description' => '',
            'discount_percentage' => $percentage,
            'is_active' => true,
        ];

        $offer = $listing->offers()->first();

        if ($offer) {
            $offer->fill($attributes)->save();
        } else {
            $listing->offers()->create($attributes);
        }
    }

    private function normalizeDiscountPercentage(string $value): ?float
    {
        $cleanValue = str_replace('%', '', trim($value));

        if ($cleanValue === '') {
            return null;
        }

        $cleanValue = str_replace(',', '.', $cleanValue);

        if (! is_numeric($cleanValue)) {
            if (preg_match('/(\d+(?:\.\d+)?)/', $cleanValue, $matches)) {
                $cleanValue = $matches[1];
            } else {
                return null;
            }
        }

        $percentage = (float) $cleanValue;

        if ($percentage <= 1) {
            $percentage *= 100;
        }

        return min(100, max(0, $percentage));
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
            $heading = $this->normalizeHeading((string) $value);

            $headings[$index] = $heading;
        }

        return $headings;
    }

    private function normalizeHeading(string $heading): string
    {
        $heading = preg_replace('/^\xEF\xBB\xBF/u', '', trim($heading));

        if ($heading === '') {
            return '';
        }

        $lowerHeading = mb_strtolower($heading);

        if (array_key_exists($lowerHeading, self::HEADING_ALIASES)) {
            return self::HEADING_ALIASES[$lowerHeading];
        }

        $cleaned = trim(Str::snake(preg_replace('/[^A-Za-z0-9]+/u', ' ', $heading) ?? ''));

        return $cleaned;
    }

    private function fillMissingCoordinates(array $row, array &$attributes, int $rowIndex): void
    {
        $hasLat = array_key_exists('latitude', $attributes) && filled($attributes['latitude']);
        $hasLng = array_key_exists('longitude', $attributes) && filled($attributes['longitude']);

        if ($hasLat && $hasLng) {
            return;
        }

        $address = trim($row['address'] ?? '');
        $area = trim($row['area_name'] ?? '');
        $governorate = trim($row['governorate_name'] ?? '');

        if ($address === '' && $area === '' && $governorate === '') {
            return;
        }

        $candidates = $this->buildGeocodingCandidates($address, $area, $governorate);

        $geo = $this->geocodingService->geocodeFromCandidates($candidates);

        if (! $geo) {
            Log::info('OpenStreetMap geocoding did not return coordinates', [
                'row_number' => $rowIndex + 2,
                'name' => $row['name'] ?? null,
                'address' => $address,
                'area' => $area,
                'governorate' => $governorate,
            ]);

            return;
        }

        $attributes['latitude'] = $geo['lat'];
        $attributes['longitude'] = $geo['lng'];

        Log::info('OpenStreetMap geocoding supplied coordinates', [
            'row_number' => $rowIndex + 2,
            'name' => $row['name'] ?? null,
            'latitude' => $geo['lat'],
            'longitude' => $geo['lng'],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function buildGeocodingCandidates(string $address, string $area, string $governorate): array
    {
        $segments = $this->splitAddressSegments($address);
        $candidates = [];

        foreach ($segments as $segment) {
            $trimmed = trim($segment);

            if ($trimmed === '') {
                continue;
            }

            $candidates[] = $trimmed;

            if ($area !== '') {
                $candidates[] = "{$trimmed}, {$area}";
            }

            if ($governorate !== '') {
                $candidates[] = "{$trimmed}, {$governorate}";
            }

            if ($area !== '' && $governorate !== '') {
                $candidates[] = "{$trimmed}, {$area}, {$governorate}";
            }
        }

        if ($area !== '' && $governorate !== '') {
            $candidates[] = "{$area}, {$governorate}";
            $candidates[] = $area;
        }

        if ($governorate !== '') {
            $candidates[] = $governorate;
        }

        return array_values(array_unique(array_filter($candidates, static fn (string $value): bool => $value !== '')));
    }

    /**
     * @return array<int, string>
     */
    private function splitAddressSegments(string $address): array
    {
        if ($address === '') {
            return [];
        }

        $parts = preg_split('/[\r\n,\/\-]+/u', $address) ?: [];

        $segments = [];

        for ($length = count($parts); $length > 0; $length--) {
            $joined = implode(', ', array_slice($parts, 0, $length));
            $segments[] = $joined;
        }

        return $segments;
    }

    private function logCoordinateGap(array $row, array $attributes, int $rowIndex): void
    {
        $hasLocationContext = $this->hasFilledValue($row, 'address')
            || $this->hasFilledValue($row, 'governorate_name')
            || $this->hasFilledValue($row, 'area_name');

        if (! $hasLocationContext) {
            return;
        }

        $latMissing = ! (array_key_exists('latitude', $attributes) && filled($attributes['latitude']));
        $lngMissing = ! (array_key_exists('longitude', $attributes) && filled($attributes['longitude']));

        if (! $latMissing && ! $lngMissing) {
            return;
        }

        $reasons = [];

        if ($latMissing) {
            $reasons[] = 'latitude missing';
        }

        if ($lngMissing) {
            $reasons[] = 'longitude missing';
        }

        Log::warning('Listing import row missing coordinates', [
            'row_number' => $rowIndex + 2,
            'name' => $row['name'] ?? null,
            'reasons' => $reasons,
            'governorate' => $row['governorate_name'] ?? null,
            'area' => $row['area_name'] ?? null,
            'address' => $row['address'] ?? null,
        ]);
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
