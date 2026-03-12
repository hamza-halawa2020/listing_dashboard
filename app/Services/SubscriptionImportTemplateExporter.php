<?php

namespace App\Services;

use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;

class SubscriptionImportTemplateExporter
{
    public function downloadFilename(): string
    {
        return 'subscriptions-import-template.xlsx';
    }

    public function createTemporaryFile(): string
    {
        $path = storage_path('app/private/' . Str::uuid() . '.xlsx');

        $writer = new Writer();
        $writer->setCreator(config('app.name', 'Laravel'));
        $writer->openToFile($path);

        try {
            $subscriptionsSheet = $writer->getCurrentSheet();
            $this->configureSubscriptionsSheet($subscriptionsSheet);
            $writer->addRow(Row::fromValues($this->headings()));
            $writer->addRow(Row::fromValues($this->exampleRow()));

            $instructionsSheet = $writer->addNewSheetAndMakeItCurrent();
            $this->configureInstructionsSheet($instructionsSheet);

            foreach ($this->instructionRows() as $row) {
                $writer->addRow(Row::fromValues($row));
            }

            $writer->setCurrentSheet($subscriptionsSheet);
        } catch (\Throwable $exception) {
            $writer->close();

            if (is_file($path)) {
                @unlink($path);
            }

            throw new RuntimeException('Could not generate the subscriptions import template.', previous: $exception);
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
            'user_phone',
            'subscription_plan_code',
            'membership_card_number',
            'starts_at',
            'ends_at',
        ];
    }

    private function configureSubscriptionsSheet(Sheet $sheet): void
    {
        $sheet->setName('Subscriptions');
        $sheet->setSheetView((new SheetView())->setFreezeRow(2));
        $sheet->setColumnWidth(24, 1, 2, 3);
        $sheet->setColumnWidth(18, 4, 5);
    }

    private function configureInstructionsSheet(Sheet $sheet): void
    {
        $sheet->setName('Instructions');
        $sheet->setSheetView((new SheetView())->setFreezeRow(2));
        $sheet->setColumnWidth(24, 1);
        $sheet->setColumnWidth(18, 2);
        $sheet->setColumnWidth(72, 3);
    }

    /**
     * @return list<list<string>>
     */
    private function instructionRows(): array
    {
        return [
            ['field', 'required', 'notes'],
            ['user_phone', 'yes', 'Matched against the user phone number exactly. Format phone cells as text to preserve leading zeros.'],
            ['subscription_plan_code', 'yes', 'Matched against subscription plan code exactly, case-insensitive.'],
            ['membership_card_number', 'optional', 'If provided and already exists, the subscription will be updated using this card number.'],
            ['starts_at', 'yes', 'Subscription start date, for example 2026-03-12.'],
            ['ends_at', 'yes', 'Subscription end date, for example 2027-03-11.'],
            ['', '', 'The first row in the main sheet is only an example to show the date format, and the importer will ignore it automatically.'],
            ['', '', 'status is set automatically to active.'],
            ['', '', 'If membership_card_number is blank, updates fall back to user_phone + subscription_plan_code + starts_at.'],
        ];
    }

    /**
     * @return list<string>
     */
    private function exampleRow(): array
    {
        return [
            '01XXXXXXXXX',
            'IG',
            'IG00011234',
            '2026-03-12',
            '2027-03-11',
        ];
    }
}
