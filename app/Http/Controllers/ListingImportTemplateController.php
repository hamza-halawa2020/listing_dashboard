<?php

namespace App\Http\Controllers;

use App\Services\ListingImportTemplateExporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListingImportTemplateController extends Controller
{
    public function __invoke(ListingImportTemplateExporter $exporter): BinaryFileResponse
    {
        return response()
            ->download(
                $exporter->createTemporaryFile(),
                $exporter->downloadFilename(),
            )
            ->deleteFileAfterSend(true);
    }
}
