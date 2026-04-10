<?php

namespace App\Policies;

use App\Enums\VoucherStatus;
use App\Models\User;
use App\Models\Voucher;

class VoucherPolicy
{
    /**
     * Any authenticated user may list vouchers.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * The voucher's order owner or anyone with the vouchers.redeem permission
     * may view a voucher.
     */
    public function view(User $user, Voucher $voucher): bool
    {
        return $user->id === $voucher->order->user_id
            || $user->hasPermission('vouchers.redeem');
    }

    /**
     * Only users with vouchers.redeem may redeem, and only active vouchers.
     */
    public function redeem(User $user, Voucher $voucher): bool
    {
        return $user->hasPermission('vouchers.redeem')
            && $voucher->status === VoucherStatus::Active;
    }
}
