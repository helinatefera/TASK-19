<?php

namespace App\Listeners\Notification;

use App\Events\RefundApproved;
use App\Events\RefundRejected;
use App\Services\Notification\NotificationService;

class SendRefundDecisionNotification
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function handle(object $event): void
    {
        $refundRequest = $event->refundRequest;
        $requester = $refundRequest->requester;

        if (! $requester) {
            return;
        }

        [$templateKey, $data] = match (true) {
            $event instanceof RefundApproved => [
                'refund.approved',
                [
                    'refund_request_id' => $refundRequest->id,
                    'order_id' => $refundRequest->order_id,
                    'refund_amount' => $refundRequest->refund_amount,
                    'confirmation_number' => $refundRequest->order?->confirmation_number,
                ],
            ],
            $event instanceof RefundRejected => [
                'refund.rejected',
                [
                    'refund_request_id' => $refundRequest->id,
                    'order_id' => $refundRequest->order_id,
                    'confirmation_number' => $refundRequest->order?->confirmation_number,
                ],
            ],
            default => [null, []],
        };

        if ($templateKey) {
            $this->notificationService->dispatch($requester, $templateKey, $data);
        }
    }
}
