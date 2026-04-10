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
