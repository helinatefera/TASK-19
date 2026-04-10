<?php

namespace App\Events;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public User $reviewer,
    ) {}
}
