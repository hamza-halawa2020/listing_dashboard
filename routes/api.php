<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WorkSampleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ListingsController;
use App\Http\Controllers\Api\SubscriptionsController;


// Public routes
Route::get('/settings', [SettingController::class, 'index']);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);


Route::get('/reviews', [ReviewController::class, 'index']);

Route::post('/contacts', [ContactController::class, 'store']);

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Listings routes (subscription-based access)
    Route::get('/listings', [ListingsController::class, 'index']);
    Route::get('/listings/{listing}', [ListingsController::class, 'show']);

    // Subscriptions routes
    Route::prefix('/subscriptions')->group(function () {
        Route::get('/current', [SubscriptionsController::class, 'current']);
        Route::get('/plans', [SubscriptionsController::class, 'plans']);
        Route::post('/', [SubscriptionsController::class, 'store']);
        Route::post('/{subscription}/cancel', [SubscriptionsController::class, 'cancel']);
    });


});