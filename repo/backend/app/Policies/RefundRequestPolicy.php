<?php

namespace App\Policies;

use App\Models\RefundRequest;
use App\Models\User;

class RefundRequestPolicy
{
    /**
     * Staff with orders.refund_approve may approve a refund request.
     */
    public function approve(User $user, RefundRequest $request): bool
    {
        return $user->hasPermission('orders.refund_approve');
    }

    /**
     * Staff with orders.refund_approve may reject a refund request.
     */
    public function reject(User $user, RefundRequest $request): bool
    {
        return $user->hasPermission('orders.refund_approve');
    }
}
