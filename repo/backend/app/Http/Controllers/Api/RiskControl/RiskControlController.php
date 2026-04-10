<?php

namespace App\Http\Controllers\Api\RiskControl;

use App\Http\Controllers\Api\BaseController;
use App\Models\AnomalyFlag;
use App\Models\CreditScore;
use App\Models\Order;
use App\Models\User;
use App\Services\RiskControl\AnomalyDetectionService;
use App\Services\RiskControl\CreditScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiskControlController extends BaseController
{
    public function __construct(
        private readonly CreditScoringService $creditScoringService,
        private readonly AnomalyDetectionService $anomalyDetectionService,
    ) {}

    /**
     * GET /api/risk/credit-scores/{user}
     */
    public function creditScore(User $user): JsonResponse
    {
        $score = $this->creditScoringService->evaluate($user);

        return $this->success($score);
    }

    /**
     * GET /api/risk/credit-scores
     */
    public function creditScoreList(): JsonResponse
    {
        $query = CreditScore::query()
            ->with('user')
            ->latest();

        return $this->paginated($query);
    }

    /**
     * GET /api/risk/anomalies
     */
    public function anomalies(): JsonResponse
    {
        $query = AnomalyFlag::query()
            ->with('user')
            ->latest();

        return $this->paginated($query);
    }

    /**
     * POST /api/risk/anomalies/{anomalyFlag}/resolve
     */
    public function resolveAnomaly(Request $request, AnomalyFlag $anomalyFlag): JsonResponse
    {
        if ($anomalyFlag->resolved_at !== null) {
            return $this->error('This anomaly has already been resolved.', 422);
        }

        $anomalyFlag->update([
            'resolved_at' => now(),
            'resolved_by' => $request->user()->id,
        ]);

        return $this->success($anomalyFlag->refresh());
    }

    /**
     * POST /api/risk/chargebacks
     */
    public function recordChargeback(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->input('order_id'));
        $user = $order->user;

        $this->creditScoringService->recordChargeback($user);

        \App\Events\ChargebackRecorded::dispatch($order, $request->user());

        return $this->success([
            'message' => 'Chargeback recorded.',
            'credit_score' => $user->creditScore->refresh(),
        ], 201);
    }
}
