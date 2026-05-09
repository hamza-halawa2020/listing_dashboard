<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ListingImportTemplateController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SubscriptionImportTemplateController;
use App\Http\Controllers\UserImportTemplateController;

Route::redirect('/', '/admin');

Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');
Route::post('/admin/media/upload', [MediaController::class, 'upload'])
    ->name('media.upload')
    ->middleware([
        'web',
        \Filament\Http\Middleware\Authenticate::class,
    ]);
Route::get('/admin/media/library', [MediaController::class, 'index'])
    ->name('media.library')
    ->middleware([
        'web',
        \Filament\Http\Middleware\Authenticate::class,
    ]);
Route::get('/listings/import-template', ListingImportTemplateController::class)->name('listings.import-template.download');
Route::get('/subscriptions/import-template', SubscriptionImportTemplateController::class)->name('subscriptions.import-template.download');
Route::get('/users/import-template', UserImportTemplateController::class)->name('users.import-template.download');

// Route::get('/storage/{filename}', [FileController::class, 'show'])->name('storage.file');


Route::get('/files/{path}', function ($path) {
    $file = storage_path('app/public/' . $path);

    if (!file_exists($file)) {
        abort(404);
    }

    return response()->file($file);
})->where('path', '.*');