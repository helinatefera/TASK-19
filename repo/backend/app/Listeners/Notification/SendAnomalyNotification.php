<?php

namespace App\Listeners\Notification;

use App\Events\AnomalyDetected;
use App\Models\User;
use App\Services\Notification\NotificationService;

class SendAnomalyNotification
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function handle(AnomalyDetected $event): void
    {
        $anomaly = $event->anomalyFlag;
        $flaggedUser = $anomaly->user;

        // Notify all moderators and admins
        $moderators = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['moderator', 'admin']);
        })->get();

        foreach ($moderators as $moderator) {
            try {
                $this->notificationService->dispatch($moderator, 'anomaly.detected', [
                    'anomaly_id' => $anomaly->id,
                    'username' => $flaggedUser?->username ?? 'Unknown',
                    'type' => $anomaly->type?->value ?? $anomaly->type ?? 'unknown',
                    'user_id' => $anomaly->user_id,
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
