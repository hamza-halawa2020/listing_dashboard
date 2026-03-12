<?php

namespace App\Http\Controllers;

use App\Services\UserImportTemplateExporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserImportTemplateController extends Controller
{
    public function __invoke(UserImportTemplateExporter $exporter): BinaryFileResponse
    {
        return response()
            ->download(
                $exporter->createTemporaryFile(),
                $exporter->downloadFilename(),
            )
            ->deleteFileAfterSend(true);
    }
}
