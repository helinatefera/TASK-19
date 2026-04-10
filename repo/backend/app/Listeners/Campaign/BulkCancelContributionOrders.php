<?php

namespace App\Listeners\Campaign;

use App\Enums\OrderStatus;
use App\Events\CampaignFailed;

class BulkCancelContributionOrders
{
    public function handle(CampaignFailed $event): void
    {
        $campaign = $event->campaign;

        $campaign->orders()
            ->where('status', OrderStatus::Confirmed)
            ->each(function ($order) {
                $order->update([
                    'status' => OrderStatus::Cancelled,
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'Campaign failed to reach funding goal.',
                ]);
            });
    }
}
