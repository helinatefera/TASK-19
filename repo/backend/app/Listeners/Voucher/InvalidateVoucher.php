<?php

namespace App\Listeners\Voucher;

use App\Enums\VoucherStatus;
use App\Events\OrderRefunded;
use App\Models\Voucher;

class InvalidateVoucher
{
    public function handle(OrderRefunded $event): void
    {
        Voucher::where('order_id', $event->order->id)
            ->where('status', VoucherStatus::Active)
            ->update([
                'status' => VoucherStatus::Revoked,
                'revoked_at' => now(),
            ]);
    }
}
