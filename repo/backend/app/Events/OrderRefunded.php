<?php

namespace App\Events;

use App\Models\Order;
use App\Models\RefundRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderRefunded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public RefundRequest $refundRequest,
    ) {}
}
