<?php

namespace App\Services\Order;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Support\Str;

class PaymentService
{

    public function recordOfflinePayment(
        Order $order,
        User $staff,
        string $method,
        int $amount,
        ?string $transactionRef = null,
    ): Payment {
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => PaymentMethod::from($method),
            'status' => PaymentStatus::Completed,
            'amount' => $amount,
            'currency' => $order->currency ?? 'USD',
            'transaction_ref' => $transactionRef,
            'recorded_by' => $staff->id,
            'paid_at' => now(),
        ]);

        Receipt::create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'rendered_locale' => $order->user->locale ?? config('app.locale', 'en'),
            'rendered_timezone' => $order->user->timezone ?? config('app.timezone', 'UTC'),
            'receipt_number' => 'RCT-' . strtoupper(Str::random(10)),
            'content' => [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'amount' => $amount,
                'method' => $method,
                'paid_at' => now()->toIso8601String(),
            ],
            'generated_at' => now(),
        ]);

        OrderPaid::dispatch($order, $payment);

        return $payment;
    }
}
