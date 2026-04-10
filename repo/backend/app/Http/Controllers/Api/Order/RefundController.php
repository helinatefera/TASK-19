<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Api\BaseController;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Services\Order\RefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundController extends BaseController
{
    public function __construct(
        private readonly RefundService $refundService,
    ) {}

    /**
     * POST /api/orders/{order}/refunds
     */
    public function store(Request $request, Order $order): JsonResponse
    {
        $this->authorize('submitRefund', $order);

        $request->validate([
            'reason' => 'required|string|max:1000',
            'refund_amount' => 'required|integer|min:1|max:' . $order->amount,
        ]);

        try {
            $refundRequest = $this->refundService->submitRequest(
                $order,
                $request->user(),
                $request->input('reason'),
                $request->input('refund_amount'),
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($refundRequest, 201);
    }

    /**
     * POST /api/refund-requests/{refundRequest}/approve
     */
    public function approve(Request $request, RefundRequest $refundRequest): JsonResponse
    {
        $this->authorize('approve', $refundRequest);

        try {
            $refundRequest = $this->refundService->approve($refundRequest, $request->user());
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($refundRequest);
    }

    /**
     * POST /api/refund-requests/{refundRequest}/reject
     */
    public function reject(Request $request, RefundRequest $refundRequest): JsonResponse
    {
        $this->authorize('reject', $refundRequest);

        try {
            $refundRequest = $this->refundService->reject($refundRequest, $request->user());
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($refundRequest);
    }
}
