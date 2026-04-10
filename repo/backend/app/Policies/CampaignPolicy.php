<?php

namespace App\Policies;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    /**
     * Anyone may browse campaigns.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Anyone may view a single campaign.
     */
    public function view(?User $user, Campaign $campaign): bool
    {
        return true;
    }

    /**
     * Only users with the campaigns.create permission may create.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('campaigns.create');
    }

    /**
     * The creator may update while the campaign is still a draft, or an admin
     * can update regardless of status.
     */
    public function update(User $user, Campaign $campaign): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->id === $campaign->creator_id
            && $campaign->status === CampaignStatus::Draft;
    }

    /**
     * The creator may submit a draft campaign for review.
     */
    public function submit(User $user, Campaign $campaign): bool
    {
        return $user->id === $campaign->creator_id
            && $campaign->status === CampaignStatus::Draft;
    }

    /**
     * Moderators / staff with the campaigns.approve permission may approve a
     * campaign that is pending review.
     */
    public function approve(User $user, Campaign $campaign): bool
    {
        return $user->hasPermission('campaigns.approve')
            && $campaign->status === CampaignStatus::PendingReview;
    }

    /**
     * Same gate as approve – the reviewer may reject instead.
     */
    public function reject(User $user, Campaign $campaign): bool
    {
        return $user->hasPermission('campaigns.approve')
            && $campaign->status === CampaignStatus::PendingReview;
    }

    /**
     * Moderators/admins with campaigns.approve may close concluded campaigns.
     */
    public function close(User $user, Campaign $campaign): bool
    {
        return $user->hasPermission('campaigns.approve')
            && in_array($campaign->status, [
                CampaignStatus::Success,
                CampaignStatus::Failure,
            ]);
    }

    /**
     * Users with the campaigns.approve permission may toggle visibility.
     */
    public function toggleVisibility(User $user, Campaign $campaign): bool
    {
        return $user->hasPermission('campaigns.approve');
    }
}
