<?php

namespace App\Http\Controllers;

use App\Models\PriceRequest;
use App\Services\PriceRequestPdfService;
use Illuminate\Http\Response;

class PriceRequestShareController extends Controller
{
    public function show(PriceRequest $priceRequest): Response
    {
        return response()->view('price-requests.share', [
            'priceRequest' => $priceRequest,
        ]);
    }

    public function downloadPdf(PriceRequest $priceRequest, PriceRequestPdfService $pdfService): Response
    {
        $pdfContent = $pdfService->generate($priceRequest);
        $filename = 'price-request-' . $priceRequest->id . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

