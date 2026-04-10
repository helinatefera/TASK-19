<?php

namespace App\Console\Commands;

use App\Services\Notification\NotificationService;
use Illuminate\Console\Command;

class PruneNotifications extends Command
{
    protected $signature = 'notifications:prune';

    protected $description = 'Delete expired notifications';

    public function handle(NotificationService $notificationService): int
    {
        $count = $notificationService->pruneExpired();

        $this->info("Pruned {$count} expired notification(s).");

        return self::SUCCESS;
    }
}
