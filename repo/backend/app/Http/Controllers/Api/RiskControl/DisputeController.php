<?php

namespace App\Http\Controllers\Api\RiskControl;

use App\Enums\DisputeStatus;
use App\Http\Controllers\Api\BaseController;
use App\Models\Dispute;
use App\Models\DisputeDecision;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends BaseController
{
    /**
     * GET /api/disputes
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Dispute::class);

        $user = $request->user();

        $query = Dispute::query()->with(['order', 'initiator', 'arbitrator']);

        // Users with arbitrate permission see all; others see only disputes they're party to.
        if (! $user->hasPermission('disputes.arbitrate')) {
            $query->where(function ($q) use ($user) {
                $q->where('initiated_by', $user->id)
                  ->orWhere('against_user_id', $user->id);
            });
        }

        return $this->paginated($query->latest());
    }

    /**
     * GET /api/disputes/{dispute}
     */
    public function show(Request $request, Dispute $dispute): JsonResponse
    {
        $this->authorize('view', $dispute);

        $dispute->load(['order', 'initiator', 'arbitrator', 'decisions']);

        return $this->success($dispute);
    }

    /**
     * POST /api/orders/{order}/disputes
     */
    public function store(Request $request, Order $order): JsonResponse
    {
        $this->authorize('create', [Dispute::class, $order]);

        $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $dispute = Dispute::create([
            'order_id' => $order->id,
            'initiated_by' => $request->user()->id,
            'against_user_id' => $order->campaign?->creator_id,
            'status' => DisputeStatus::Open,
            'reason' => $request->input('reason'),
        ]);

        return $this->success($dispute, 201);
    }

    /**
     * POST /api/disputes/{dispute}/assign
     */
    public function assign(Request $request, Dispute $dispute): JsonResponse
    {
        $this->authorize('assign', $dispute);

        $request->validate([
            'assigned_to' => 'required|integer|exists:users,id',
        ]);

        $dispute->update([
            'assigned_to' => $request->input('assigned_to'),
            'assigned_at' => now(),
            'status' => DisputeStatus::UnderReview,
        ]);

        return $this->success($dispute->refresh());
    }

    /**
     * POST /api/disputes/{dispute}/decide
     */
    public function decide(Request $request, Dispute $dispute): JsonResponse
    {
        $this->authorize('decide', $dispute);

        $request->validate([
            'decision' => 'required|string|max:2000',
            'reasoning' => 'required|string|max:5000',
            'action_taken' => 'sometimes|string|max:2000',
        ]);

        $decision = DisputeDecision::create([
            'dispute_id' => $dispute->id,
            'decided_by' => $request->user()->id,
            'decision' => $request->input('decision'),
            'reasoning' => $request->input('reasoning'),
            'action_taken' => $request->input('action_taken'),
        ]);

        $dispute->update([
            'status' => DisputeStatus::Resolved,
            'resolved_at' => now(),
        ]);

        \App\Events\DisputeDecided::dispatch($dispute, $decision);

        return $this->success([
            'dispute' => $dispute->refresh(),
            'decision' => $decision,
        ]);
    }
}
