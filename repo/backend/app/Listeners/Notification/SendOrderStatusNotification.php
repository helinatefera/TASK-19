<?php

namespace App\Listeners\Notification;

use App\Events\OrderCancelled;
use App\Events\OrderFulfilled;
use App\Events\OrderPaid;
use App\Events\OrderRefunded;
use App\Services\Notification\NotificationService;

class SendOrderStatusNotification
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function handle(object $event): void
    {
        $order = $event->order;
        $user = $order->user;

        if (! $user) {
            return;
        }

        [$templateKey, $data] = match (true) {
            $event instanceof OrderPaid => [
                'order.paid',
                [
                    'order_id' => $order->id,
                    'confirmation_number' => $order->confirmation_number,
                    'amount' => $event->payment->amount,
                ],
            ],
            $event instanceof OrderCancelled => [
                'order.cancelled',
                [
                    'order_id' => $order->id,
                    'confirmation_number' => $order->confirmation_number,
                    'reason' => $event->reason,
                ],
            ],
            $event instanceof OrderRefunded => [
                'order.refunded',
                [
                    'order_id' => $order->id,
                    'confirmation_number' => $order->confirmation_number,
                    'refund_amount' => $event->refundRequest->refund_amount,
                    'amount' => $event->refundRequest->refund_amount,
                ],
            ],
            $event instanceof OrderFulfilled => [
                'order.fulfilled',
                [
                    'order_id' => $order->id,
                    'confirmation_number' => $order->confirmation_number,
                ],
            ],
            default => [null, []],
        };

        if ($templateKey) {
            $this->notificationService->dispatch($user, $templateKey, $data);
        }
    }
}
