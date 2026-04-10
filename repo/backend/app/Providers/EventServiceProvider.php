<?php

namespace App\Providers;

use App\Events\AnomalyDetected;
use App\Events\BookingConfirmed;
use App\Events\CampaignApproved;
use App\Events\CampaignFailed;
use App\Events\CampaignRejected;
use App\Events\ChargebackRecorded;
use App\Events\DisputeDecided;
use App\Events\NoShowDetected;
use App\Events\OrderCancelled;
use App\Events\OrderCreated;
use App\Events\OrderFulfilled;
use App\Events\OrderPaid;
use App\Events\OrderRefunded;
use App\Events\RefundApproved;
use App\Events\RefundRejected;
use App\Events\ReviewSubmitted;
use App\Events\VoucherGenerated;
use App\Listeners\Audit\WriteAuditLog;
use App\Listeners\Campaign\BulkCancelContributionOrders;
use App\Listeners\Notification\SendAnomalyNotification;
use App\Listeners\Notification\SendBookingConfirmation;
use App\Listeners\Notification\SendCampaignDecisionNotification;
use App\Listeners\Notification\SendCampaignFailedNotification;
use App\Listeners\Notification\SendDisputeDecisionNotification;
use App\Listeners\Notification\SendOrderCreatedNotification;
use App\Listeners\Notification\SendOrderStatusNotification;
use App\Listeners\Notification\SendRefundDecisionNotification;
use App\Listeners\Notification\SendVoucherNotification;
use App\Listeners\Order\UpdateCampaignPledgedAmount;
use App\Listeners\RiskControl\EvaluateAnomaly;
use App\Listeners\RiskControl\EvaluateCreditImpact;
use App\Listeners\RiskControl\FlagAnomaly;
use App\Listeners\Voucher\GenerateVoucher;
use App\Listeners\Voucher\InvalidateVoucher;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        BookingConfirmed::class => [
            WriteAuditLog::class,
            SendBookingConfirmation::class,
        ],

        OrderCreated::class => [
            WriteAuditLog::class,
            SendOrderCreatedNotification::class,
        ],

        OrderPaid::class => [
            WriteAuditLog::class,
            SendOrderStatusNotification::class,
            UpdateCampaignPledgedAmount::class,
            GenerateVoucher::class,
        ],

        OrderCancelled::class => [
            WriteAuditLog::class,
            SendOrderStatusNotification::class,
            EvaluateCreditImpact::class,
        ],

        OrderRefunded::class => [
            WriteAuditLog::class,
            SendOrderStatusNotification::class,
            InvalidateVoucher::class,
        ],

        OrderFulfilled::class => [
            WriteAuditLog::class,
            SendOrderStatusNotification::class,
        ],

        CampaignApproved::class => [
            WriteAuditLog::class,
            SendCampaignDecisionNotification::class,
        ],

        CampaignRejected::class => [
            WriteAuditLog::class,
            SendCampaignDecisionNotification::class,
        ],

        CampaignFailed::class => [
            WriteAuditLog::class,
            SendCampaignFailedNotification::class,
            BulkCancelContributionOrders::class,
        ],

        RefundApproved::class => [
            WriteAuditLog::class,
            SendRefundDecisionNotification::class,
            EvaluateAnomaly::class,
        ],

        RefundRejected::class => [
            WriteAuditLog::class,
            SendRefundDecisionNotification::class,
        ],

        ReviewSubmitted::class => [
            WriteAuditLog::class,
        ],

        VoucherGenerated::class => [
            WriteAuditLog::class,
            SendVoucherNotification::class,
        ],

        AnomalyDetected::class => [
            WriteAuditLog::class,
            SendAnomalyNotification::class,
        ],

        ChargebackRecorded::class => [
            WriteAuditLog::class,
            EvaluateCreditImpact::class,
            FlagAnomaly::class,
        ],

        NoShowDetected::class => [
            WriteAuditLog::class,
            EvaluateCreditImpact::class,
        ],

        DisputeDecided::class => [
            WriteAuditLog::class,
            SendDisputeDecisionNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
