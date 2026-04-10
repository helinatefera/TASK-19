<?php

namespace App\Listeners\Notification;

use App\Events\VoucherGenerated;
use App\Services\Notification\NotificationService;

class SendVoucherNotification
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function handle(VoucherGenerated $event): void
    {
        $order = $event->order;
        $user = $order->user;

        if (! $user) {
            return;
        }

        $this->notificationService->dispatch($user, 'voucher.generated', [
            'voucher_code' => $event->voucher->code,
            'order_id' => $order->id,
            'confirmation_number' => $order->confirmation_number,
            'expires_at' => $event->voucher->expires_at?->toIso8601String(),
        ]);
    }
}
