<?php

namespace App\Listeners\Notification;

use App\Events\CampaignFailed;
use App\Services\Notification\NotificationService;

class SendCampaignFailedNotification
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function handle(CampaignFailed $event): void
    {
        $campaign = $event->campaign;

        // Notify the campaign creator
        $creator = $campaign->creator;
        if ($creator) {
            $this->notificationService->dispatch($creator, 'campaign.failed', [
                'campaign_id' => $campaign->id,
                'campaign_title' => $campaign->title,
            ]);
        }

        // Bulk notify all contributors
        $contributors = $campaign->orders()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id');

        foreach ($contributors as $contributor) {
            $this->notificationService->dispatch($contributor, 'campaign.failed.contributor', [
                'campaign_id' => $campaign->id,
                'campaign_title' => $campaign->title,
            ]);
        }
    }
}
