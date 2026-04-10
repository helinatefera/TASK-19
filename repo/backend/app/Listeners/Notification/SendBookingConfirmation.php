<?php

namespace App\Listeners\Notification;

use App\Events\BookingConfirmed;
use App\Services\Notification\NotificationService;

class SendBookingConfirmation
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function handle(BookingConfirmed $event): void
    {
        $order = $event->order;
        $user = $order->user;

        if (! $user) {
            return;
        }

        $this->notificationService->dispatch($user, 'booking.confirmed', [
            'order_id' => $order->id,
            'confirmation_number' => $order->confirmation_number,
            'seat_quantity' => $order->seat_quantity,
            'event_date' => $order->timeSlot?->starts_at?->toIso8601String() ?? '',
        ]);
    }
}
