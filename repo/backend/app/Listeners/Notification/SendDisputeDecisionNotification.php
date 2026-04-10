<?php

namespace App\Listeners\Notification;

use App\Events\DisputeDecided;
use App\Services\Notification\NotificationService;

class SendDisputeDecisionNotification
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function handle(DisputeDecided $event): void
    {
        $dispute = $event->dispute;
        $initiator = $dispute->initiator;

        if (! $initiator) {
            return;
        }

        $this->notificationService->dispatch($initiator, 'arbitration.decided', [
            'dispute_id' => $dispute->id,
            'decision_id' => $event->decision->id,
            'outcome' => $event->decision->outcome ?? null,
        ]);
    }
}
