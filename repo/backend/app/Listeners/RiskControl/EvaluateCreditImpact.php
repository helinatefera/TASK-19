<?php

namespace App\Listeners\RiskControl;

use App\Events\ChargebackRecorded;
use App\Events\NoShowDetected;
use App\Events\OrderCancelled;
use App\Services\RiskControl\CreditScoringService;

class EvaluateCreditImpact
{
    public function __construct(
        private readonly CreditScoringService $creditScoringService,
    ) {}

    public function handle(object $event): void
    {
        $order = $event->order;
        $user = $order->user;

        if (! $user) {
            return;
        }

        match (true) {
            $event instanceof OrderCancelled => $this->creditScoringService->recordRefund($user),
            $event instanceof NoShowDetected => $this->creditScoringService->recordNoShow($user),
            $event instanceof ChargebackRecorded => $this->creditScoringService->recordChargeback($user),
            default => null,
        };
    }
}
