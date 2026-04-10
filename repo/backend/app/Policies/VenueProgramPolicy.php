<?php

namespace App\Policies;

use App\Enums\CampaignStatus;
use App\Models\User;
use App\Models\VenueProgram;

class VenueProgramPolicy
{
    /**
     * Anyone may browse venue programs.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Anyone may view a single venue program.
     */
    public function view(?User $user, VenueProgram $program): bool
    {
        return true;
    }

    /**
     * Only users with the programs.create permission may create.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('programs.create');
    }

    /**
     * Only users with the programs.create permission may update.
     */
    public function update(User $user, VenueProgram $program): bool
    {
        return $user->hasPermission('programs.create');
    }

    /**
     * Submit a draft program for review.
     */
    public function submit(User $user, VenueProgram $program): bool
    {
        return $user->hasPermission('programs.create')
            && $program->status === CampaignStatus::Draft;
    }

    /**
     * Approve a program that is pending review.
     */
    public function approve(User $user, VenueProgram $program): bool
    {
        return $user->hasPermission('programs.approve')
            && $program->status === CampaignStatus::PendingReview;
    }

    /**
     * Toggle visibility of a venue program.
     */
    public function toggleVisibility(User $user, VenueProgram $program): bool
    {
        return $user->hasPermission('programs.approve');
    }
}
