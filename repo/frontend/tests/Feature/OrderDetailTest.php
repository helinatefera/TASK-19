<?php

use App\Livewire\Order\OrderDetail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

beforeEach(function () {
    Session::put('api_token', 'test-token');
    Session::put('api_user', [
        'id' => 1,
        'username' => 'testuser',
        'display_name' => 'Test User',
        'roles' => ['user'],
        'permissions' => [],
        'locale' => 'en',
        'timezone' => 'UTC',
    ]);
});

test('order detail renders with order data from API', function () {
    Http::fake([
        '*/api/orders/1*' => Http::response([
            'id' => 1,
            'order_type' => 'contribution',
            'status' => 'confirmed',
            'amount' => 5000,
            'currency' => 'USD',
            'confirmation_number' => 'CC-260409-ABCD1234',
            'can_cancel' => true,
            'can_refund' => false,
            'has_pending_refund' => false,
            'has_pending_after_sales' => false,
            'attended' => null,
            'campaign' => ['id' => 1, 'title' => 'Test Campaign'],
            'payments' => [],
            'vouchers' => [],
            'refund_requests' => [],
            'after_sales_requests' => [],
            'logistics_milestones' => [],
            'receipts' => [],
        ]),
    ]);

    Livewire::test(OrderDetail::class, ['orderId' => 1])
        ->assertStatus(200)
        ->assertSee('CC-260409-ABCD1234');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/orders/1'));
});

test('requestRefund generates session-backed idempotency key stable across retries', function () {
    Http::fake([
        '*/api/orders/1/refunds' => Http::response(['msg' => 'Server error'], 500),
        '*/api/orders/*' => Http::response([
            'id' => 1,
            'order_type' => 'contribution',
            'status' => 'fulfilled',
            'amount' => 5000,
            'currency' => 'USD',
            'confirmation_number' => 'CC-260409-STABLE01',
            'can_cancel' => false,
            'can_refund' => true,
            'has_pending_refund' => false,
            'has_pending_after_sales' => false,
            'attended' => null,
            'campaign' => ['id' => 1, 'title' => 'Test Campaign'],
            'payments' => [],
            'vouchers' => [],
            'refund_requests' => [],
            'after_sales_requests' => [],
            'logistics_milestones' => [],
            'receipts' => [],
        ]),
    ]);

    $component = Livewire::test(OrderDetail::class, ['orderId' => 1])
        ->set('refundReason', 'Test refund reason')
        ->set('refundAmount', 5000);

    // First attempt — fails (500), key should be stored in session
    $component->call('requestRefund');

    $storedKey = session('idempotency:refund:1');
    expect($storedKey)->not->toBeNull()
        ->toStartWith('refund-1-');

    // Retry — should reuse same key from session, not generate a new one
    $component->call('requestRefund');

    expect(session('idempotency:refund:1'))->toBe($storedKey);

    // Both requests must have sent the identical idempotency key
    $refundRequests = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), '/api/orders/1/refunds'))
        ->values();

    expect($refundRequests)->toHaveCount(2);
    expect($refundRequests[0][0]->header('X-Idempotency-Key')[0])
        ->toBe($storedKey);
    expect($refundRequests[1][0]->header('X-Idempotency-Key')[0])
        ->toBe($storedKey);
});

test('requestRefund clears session key after success', function () {
    Http::fake([
        '*/api/orders/1/refunds' => Http::response([
            'id' => 10,
            'order_id' => 1,
            'requested_by' => 1,
            'reason' => 'Test',
            'status' => 'pending',
            'refund_amount' => 5000,
        ], 201),
        '*/api/orders/*' => Http::response([
            'id' => 1,
            'order_type' => 'contribution',
            'status' => 'fulfilled',
            'amount' => 5000,
            'currency' => 'USD',
            'confirmation_number' => 'CC-260409-CLEAR01',
            'can_cancel' => false,
            'can_refund' => true,
            'has_pending_refund' => false,
            'has_pending_after_sales' => false,
            'attended' => null,
            'campaign' => ['id' => 1, 'title' => 'Test Campaign'],
            'payments' => [],
            'vouchers' => [],
            'refund_requests' => [],
            'after_sales_requests' => [],
            'logistics_milestones' => [],
            'receipts' => [],
        ]),
    ]);

    $component = Livewire::test(OrderDetail::class, ['orderId' => 1])
        ->set('refundReason', 'Test refund')
        ->set('refundAmount', 5000);

    // Successful refund — key should be cleared from session
    $component->call('requestRefund');

    expect(session('idempotency:refund:1'))->toBeNull();
});

test('recordPayment uses session-backed idempotency key', function () {
    Http::fake([
        '*/api/orders/1/payments' => Http::response(['msg' => 'Temporary failure'], 503),
        '*/api/orders/*' => Http::response([
            'id' => 1,
            'order_type' => 'contribution',
            'status' => 'confirmed',
            'amount' => 5000,
            'currency' => 'USD',
            'confirmation_number' => 'CC-260409-PAY01',
            'can_cancel' => false,
            'can_refund' => false,
            'has_pending_refund' => false,
            'has_pending_after_sales' => false,
            'attended' => null,
            'campaign' => ['id' => 1, 'title' => 'Test Campaign'],
            'payments' => [],
            'vouchers' => [],
            'refund_requests' => [],
            'after_sales_requests' => [],
            'logistics_milestones' => [],
            'receipts' => [],
        ]),
    ]);

    $component = Livewire::test(OrderDetail::class, ['orderId' => 1])
        ->set('paymentMethod', 'cash')
        ->set('paymentAmount', 5000);

    // First attempt — fails, key stored
    $component->call('recordPayment');
    $storedKey = session('idempotency:payment:1');
    expect($storedKey)->not->toBeNull();

    // Retry — same key reused
    $component->call('recordPayment');
    expect(session('idempotency:payment:1'))->toBe($storedKey);

    $paymentRequests = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), '/api/orders/1/payments'))
        ->values();

    expect($paymentRequests)->toHaveCount(2);
    expect($paymentRequests[0][0]->header('X-Idempotency-Key')[0])
        ->toBe($paymentRequests[1][0]->header('X-Idempotency-Key')[0]);
});

test('order detail cancel calls backend API', function () {
    Http::fake([
        '*/api/orders/1' => Http::response([
            'id' => 1,
            'order_type' => 'contribution',
            'status' => 'confirmed',
            'amount' => 5000,
            'currency' => 'USD',
            'confirmation_number' => 'CC-260409-CANCEL01',
            'can_cancel' => true,
            'can_refund' => false,
            'has_pending_refund' => false,
            'has_pending_after_sales' => false,
            'attended' => null,
            'campaign' => ['id' => 1, 'title' => 'Test Campaign'],
            'payments' => [],
            'vouchers' => [],
            'refund_requests' => [],
            'after_sales_requests' => [],
            'logistics_milestones' => [],
            'receipts' => [],
        ]),
        '*/api/orders/1/cancel' => Http::response(['status' => 'cancelled']),
    ]);

    Livewire::test(OrderDetail::class, ['orderId' => 1])
        ->set('cancelReason', 'Changed my mind')
        ->call('cancelOrder');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/orders/1/cancel'));
});
