<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\SubscriptionsController;
use App\Http\Controllers\Api\AuthController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

Route::get('/locations', [LocationController::class, 'index']);
Route::get('/locations/{id}', [LocationController::class, 'show']);

Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index']);
Route::get('/subscription-plans/{id}', [SubscriptionPlanController::class, 'show']);

Route::get('/settings', [SettingController::class, 'index']);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);

Route::get('/reviews', [ReviewController::class, 'index']);

Route::post('/contacts', [ContactController::class, 'store']);

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Listings routes (subscription-based access)
    Route::get('/listings', [ListingController::class, 'index']);
    Route::get('/listings/{id}', [ListingController::class, 'show']);

    // Subscriptions routes
    // Route::prefix('/subscriptions')->group(function () {
    //     Route::get('/current', [SubscriptionsController::class, 'current']);
    //     Route::get('/plans', [SubscriptionsController::class, 'plans']);
    //     Route::post('/', [SubscriptionsController::class, 'store']);
    //     Route::post('/{subscription}/cancel', [SubscriptionsController::class, 'cancel']);
    // });

});