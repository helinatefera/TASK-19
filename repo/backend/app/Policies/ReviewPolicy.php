<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Anyone may browse reviews.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Anyone may view a single review.
     */
    public function view(?User $user): bool
    {
        return true;
    }

    /**
     * A user may create a review for a fulfilled order when they are the order
     * owner or the campaign creator, and they hold the reviews.create permission.
     */
    public function create(User $user, Order $order): bool
    {
        if (! $user->hasPermission('reviews.create')) {
            return false;
        }

        if ($order->status !== OrderStatus::Fulfilled) {
            return false;
        }

        return $user->id === $order->user_id
            || $user->id === $order->campaign?->creator_id;
    }
}
