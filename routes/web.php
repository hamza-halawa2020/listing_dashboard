<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\FileController;

Route::redirect('/', '/admin');

Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');

Route::get('/storage/{filename}', [FileController::class, 'show'])->name('storage.file');
