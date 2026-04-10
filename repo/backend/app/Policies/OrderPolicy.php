<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Any authenticated user may list orders (scoping is handled elsewhere).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * The order owner or anyone with the orders.cancel_any permission may view.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            || $user->hasPermission('orders.cancel_any');
    }

    /**
     * Users with the orders.create permission may place orders.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('orders.create');
    }

    /**
     * The owner may cancel if the order is cancellable, or staff with
     * orders.cancel_any may cancel any order.
     */
    public function cancel(User $user, Order $order): bool
    {
        if ($user->hasPermission('orders.cancel_any')) {
            return true;
        }

        return $user->id === $order->user_id && $order->canCancel();
    }

    /**
     * Staff with orders.fulfill may fulfil an order.
     */
    public function fulfill(User $user, Order $order): bool
    {
        return $user->hasPermission('orders.fulfill');
    }

    /**
     * Staff with payments.record may record a payment against an order.
     */
    public function recordPayment(User $user, Order $order): bool
    {
        return $user->hasPermission('payments.record');
    }

    /**
     * Staff with orders.fulfill may mark attendance.
     */
    public function markAttendance(User $user, Order $order): bool
    {
        return $user->hasPermission('orders.fulfill');
    }

    /**
     * The order owner may submit a refund request when the order is eligible.
     */
    public function submitRefund(User $user, Order $order): bool
    {
        return $user->id === $order->user_id && $order->canRefund();
    }

    /**
     * The order owner may submit an after-sales request when permitted and no
     * pending requests exist.
     */
    public function submitAfterSales(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            && $user->hasPermission('after_sales.create')
            && ! $order->has_pending_after_sales
            && ! $order->has_pending_refund;
    }
}
