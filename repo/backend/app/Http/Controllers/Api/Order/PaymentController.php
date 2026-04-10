<?php

namespace App\Http\Controllers\Api\Order;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Api\BaseController;
use App\Models\Order;
use App\Services\Order\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class PaymentController extends BaseController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * POST /api/orders/{order}/payments
     */
    public function store(Request $request, Order $order): JsonResponse
    {
        $this->authorize('recordPayment', $order);

        $request->validate([
            'method' => ['required', 'string', new Enum(PaymentMethod::class)],
            'amount' => 'required|integer|min:1',
            'transaction_ref' => 'sometimes|string|max:255',
        ]);

        $payment = $this->paymentService->recordOfflinePayment(
            $order,
            $request->user(),
            $request->input('method'),
            $request->input('amount'),
            $request->input('transaction_ref'),
        );

        return $this->success($payment, 201);
    }
}
