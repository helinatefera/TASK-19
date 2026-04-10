<?php

namespace App\Http\Controllers\Api\Voucher;

use App\Http\Controllers\Api\BaseController;
use App\Models\Voucher;
use App\Services\Voucher\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class VoucherController extends BaseController
{
    public function __construct(
        private readonly VoucherService $voucherService,
    ) {}

    /**
     * GET /api/vouchers
     */
    public function index(Request $request): JsonResponse
    {
        $query = Voucher::query()
            ->whereHas('order', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->with('order')
            ->latest();

        return $this->paginated($query);
    }

    /**
     * GET /api/vouchers/{voucher}
     */
    public function show(Request $request, Voucher $voucher): JsonResponse
    {
        $this->authorize('view', $voucher);

        $voucher->load('order');

        return $this->success($voucher);
    }

    /**
     * POST /api/vouchers/{voucher}/redeem
     */
    public function redeem(Request $request, Voucher $voucher): JsonResponse
    {
        $this->authorize('redeem', $voucher);

        try {
            $voucher = $this->voucherService->redeem($voucher, $request->user());
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($voucher);
    }
}
