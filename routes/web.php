<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ListingImportTemplateController;
use App\Http\Controllers\UserImportTemplateController;

Route::redirect('/', '/admin');

Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');
Route::get('/listings/import-template', ListingImportTemplateController::class)->name('listings.import-template.download');
Route::get('/users/import-template', UserImportTemplateController::class)->name('users.import-template.download');

Route::get('/storage/{filename}', [FileController::class, 'show'])->name('storage.file');
