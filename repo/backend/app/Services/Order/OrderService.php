<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Events\OrderCancelled;
use App\Events\OrderFulfilled;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\RewardTier;
use App\Models\SeatLock;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\BusinessParameter;
use App\Services\Booking\BookingService;
use RuntimeException;

class OrderService
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function createContributionOrder(User $user, Campaign $campaign, RewardTier $tier, string $requestKey): Order
    {
        // Idempotent: return existing order if request_key already used
        $existing = Order::where('request_key', $requestKey)->first();
        if ($existing) {
            return $existing;
        }

        $order = Order::create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'reward_tier_id' => $tier->id,
            'request_key' => $requestKey,
            'confirmation_number' => $this->bookingService->generateConfirmationNumber(),
            'order_type' => OrderType::Contribution,
            'amount' => $tier->price,
            'currency' => $campaign->currency,
            'status' => OrderStatus::Confirmed,
        ]);

        \App\Events\OrderCreated::dispatch($order);

        return $order;
    }

    public function createReservationOrder(User $user, TimeSlot $timeSlot, SeatLock $lock, string $requestKey): Order
    {
        return $this->bookingService->confirm($lock, [
            'user_id' => $user->id,
            'time_slot_id' => $timeSlot->id,
            'request_key' => $requestKey,
        ]);
    }

    public function cancel(Order $order, string $reason): Order
    {
        if (! $order->canCancel()) {
            throw new RuntimeException('This order cannot be cancelled at this time.');
        }

        $order->update([
            'status' => OrderStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        $order->refresh();

        OrderCancelled::dispatch($order, $reason);

        return $order;
    }

    public function fulfill(Order $order): Order
    {
        if ($order->status !== OrderStatus::Confirmed) {
            throw new RuntimeException('Only confirmed orders can be fulfilled.');
        }

        $order->update([
            'status' => OrderStatus::Fulfilled,
            'fulfilled_at' => now(),
            'refund_deadline' => now()->addDays(
                (int) (BusinessParameter::where('key', 'refund_window_days')->first()?->getTypedValue() ?? 14)
            ),
        ]);

        $order->refresh();

        OrderFulfilled::dispatch($order);

        return $order;
    }
}
