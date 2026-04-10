<?php

namespace App\Listeners\RiskControl;

use App\Enums\AnomalyType;
use App\Events\AnomalyDetected;
use App\Events\ChargebackRecorded;
use App\Services\RiskControl\AnomalyDetectionService;

class FlagAnomaly
{
    public function __construct(
        private readonly AnomalyDetectionService $anomalyDetectionService,
    ) {}

    public function handle(ChargebackRecorded $event): void
    {
        $order = $event->order;
        $user = $order->user;

        if (! $user) {
            return;
        }

        $flag = $this->anomalyDetectionService->flagAnomaly(
            $user,
            AnomalyType::Chargeback,
            [
                'order_id' => $order->id,
                'recorded_by' => $event->recorder->id,
            ]
        );

        AnomalyDetected::dispatch($flag);
    }
}
