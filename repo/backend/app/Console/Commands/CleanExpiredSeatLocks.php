<?php

namespace App\Console\Commands;

use App\Services\Booking\SeatLockService;
use Illuminate\Console\Command;

class CleanExpiredSeatLocks extends Command
{
    protected $signature = 'seats:clean-expired-locks';

    protected $description = 'Release all expired seat locks';

    public function handle(SeatLockService $seatLockService): int
    {
        $count = $seatLockService->releaseExpired();

        $this->info("Released {$count} expired seat lock(s).");

        return self::SUCCESS;
    }
}
