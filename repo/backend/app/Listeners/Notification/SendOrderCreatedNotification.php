<?php

namespace App\Listeners\Notification;

use App\Events\OrderCreated;
use App\Services\Notification\NotificationService;

class SendOrderCreatedNotification
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $user = $order->user;

        if (! $user) {
            return;
        }

        try {
            $this->notificationService->dispatch($user, 'order.created', [
                'order_id' => $order->id,
                'confirmation_number' => $order->confirmation_number,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
