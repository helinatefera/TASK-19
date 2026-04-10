<?php

namespace App\Listeners\Order;

use App\Events\OrderPaid;
use App\Models\Campaign;

class UpdateCampaignPledgedAmount
{
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;

        if ($order->campaign_id) {
            Campaign::where('id', $order->campaign_id)
                ->increment('pledged_amount', $event->payment->amount);
        }
    }
}
