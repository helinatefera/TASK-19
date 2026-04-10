<?php

namespace App\Policies;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;

class DisputePolicy
{
    /**
     * Any authenticated user may list disputes.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * The initiator, the respondent, or anyone who can arbitrate may view.
     */
    public function view(User $user, Dispute $dispute): bool
    {
        return $user->id === $dispute->initiated_by
            || $user->id === $dispute->against_user_id
            || $user->hasPermission('disputes.arbitrate');
    }

    /**
     * Users with disputes.create may open a dispute.
     */
    public function create(User $user, Order $order): bool
    {
        return $user->hasPermission('disputes.create')
            && $user->id === $order->user_id;
    }

    /**
     * Users with disputes.arbitrate may assign an arbitrator.
     */
    public function assign(User $user, Dispute $dispute): bool
    {
        return $user->hasPermission('disputes.arbitrate');
    }

    /**
     * Only the assigned arbitrator who holds disputes.arbitrate may decide.
     */
    public function decide(User $user, Dispute $dispute): bool
    {
        return $user->hasPermission('disputes.arbitrate')
            && $user->id === $dispute->assigned_to;
    }
}
