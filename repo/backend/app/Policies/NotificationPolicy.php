<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    /**
     * Only the notification owner may view it.
     */
    public function view(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Only the notification owner may mark it as read.
     */
    public function markRead(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }
}
