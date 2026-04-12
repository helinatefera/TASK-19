<?php

use App\Enums\CampaignStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\AfterSalesStatus;
use App\Models\AfterSalesRequest;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\RewardTier;
use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('username', 'user1')->first();
    $this->staff = User::where('username', 'staff1')->first();
    $this->campaign = Campaign::where('status', CampaignStatus::Fundraising)->first();
    $this->rewardTier = RewardTier::where('campaign_id', $this->campaign->id)->first();
});

test('POST /api/orders/{id}/refunds creates refund request', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'refund-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REFUND01',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
        'refund_deadline' => now()->addDays(14),
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/orders/{$order->id}/refunds", [
        'reason' => 'I changed my mind',
        'refund_amount' => $this->rewardTier->price,
    ], ['X-Idempotency-Key' => 'refund-create-' . uniqid()]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'order_id',
            'requested_by',
            'reason',
            'status',
            'refund_amount',
        ])
        ->assertJsonFragment([
            'status' => 'pending',
        ]);
});

test('POST /api/refund-requests/{id}/approve by staff approves refund', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'refund-approve-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REFUND02',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
        'refund_deadline' => now()->addDays(14),
    ]);

    // Create refund request
    $refundResponse = $this->actingAs($this->user)->postJson("/api/orders/{$order->id}/refunds", [
        'reason' => 'Need a refund',
        'refund_amount' => $this->rewardTier->price,
    ], ['X-Idempotency-Key' => 'refund-for-approve-' . uniqid()]);

    $refundRequestId = $refundResponse->json('id');

    // Approve it
    $response = $this->actingAs($this->staff)->postJson("/api/refund-requests/{$refundRequestId}/approve");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'approved',
        ]);
});

test('POST /api/refund-requests/{id}/reject by staff rejects refund', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'refund-reject-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REFUND03',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
        'refund_deadline' => now()->addDays(14),
    ]);

    // Create refund request
    $refundResponse = $this->actingAs($this->user)->postJson("/api/orders/{$order->id}/refunds", [
        'reason' => 'Need a refund',
        'refund_amount' => $this->rewardTier->price,
    ], ['X-Idempotency-Key' => 'refund-for-reject-' . uniqid()]);

    $refundRequestId = $refundResponse->json('id');

    // Reject it
    $response = $this->actingAs($this->staff)->postJson("/api/refund-requests/{$refundRequestId}/reject");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'rejected',
        ]);
});

test('POST /api/refund-requests/{id}/approve by moderator approves refund', function () {
    $moderator = User::where('username', 'mod1')->first();

    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'refund-mod-approve-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REFMOD1',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
        'refund_deadline' => now()->addDays(14),
    ]);

    $refundResponse = $this->actingAs($this->user)->postJson("/api/orders/{$order->id}/refunds", [
        'reason' => 'Moderator review needed',
        'refund_amount' => $this->rewardTier->price,
    ], ['X-Idempotency-Key' => 'refund-for-mod-' . uniqid()]);

    $refundRequestId = $refundResponse->json('id');

    $response = $this->actingAs($moderator)->postJson("/api/refund-requests/{$refundRequestId}/approve");

    $response->assertStatus(200)
        ->assertJsonFragment(['status' => 'approved']);
});

test('cannot submit refund when after-sales request is pending', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'refund-aftersales-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REFUND04',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
        'refund_deadline' => now()->addDays(14),
        'has_pending_after_sales' => true,
    ]);

    // Create an after-sales request to simulate pending state
    AfterSalesRequest::create([
        'order_id' => $order->id,
        'user_id' => $this->user->id,
        'type' => 'refund',
        'reason' => 'After-sales issue',
        'status' => AfterSalesStatus::Submitted,
    ]);

    // Try to create a refund — should be REJECTED because after-sales is pending
    $response = $this->actingAs($this->user)->postJson("/api/orders/{$order->id}/refunds", [
        'reason' => 'I want a refund too',
        'refund_amount' => $this->rewardTier->price,
    ], ['X-Idempotency-Key' => 'refund-pending-aftersales-' . uniqid()]);

    // Conflict: after-sales is pending, refund must be blocked
    $response->assertStatus(422);
});

test('cannot submit refund on non-fulfilled order', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'refund-confirmed-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REFUND05',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
        'refund_deadline' => now()->addDays(14),
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/orders/{$order->id}/refunds", [
        'reason' => 'I want a refund',
        'refund_amount' => $this->rewardTier->price,
    ], ['X-Idempotency-Key' => 'refund-non-fulfilled-' . uniqid()]);

    // Should be rejected — only fulfilled orders are eligible for refund
    $response->assertStatus(403);
});

test('cannot submit refund after refund window expires', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'refund-expired-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REFUND06',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now()->subDays(30),
        'refund_deadline' => now()->subDays(1),
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/orders/{$order->id}/refunds", [
        'reason' => 'Late refund attempt',
        'refund_amount' => $this->rewardTier->price,
    ], ['X-Idempotency-Key' => 'refund-expired-window-' . uniqid()]);

    // Should be rejected — refund window has expired
    $response->assertStatus(403);
});
