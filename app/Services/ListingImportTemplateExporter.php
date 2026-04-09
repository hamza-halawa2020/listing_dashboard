<?php

namespace App\Services;

use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;

class ListingImportTemplateExporter
{
    private const SOURCE_FILENAME = 'listings-import-template.xlsx';

    public function downloadFilename(): string
    {
        return self::SOURCE_FILENAME;
    }

    public function createTemporaryFile(): string
    {
        $source = $this->prepareSourcePath();
        $destination = storage_path('app/private/' . Str::uuid() . '.xlsx');
        $directory = dirname($destination);

        if (! is_dir($directory)) {
            if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                throw new RuntimeException('Could not prepare temporary directory for the import template.');
            }
        }

        if (! copy($source, $destination)) {
            throw new RuntimeException('Could not prepare the import template download.');
        }

        return $destination;
    }

    private function prepareSourcePath(): string
    {
        $path = resource_path('templates/' . self::SOURCE_FILENAME);

        if (! file_exists($path)) {
            throw new RuntimeException("The import template file [{$path}] is missing.");
        }

        return $path;
    }

    /**
     * @return list<string>
     */
    private function headings(): array
    {
        return [
            'name',
            'category_name',
            'governorate_name',
            'area_name',
            'address',
            'description',
            'latitude',
            'longitude',
        ];
    }

    private function configureListingsSheet(Sheet $sheet): void
    {
        $sheet->setName('Listings');
        $sheet->setSheetView((new SheetView())->setFreezeRow(2));
        $sheet->setColumnWidth(28, 1, 2, 5);
        $sheet->setColumnWidth(20, 3, 4);
        $sheet->setColumnWidth(40, 6);
        $sheet->setColumnWidth(16, 7, 8);
    }

    private function configureInstructionsSheet(Sheet $sheet): void
    {
        $sheet->setName('Instructions');
        $sheet->setSheetView((new SheetView())->setFreezeRow(2));
        $sheet->setColumnWidth(22, 1);
        $sheet->setColumnWidth(20, 2);
        $sheet->setColumnWidth(70, 3);
    }

    /**
     * @return list<list<string>>
     */
    private function instructionRows(): array
    {
        return [
            ['field', 'required', 'notes'],
            ['name', 'yes', 'اسم القائمة. إذا كان الاسم موجودا بالفعل سيتم تحديث نفس السجل.'],
            ['category_name', 'yes for new rows', 'اسم التصنيف كما هو موجود داخل النظام.'],
            ['governorate_name', 'yes for new rows', 'اسم المحافظة كما هو موجود داخل النظام.'],
            ['area_name', 'yes for new rows', 'اسم المنطقة كما هو موجود داخل النظام.'],
            ['address', 'no', 'العنوان النصي للقائمة.'],
            ['description', 'no', 'وصف القائمة.'],
            ['latitude', 'no', 'خط العرض ويجب أن يكون رقما.'],
            ['longitude', 'no', 'خط الطول ويجب أن يكون رقما.'],
            ['', '', 'اترك عناوين الأعمدة كما هي ولا تغيّر أسماء الحقول في الصف الأول.'],
            ['', '', 'الملفات المدعومة للاستيراد: CSV أو XLSX فقط.'],
        ];
    }
}
