<?php

namespace App\Console\Commands;

use App\Services\Review\ReviewService;
use Illuminate\Console\Command;

class PublishPendingReviews extends Command
{
    protected $signature = 'reviews:publish-pending';

    protected $description = 'Publish reviews that have passed their visibility delay';

    public function handle(ReviewService $reviewService): int
    {
        $count = $reviewService->publishPendingReviews();

        $this->info("Published {$count} pending review(s).");

        return self::SUCCESS;
    }
}
