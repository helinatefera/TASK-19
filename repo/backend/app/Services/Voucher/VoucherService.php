<?php

namespace App\Services\Voucher;

use App\Enums\VoucherStatus;
use App\Events\VoucherGenerated;
use App\Models\BusinessParameter;
use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class VoucherService
{
    public function generate(Order $order): Voucher
    {
        $expiresAt = $this->resolveExpiry($order);

        $voucher = Voucher::create([
            'order_id' => $order->id,
            'code' => $this->generateCode(),
            'status' => VoucherStatus::Active,
            'expires_at' => $expiresAt,
        ]);

        VoucherGenerated::dispatch($voucher, $order);

        return $voucher;
    }

    public function redeem(Voucher $voucher, User $staff): Voucher
    {
        return DB::transaction(function () use ($voucher, $staff) {
            /** @var Voucher $locked */
            $locked = Voucher::query()
                ->where('id', $voucher->id)
                ->lockForUpdate()
                ->first();

            if ($locked->status !== VoucherStatus::Active) {
                throw new RuntimeException('Voucher is not in a redeemable state.');
            }

            if ($locked->expires_at && $locked->expires_at->isPast()) {
                throw new RuntimeException('Voucher has expired.');
            }

            $locked->update([
                'status' => VoucherStatus::Redeemed,
                'redeemed_at' => now(),
                'redeemed_by' => $staff->id,
            ]);

            return $locked->refresh();
        });
    }

    public function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }

    private function resolveExpiry(Order $order): \Carbon\Carbon
    {
        $order->loadMissing('timeSlot');

        if ($order->timeSlot && $order->timeSlot->ends_at) {
            return $order->timeSlot->ends_at;
        }

        $param = BusinessParameter::where('key', 'voucher_expiry_days')->first();
        $days = $param ? (int) $param->getTypedValue() : 90;

        return now()->addDays($days);
    }
}
