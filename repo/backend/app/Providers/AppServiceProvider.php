<?php

namespace App\Providers;

use App\Models\AfterSalesRequest;
use App\Models\Campaign;
use App\Models\Dispute;
use App\Models\Notification;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\Review;
use App\Models\VenueProgram;
use App\Models\Voucher;
use App\Policies\AfterSalesRequestPolicy;
use App\Policies\CampaignPolicy;
use App\Policies\DisputePolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OrderPolicy;
use App\Policies\RefundRequestPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\VenueProgramPolicy;
use App\Policies\VoucherPolicy;
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
        Gate::policy(Campaign::class, CampaignPolicy::class);
        Gate::policy(VenueProgram::class, VenueProgramPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Voucher::class, VoucherPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
        Gate::policy(Dispute::class, DisputePolicy::class);
        Gate::policy(RefundRequest::class, RefundRequestPolicy::class);
        Gate::policy(AfterSalesRequest::class, AfterSalesRequestPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
    }
}
