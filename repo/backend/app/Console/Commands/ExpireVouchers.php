<?php

namespace App\Console\Commands;

use App\Enums\VoucherStatus;
use App\Models\Voucher;
use Illuminate\Console\Command;

class ExpireVouchers extends Command
{
    protected $signature = 'vouchers:expire';

    protected $description = 'Expire active vouchers that have passed their expiration date';

    public function handle(): int
    {
        $count = Voucher::query()
            ->where('status', VoucherStatus::Active)
            ->where('expires_at', '<=', now())
            ->update(['status' => VoucherStatus::Expired]);

        $this->info("Expired {$count} voucher(s).");

        return self::SUCCESS;
    }
}
