<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionImportTemplateExporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubscriptionImportTemplateController extends Controller
{
    public function __invoke(SubscriptionImportTemplateExporter $exporter): BinaryFileResponse
    {
        return response()
            ->download(
                $exporter->createTemporaryFile(),
                $exporter->downloadFilename(),
            )
            ->deleteFileAfterSend(true);
    }
}
