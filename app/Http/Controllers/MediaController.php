<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file'   => ['required', 'image', 'max:5120'],
            'format' => ['nullable', 'string', 'in:filename,path,url'],
        ]);

        $path   = $request->file('file')->store('media', 'public');
        $disk   = Storage::disk('public');
        $url    = $disk->url($path);
        $format = $request->input('format', 'filename');

        $saveValue = match ($format) {
            'url'   => $url,
            'path'  => $path,
            default => basename($path),
        };

        return response()->json([
            'saveValue' => $saveValue,
            'url'       => $url,
            'path'      => $path,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $search  = strtolower(trim($request->input('search', '')));
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $format  = $request->input('format', 'path');

        $disk  = Storage::disk('public');
        $files = $disk->allFiles('media');

        // Filter images only
        $images = array_filter($files, function ($file) use ($search) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                return false;
            }
            if ($search && !str_contains(strtolower(basename($file)), $search)) {
                return false;
            }
            return true;
        });

        // Sort newest first
        usort($images, fn($a, $b) => $disk->lastModified($b) <=> $disk->lastModified($a));

        $total  = count($images);
        $sliced = array_slice($images, ($page - 1) * $perPage, $perPage);

        $result = array_map(function ($file) use ($disk, $format) {
            $url = $disk->url($file);
            return [
                'saveValue' => match ($format) {
                    'url'      => $url,
                    'filename' => basename($file),
                    default    => $file,
                },
                'url'  => $url,
                'name' => basename($file),
            ];
        }, array_values($sliced));

        return response()->json([
            'data'     => $result,
            'total'    => $total,
            'page'     => $page,
            'lastPage' => (int) ceil($total / $perPage),
        ]);
    }
}
