<?php

namespace App\Console\Commands;

use App\Services\RiskControl\AnomalyDetectionService;
use Illuminate\Console\Command;

class DetectDuplicateDevices extends Command
{
    protected $signature = 'risk:detect-duplicate-devices';

    protected $description = 'Detect users sharing device fingerprints and flag anomalies';

    public function handle(AnomalyDetectionService $anomalyDetectionService): int
    {
        $flagged = $anomalyDetectionService->detectDuplicateDevices();

        $this->info("Flagged {$flagged->count()} duplicate device anomaly(ies).");

        return self::SUCCESS;
    }
}
