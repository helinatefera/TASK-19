<?php

namespace App\Listeners\RiskControl;

use App\Events\AnomalyDetected;
use App\Events\RefundApproved;
use App\Events\RefundRequested;
use App\Models\Order;
use App\Services\RiskControl\AnomalyDetectionService;

class EvaluateAnomaly
{
    public function __construct(
        private readonly AnomalyDetectionService $anomalyDetectionService,
    ) {}

    public function handle(RefundApproved|RefundRequested $event): void
    {
        try {
            $refundRequest = $event->refundRequest;
            $order = $refundRequest->order ?? Order::find($refundRequest->order_id);
            $user = $order?->user;

            if (! $user) {
                return;
            }

            $flag = $this->anomalyDetectionService->checkRefundFrequency($user);

            if ($flag) {
                AnomalyDetected::dispatch($flag);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
