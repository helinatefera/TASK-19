<?php

namespace App\Services\Campaign;

use App\Enums\CampaignStatus;
use App\Events\CampaignApproved;
use App\Events\CampaignFailed;
use App\Events\CampaignRejected;
use App\Models\Campaign;
use App\Models\User;
use RuntimeException;

class CampaignLifecycleService
{
    public function submitForReview(Campaign $campaign): Campaign
    {
        if ($campaign->status !== CampaignStatus::Draft) {
            throw new RuntimeException('Only draft campaigns can be submitted for review.');
        }

        $campaign->update([
            'status' => CampaignStatus::PendingReview,
        ]);

        return $campaign->refresh();
    }

    public function approve(Campaign $campaign, User $moderator, ?string $notes = null): Campaign
    {
        if ($campaign->status !== CampaignStatus::PendingReview) {
            throw new RuntimeException('Only campaigns pending review can be approved.');
        }

        $campaign->update([
            'status' => CampaignStatus::Fundraising,
            'reviewed_by' => $moderator->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        $campaign->refresh();

        CampaignApproved::dispatch($campaign, $moderator);

        return $campaign;
    }

    public function reject(Campaign $campaign, User $moderator, string $notes): Campaign
    {
        if ($campaign->status !== CampaignStatus::PendingReview) {
            throw new RuntimeException('Only campaigns pending review can be rejected.');
        }

        $campaign->update([
            'status' => CampaignStatus::Draft,
            'reviewed_by' => $moderator->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        $campaign->refresh();

        CampaignRejected::dispatch($campaign, $moderator, $notes);

        return $campaign;
    }

    public function publish(Campaign $campaign): Campaign
    {
        if ($campaign->status !== CampaignStatus::Fundraising) {
            throw new RuntimeException('Only approved campaigns can be published.');
        }

        $campaign->update([
            'starts_at' => now(),
            'ends_at' => now()->addDays($campaign->duration_days ?? 30),
        ]);

        return $campaign->refresh();
    }

    public function close(Campaign $campaign, User $moderator, ?string $notes = null): Campaign
    {
        $closeable = [CampaignStatus::Success, CampaignStatus::Failure];

        if (! in_array($campaign->status, $closeable, true)) {
            throw new RuntimeException('Only successful or failed campaigns can be closed.');
        }

        $campaign->update([
            'status' => CampaignStatus::Closed,
            'reviewed_by' => $moderator->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        return $campaign->refresh();
    }

    public function transitionExpired(): int
    {
        $expired = Campaign::query()
            ->where('status', CampaignStatus::Fundraising)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expired as $campaign) {
            if ($campaign->pledged_amount >= $campaign->target_amount) {
                $campaign->update(['status' => CampaignStatus::Success]);
            } else {
                $campaign->update(['status' => CampaignStatus::Failure]);
                CampaignFailed::dispatch($campaign);
            }

            $count++;
        }

        return $count;
    }
}
