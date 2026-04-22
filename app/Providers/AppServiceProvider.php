<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\ListingApplication;
use App\Models\Payment;
use App\Models\PriceRequest;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Observers\CommentObserver;
use App\Observers\ListingApplicationObserver;
use App\Observers\PaymentObserver;
use App\Observers\PriceRequestObserver;
use App\Observers\ReviewObserver;
use App\Observers\SubscriptionObserver;
use App\Observers\SubscriptionPlanObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        Comment::observe(CommentObserver::class);
        Review::observe(ReviewObserver::class);
        PriceRequest::observe(PriceRequestObserver::class);
        Payment::observe(PaymentObserver::class);
        Subscription::observe(SubscriptionObserver::class);
        ListingApplication::observe(ListingApplicationObserver::class);
        SubscriptionPlan::observe(SubscriptionPlanObserver::class);
    }
}
