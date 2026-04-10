<?php

namespace App\Listeners\Notification;

use App\Events\CampaignApproved;
use App\Events\CampaignRejected;
use App\Services\Notification\NotificationService;

class SendCampaignDecisionNotification
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function handle(object $event): void
    {
        $campaign = $event->campaign;
        $creator = $campaign->creator;

        if (! $creator) {
            return;
        }

        [$templateKey, $data] = match (true) {
            $event instanceof CampaignApproved => [
                'campaign.approved',
                [
                    'campaign_id' => $campaign->id,
                    'campaign_title' => $campaign->title,
                ],
            ],
            $event instanceof CampaignRejected => [
                'campaign.rejected',
                [
                    'campaign_id' => $campaign->id,
                    'campaign_title' => $campaign->title,
                    'reason' => $event->reason,
                ],
            ],
            default => [null, []],
        };

        if ($templateKey) {
            $this->notificationService->dispatch($creator, $templateKey, $data);
        }
    }
}
