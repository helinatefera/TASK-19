<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Voucher;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Voucher $voucher,
        public Order $order,
    ) {}
}
