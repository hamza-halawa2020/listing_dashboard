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
    public function downloadFilename(): string
    {
        return 'listings-import-template.xlsx';
    }

    public function createTemporaryFile(): string
    {
        $path = storage_path('app/private/' . Str::uuid() . '.xlsx');

        $writer = new Writer();
        $writer->setCreator(config('app.name', 'Laravel'));
        $writer->openToFile($path);

        try {
            $listingsSheet = $writer->getCurrentSheet();
            $this->configureListingsSheet($listingsSheet);
            $writer->addRow(Row::fromValues($this->headings()));

            $instructionsSheet = $writer->addNewSheetAndMakeItCurrent();
            $this->configureInstructionsSheet($instructionsSheet);

            foreach ($this->instructionRows() as $row) {
                $writer->addRow(Row::fromValues($row));
            }

            $writer->setCurrentSheet($listingsSheet);
        } catch (\Throwable $exception) {
            $writer->close();

            if (is_file($path)) {
                @unlink($path);
            }

            throw new RuntimeException('Could not generate the listings import template.', previous: $exception);
        }

        $writer->close();

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
