<?php

namespace App\Services;

use App\Models\PriceRequest;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use RuntimeException;

class PriceRequestPdfService
{
    public function generate(PriceRequest $priceRequest): string
    {
        if (! class_exists(Mpdf::class)) {
            throw new RuntimeException('PDF package not installed. Run: composer require mpdf/mpdf');
        }

        $html = View::make('price-requests.pdf', [
            'priceRequest' => $priceRequest,
            'isArabic' => app()->getLocale() === 'ar',
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_right' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'tempDir' => storage_path('app/mpdf-temp'),
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $mpdf->SetDirectionality(app()->getLocale() === 'ar' ? 'rtl' : 'ltr');
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', Destination::STRING_RETURN);
    }
}
