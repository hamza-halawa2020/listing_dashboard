<?php

namespace App\Services;

use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;

class UserImportTemplateExporter
{
    public function downloadFilename(): string
    {
        return 'users-import-template.xlsx';
    }

    public function createTemporaryFile(): string
    {
        $path = storage_path('app/private/' . Str::uuid() . '.xlsx');

        $writer = new Writer();
        $writer->setCreator(config('app.name', 'Laravel'));
        $writer->openToFile($path);

        try {
            $usersSheet = $writer->getCurrentSheet();
            $this->configureUsersSheet($usersSheet);
            $writer->addRow(Row::fromValues($this->headings()));

            $instructionsSheet = $writer->addNewSheetAndMakeItCurrent();
            $this->configureInstructionsSheet($instructionsSheet);

            foreach ($this->instructionRows() as $row) {
                $writer->addRow(Row::fromValues($row));
            }

            $writer->setCurrentSheet($usersSheet);
        } catch (\Throwable $exception) {
            $writer->close();

            if (is_file($path)) {
                @unlink($path);
            }

            throw new RuntimeException('Could not generate the users import template.', previous: $exception);
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
            'email',
            'phone',
            'national_id',
        ];
    }

    private function configureUsersSheet(Sheet $sheet): void
    {
        $sheet->setName('Users');
        $sheet->setSheetView((new SheetView())->setFreezeRow(2));
        $sheet->setColumnWidth(28, 1, 2);
        $sheet->setColumnWidth(22, 3, 4);
    }

    private function configureInstructionsSheet(Sheet $sheet): void
    {
        $sheet->setName('Instructions');
        $sheet->setSheetView((new SheetView())->setFreezeRow(2));
        $sheet->setColumnWidth(22, 1);
        $sheet->setColumnWidth(18, 2);
        $sheet->setColumnWidth(75, 3);
    }

    /**
     * @return list<list<string>>
     */
    private function instructionRows(): array
    {
        return [
            ['field', 'required', 'notes'],
            ['name', 'yes', 'اسم المستخدم.'],
            ['email', 'yes', 'البريد الإلكتروني ويجب أن يكون فريدًا. يستخدم أيضًا لتحديث المستخدم إذا كان موجودًا.'],
            ['phone', 'yes', 'رقم الهاتف ويجب أن يكون فريدًا. يفضّل تنسيق الخلية كنص حتى لا تضيع الأصفار الأولى.'],
            ['national_id', 'no', 'الرقم القومي إن وجد. يفضّل تنسيق الخلية كنص.'],
            ['', '', 'المستخدم الجديد يتم إنشاؤه تلقائيًا بدور member.'],
            ['', '', 'إذا لم يتم إرسال كلمة مرور، فسيتم استخدام رقم الهاتف ككلمة مرور افتراضية للمستخدم الجديد.'],
        ];
    }
}
