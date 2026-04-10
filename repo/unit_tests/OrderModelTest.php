<?php

use App\Enums\CampaignStatus;
use App\Enums\CampaignVisibility;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->campaign = Campaign::create([
        'creator_id' => $this->user->id,
        'title' => 'Test Campaign',
        'slug' => 'test-campaign-' . uniqid(),
        'description' => 'Test description',
        'risk_disclosure' => 'Test risk',
        'target_amount' => 100000,
        'pledged_amount' => 0,
        'currency' => 'USD',
        'status' => CampaignStatus::Fundraising,
        'visibility' => CampaignVisibility::Online,
        'duration_days' => 30,
    ]);
});

test('canCancel returns true for confirmed order', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'request_key' => 'rk-cancel-true-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-TEST1234',
        'order_type' => OrderType::Contribution,
        'amount' => 5000,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    expect($order->canCancel())->toBeTrue();
});

test('canCancel returns false for fulfilled order', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'request_key' => 'rk-cancel-fulfilled-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-TEST1235',
        'order_type' => OrderType::Contribution,
        'amount' => 5000,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
    ]);

    expect($order->canCancel())->toBeFalse();
});

test('canCancel returns false for already cancelled order', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'request_key' => 'rk-cancel-cancelled-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-TEST1236',
        'order_type' => OrderType::Contribution,
        'amount' => 5000,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
        'cancelled_at' => now(),
    ]);

    expect($order->canCancel())->toBeFalse();
});

test('canRefund returns true when fulfilled and refund_deadline is in the future', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'request_key' => 'rk-refund-true-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-TEST1237',
        'order_type' => OrderType::Contribution,
        'amount' => 5000,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
        'refund_deadline' => now()->addDays(14),
        'has_pending_refund' => false,
    ]);

    expect($order->canRefund())->toBeTrue();
});

test('canRefund returns false when refund_deadline is in the past', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'request_key' => 'rk-refund-past-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-TEST1238',
        'order_type' => OrderType::Contribution,
        'amount' => 5000,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
        'refund_deadline' => now()->subDay(),
        'has_pending_refund' => false,
    ]);

    expect($order->canRefund())->toBeFalse();
});

test('canRefund returns false when has_pending_refund is true', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'request_key' => 'rk-refund-pending-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-TEST1239',
        'order_type' => OrderType::Contribution,
        'amount' => 5000,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
        'refund_deadline' => now()->addDays(14),
        'has_pending_refund' => true,
    ]);

    expect($order->canRefund())->toBeFalse();
});

test('canRefund returns false when order is not fulfilled', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'request_key' => 'rk-refund-notconf-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-TEST1240',
        'order_type' => OrderType::Contribution,
        'amount' => 5000,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
        'refund_deadline' => now()->addDays(14),
        'has_pending_refund' => false,
    ]);

    expect($order->canRefund())->toBeFalse();
});
