<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatConversationController;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ImpactStatsController;
use App\Http\Controllers\Api\ListingApplicationController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostCommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PriceRequestController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SubscriptionCheckController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PointSettingsController;
use App\Http\Controllers\Api\PointsController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\ForgotPasswordController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Forgot password
Route::post('/forgot-password/send-code', [ForgotPasswordController::class, 'sendCode']);
Route::post('/forgot-password/verify-code', [ForgotPasswordController::class, 'verifyCode']);
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/all-categories', [CategoryController::class, 'withOthers']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

Route::get('/locations', [LocationController::class, 'index']);
Route::get('/locations/{id}', [LocationController::class, 'show']);

Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index']);
Route::get('/subscription-plans/{id}', [SubscriptionPlanController::class, 'show']);
Route::get('/impact-stats', ImpactStatsController::class);

Route::get('/settings', [SettingController::class, 'index']);

// Point calculation (public)
Route::get('/points/calculate', [PointSettingsController::class, 'calculatePoints']);
Route::get('/points/calculate-egp', [PointSettingsController::class, 'calculateEgp']);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::post('/posts/{post}/comments', [PostCommentController::class, 'store']);

Route::get('/comments', [CommentController::class, 'index']);

Route::get('/reviews', [ReviewController::class, 'index']);
Route::post('/reviews', [ReviewController::class, 'store']);

Route::post('/price-requests', [PriceRequestController::class, 'store']);

Route::post('/contacts', [ContactController::class, 'store']);
Route::post('/check-subscription', [SubscriptionCheckController::class, 'check']);
Route::post('/listing-applications', [ListingApplicationController::class, 'store']);

Route::get('/point-settings', [PointSettingsController::class, 'index']);

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update']);
    Route::post('/profile/family-members', [ProfileController::class, 'storeFamilyMember']);
    Route::match(['put', 'patch'], '/profile/family-members/{id}', [ProfileController::class, 'updateFamilyMember']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markRead']);

    // Listings routes (subscription-based access)
    Route::get('/listings', [ListingController::class, 'index']);
    Route::get('/listings/{id}', [ListingController::class, 'show']);

    // Subscriptions routes
    Route::post('/payments', [PaymentController::class, 'store']);

    // Point settings (admin only - you may want to add middleware for admin role)
    Route::put('/point-settings', [PointSettingsController::class, 'update']);
    Route::get('/point-settings/history', [PointSettingsController::class, 'history']);

    // Referral
    Route::get('/referral', [ReferralController::class, 'show']);

    // Points summary
    Route::get('/points/summary', [PointsController::class, 'summary']);
    Route::get('/chat/contacts', [ChatConversationController::class, 'contacts']);
    Route::get('/chat/summary', [ChatConversationController::class, 'summary']);
    Route::get('/chat/conversations', [ChatConversationController::class, 'index']);
    Route::post('/chat/conversations', [ChatConversationController::class, 'store']);
    Route::post('/chat/conversations/{chatConversation}/read', [ChatConversationController::class, 'markRead']);
    Route::get('/chat/conversations/{chatConversation}/messages', [ChatMessageController::class, 'index']);
    Route::post('/chat/conversations/{chatConversation}/messages', [ChatMessageController::class, 'store']);

});
