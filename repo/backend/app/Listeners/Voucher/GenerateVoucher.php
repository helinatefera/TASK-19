<?php

namespace App\Listeners\Voucher;

use App\Events\OrderPaid;
use App\Events\VoucherGenerated;
use App\Services\Voucher\VoucherService;

class GenerateVoucher
{
    public function __construct(
        private readonly VoucherService $voucherService,
    ) {}

    public function handle(OrderPaid $event): void
    {
        $voucher = $this->voucherService->generate($event->order);

        VoucherGenerated::dispatch($voucher, $event->order);
    }
}
