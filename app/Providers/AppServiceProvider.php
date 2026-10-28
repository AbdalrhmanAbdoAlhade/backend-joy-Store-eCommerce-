<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use App\Models\Ticket;
use App\Models\ReturnRequest;
use App\Models\Coupon;
use App\Policies\ProductPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\TicketPolicy;
use App\Policies\ReturnRequestPolicy;
use App\Policies\CouponPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // <-- أضف هذا السطر هنا

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
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(ReturnRequest::class, ReturnRequestPolicy::class);
        Gate::policy(Coupon::class, CouponPolicy::class);
    }
}
