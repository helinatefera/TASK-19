<?php

namespace App\Http\Controllers\Api\Order;

use App\Enums\AfterSalesStatus;
use App\Http\Controllers\Api\BaseController;
use App\Models\AfterSalesRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AfterSalesController extends BaseController
{
    /**
     * POST /api/orders/{order}/after-sales
     */
    public function store(Request $request, Order $order): JsonResponse
    {
        $this->authorize('submitAfterSales', $order);

        if ($order->status->value !== 'fulfilled') {
            return $this->error('After-sales requests can only be submitted for fulfilled orders.', 409);
        }

        if ($order->has_pending_refund) {
            return $this->error('Cannot submit after-sales request while a refund is pending.', 409);
        }

        $request->validate([
            'type' => 'required|string|in:refund,exchange,complaint,other',
            'reason' => 'required|string|max:2000',
            'attachment' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
            'client_checksum' => 'required|string',
        ]);

        $data = [
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'type' => $request->input('type'),
            'reason' => $request->input('reason'),
            'status' => AfterSalesStatus::Submitted,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $serverChecksum = hash_file('sha256', $file->getRealPath());

            if (! hash_equals($serverChecksum, $request->input('client_checksum'))) {
                return $this->error('Attachment checksum mismatch. The file may have been corrupted during upload.', 422);
            }

            $path = $file->store('after-sales', 'local');

            $data['attachment_path'] = $path;
            $data['attachment_mime'] = $file->getMimeType();
            $data['attachment_checksum'] = $serverChecksum;
            $data['attachment_size'] = $file->getSize();
        }

        $afterSales = AfterSalesRequest::create($data);

        $order->update(['has_pending_after_sales' => true]);

        return $this->success($afterSales, 201);
    }

    /**
     * POST /api/after-sales/{afterSalesRequest}/review
     */
    public function review(Request $request, AfterSalesRequest $afterSalesRequest): JsonResponse
    {
        $this->authorize('review', $afterSalesRequest);

        if ($afterSalesRequest->status !== AfterSalesStatus::Submitted) {
            return $this->error('Only submitted requests can be moved to review.', 422);
        }

        $request->validate([
            'staff_notes' => 'required|string|max:2000',
        ]);

        $afterSalesRequest->update([
            'status' => AfterSalesStatus::UnderReview,
            'staff_notes' => $request->input('staff_notes'),
        ]);

        return $this->success($afterSalesRequest->refresh());
    }

    /**
     * POST /api/after-sales/{afterSalesRequest}/resolve
     */
    public function resolve(Request $request, AfterSalesRequest $afterSalesRequest): JsonResponse
    {
        $this->authorize('resolve', $afterSalesRequest);

        if (! in_array($afterSalesRequest->status, [AfterSalesStatus::Submitted, AfterSalesStatus::UnderReview], true)) {
            return $this->error('Only submitted or under-review requests can be resolved.', 422);
        }

        $request->validate([
            'status' => 'required|string|in:approved,rejected',
            'staff_notes' => 'required|string|max:2000',
        ]);

        $afterSalesRequest->update([
            'status' => AfterSalesStatus::from($request->input('status')),
            'staff_notes' => $request->input('staff_notes'),
            'resolved_at' => now(),
            'resolved_by' => $request->user()->id,
        ]);

        $afterSalesRequest->order->update(['has_pending_after_sales' => false]);

        return $this->success($afterSalesRequest->refresh());
    }
}
