<?php

namespace App\Console\Commands;

use App\Services\Campaign\CampaignLifecycleService;
use Illuminate\Console\Command;

class TransitionCampaignStatuses extends Command
{
    protected $signature = 'campaigns:transition-expired';

    protected $description = 'Transition expired campaigns to success or failure based on funding goals';

    public function handle(CampaignLifecycleService $campaignLifecycleService): int
    {
        $count = $campaignLifecycleService->transitionExpired();

        $this->info("Transitioned {$count} expired campaign(s).");

        return self::SUCCESS;
    }
}
