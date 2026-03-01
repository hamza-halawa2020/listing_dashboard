<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function show($filename)
    {
        if (!Storage::exists($filename)) {
            abort(404);
        }

        $file = Storage::get($filename);
        $type = Storage::mimeType($filename);

        return response($file, 200)->header('Content-Type', $type);
    }
}
