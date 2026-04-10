<?php

use App\Enums\CampaignStatus;
use App\Enums\CampaignVisibility;
use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\RewardTier;
use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('username', 'user1')->first();
    $this->staff = User::where('username', 'staff1')->first();
    $this->campaign = Campaign::where('status', CampaignStatus::Fundraising)->first();
    $this->rewardTier = RewardTier::where('campaign_id', $this->campaign->id)->first();
});

test('POST /api/orders creates contribution order', function () {
    $requestKey = 'test-order-' . uniqid();

    $response = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'order_type' => 'contribution',
            'status' => 'confirmed',
        ]);

    expect($response->json('confirmation_number'))->toStartWith('CC-');
});

test('POST /api/orders with duplicate request_key returns same order (idempotency)', function () {
    $requestKey = 'idempotent-order-' . uniqid();

    $response1 = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ], ['X-Idempotency-Key' => $requestKey]);

    $response1->assertStatus(201);

    // Second request with same idempotency key
    $response2 = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ], ['X-Idempotency-Key' => $requestKey]);

    // Should return the cached response
    expect($response2->json('id'))->toBe($response1->json('id'));
});

test('GET /api/orders returns users orders', function () {
    // Create an order first
    $requestKey = 'list-test-order-' . uniqid();
    $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/orders');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'current_page',
            'per_page',
            'total',
        ]);
});

test('GET /api/orders/{id} returns order detail', function () {
    $requestKey = 'detail-test-order-' . uniqid();
    $createResponse = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ]);

    $orderId = $createResponse->json('id');

    $response = $this->actingAs($this->user)->getJson("/api/orders/{$orderId}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'user_id',
            'campaign_id',
            'confirmation_number',
            'order_type',
            'status',
            'amount',
        ]);
});

test('POST /api/orders/{id}/cancel cancels a confirmed order', function () {
    $requestKey = 'cancel-test-order-' . uniqid();
    $createResponse = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ]);

    $orderId = $createResponse->json('id');

    $response = $this->actingAs($this->user)->postJson("/api/orders/{$orderId}/cancel", [
        'reason' => 'Changed my mind',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'cancelled',
        ]);
});

test('POST /api/orders/{id}/fulfill by staff fulfills order', function () {
    $requestKey = 'fulfill-test-order-' . uniqid();
    $createResponse = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ]);

    $orderId = $createResponse->json('id');

    $response = $this->actingAs($this->staff)->postJson("/api/orders/{$orderId}/fulfill");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'fulfilled',
        ]);
});

test('POST /api/orders/{id}/payments by staff records payment', function () {
    $requestKey = 'payment-test-order-' . uniqid();
    $createResponse = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ]);

    $orderId = $createResponse->json('id');

    $response = $this->actingAs($this->staff)->postJson("/api/orders/{$orderId}/payments", [
        'method' => 'cash',
        'amount' => $this->rewardTier->price,
        'transaction_ref' => 'TXN-' . uniqid(),
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'order_id',
            'method',
            'status',
            'amount',
        ]);
});

test('POST /api/orders/{id}/payments with invalid method returns 422', function () {
    $requestKey = 'payment-invalid-method-' . uniqid();
    $createResponse = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ]);

    $orderId = $createResponse->json('id');

    $response = $this->actingAs($this->staff)->postJson("/api/orders/{$orderId}/payments", [
        'method' => 'bitcoin',
        'amount' => $this->rewardTier->price,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['method']);
});

test('GET /api/orders by staff returns all users orders', function () {
    // Create an order for user
    $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'staff-list-test-' . uniqid(),
    ]);

    // Staff should see orders from all users
    $response = $this->actingAs($this->staff)->getJson('/api/orders');

    $response->assertStatus(200);
    expect($response->json('total'))->toBeGreaterThanOrEqual(1);
});

test('GET /api/orders by regular user returns only own orders', function () {
    $user2 = \App\Models\User::where('username', 'user2')->first();

    $response = $this->actingAs($user2)->getJson('/api/orders');

    $response->assertStatus(200);
    // All returned orders should belong to user2
    $orders = $response->json('data');
    foreach ($orders as $order) {
        expect($order['user_id'])->toBe($user2->id);
    }
});

test('GET /api/orders filters by order_type', function () {
    $response = $this->actingAs($this->user)->getJson('/api/orders?order_type=contribution');

    $response->assertStatus(200);
    $orders = $response->json('data');
    foreach ($orders as $order) {
        expect($order['order_type'])->toBe('contribution');
    }
});
