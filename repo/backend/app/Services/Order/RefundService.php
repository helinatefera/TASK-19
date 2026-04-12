<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Enums\RefundStatus;
use App\Events\RefundApproved;
use App\Events\RefundRejected;
use App\Events\RefundRequested;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\User;

class RefundService
{
    public function submitRequest(Order $order, User $user, string $reason, int $amount): RefundRequest
    {
        if ($order->status !== OrderStatus::Fulfilled) {
            throw new \RuntimeException('Refunds can only be requested for fulfilled orders.');
        }

        if ($order->refund_deadline && now()->isAfter($order->refund_deadline)) {
            throw new \RuntimeException('The refund window has expired.');
        }

        if ($order->has_pending_after_sales) {
            throw new \RuntimeException('Cannot submit refund while an after-sales request is pending.');
        }

        $refundRequest = RefundRequest::create([
            'order_id' => $order->id,
            'requested_by' => $user->id,
            'reason' => $reason,
            'status' => RefundStatus::Pending,
            'refund_amount' => $amount,
        ]);

        $order->update(['has_pending_refund' => true]);

        try {
            RefundRequested::dispatch($refundRequest, $user);
        } catch (\Throwable $e) {
            report($e);
        }

        return $refundRequest;
    }

    public function approve(RefundRequest $request, User $reviewer): RefundRequest
    {
        if ($request->status !== RefundStatus::Pending) {
            throw new \RuntimeException('Only pending refund requests can be approved.');
        }

        $request->update([
            'status' => RefundStatus::Approved,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $order = $request->order;
        $order->update([
            'has_pending_refund' => false,
            'status' => OrderStatus::Refunded,
        ]);

        $request->refresh();
        $order->refresh();

        // Dispatch events after all DB writes are committed
        try {
            RefundApproved::dispatch($request, $reviewer);
            \App\Events\OrderRefunded::dispatch($order, $request);
        } catch (\Throwable $e) {
            report($e);
        }

        return $request;
    }

    public function reject(RefundRequest $request, User $reviewer): RefundRequest
    {
        if ($request->status !== RefundStatus::Pending) {
            throw new \RuntimeException('Only pending refund requests can be rejected.');
        }

        $request->update([
            'status' => RefundStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $request->order->update(['has_pending_refund' => false]);

        $request->refresh();

        RefundRejected::dispatch($request, $reviewer);

        return $request;
    }
}
