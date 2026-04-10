<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Api\BaseController;
use App\Models\LogisticsMilestone;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogisticsMilestoneController extends BaseController
{
    /**
     * GET /api/orders/{order}/milestones
     */
    public function index(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $milestones = $order->logisticsMilestones()
            ->orderBy('sort_order')
            ->get();

        return $this->success($milestones);
    }

    /**
     * POST /api/orders/{order}/milestones
     */
    public function store(Request $request, Order $order): JsonResponse
    {
        $this->authorize('fulfill', $order);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'sometimes|string|max:2000',
            'status' => 'sometimes|string|in:pending,in_progress,completed',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $milestone = $order->logisticsMilestones()->create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status', 'pending'),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return $this->success($milestone, 201);
    }

    /**
     * PUT /api/milestones/{milestone}
     */
    public function update(Request $request, LogisticsMilestone $milestone): JsonResponse
    {
        $order = $milestone->order;
        $this->authorize('fulfill', $order);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:2000',
            'status' => 'sometimes|string|in:pending,in_progress,completed',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $data = $request->only(['title', 'description', 'status', 'sort_order']);

        if (isset($data['status']) && $data['status'] === 'completed' && $milestone->status !== 'completed') {
            $data['completed_at'] = now();
        }

        $milestone->update($data);

        return $this->success($milestone->refresh());
    }
}
