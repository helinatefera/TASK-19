<?php

use App\Enums\CampaignStatus;
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

test('GET /api/orders/{id}/milestones returns milestones list', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'milestone-list-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-MILE01',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    $response = $this->actingAs($this->user)->getJson("/api/orders/{$order->id}/milestones");

    $response->assertStatus(200)
        ->assertJsonCount(0);
});

test('POST /api/orders/{id}/milestones by staff creates milestone', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'milestone-create-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-MILE02',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    $response = $this->actingAs($this->staff)->postJson("/api/orders/{$order->id}/milestones", [
        'title' => 'Shipped',
        'description' => 'Package has been shipped via courier',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['id', 'order_id', 'title', 'description', 'status', 'sort_order'])
        ->assertJsonFragment([
            'title' => 'Shipped',
            'description' => 'Package has been shipped via courier',
            'status' => 'pending',
        ]);
});

test('POST /api/orders/{id}/milestones by regular user returns 403', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'milestone-unauth-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-MILE03',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/orders/{$order->id}/milestones", [
        'title' => 'Should Fail',
    ]);

    $response->assertStatus(403);
});

test('PUT /api/milestones/{id} by staff updates milestone status and sets completed_at', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'milestone-update-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-MILE04',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    // Create milestone via API first
    $createResponse = $this->actingAs($this->staff)->postJson("/api/orders/{$order->id}/milestones", [
        'title' => 'Preparing',
        'description' => 'Order is being prepared',
        'status' => 'pending',
        'sort_order' => 1,
    ]);
    $milestoneId = $createResponse->json('id');

    // Update it to completed
    $response = $this->actingAs($this->staff)->putJson("/api/milestones/{$milestoneId}", [
        'status' => 'completed',
        'description' => 'Order preparation finished',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'completed',
            'description' => 'Order preparation finished',
        ]);

    // completed_at should be set automatically
    expect($response->json('completed_at'))->not->toBeNull();
});

test('POST /api/orders/{id}/attend by staff marks attendance', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'attend-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-ATND01',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    $response = $this->actingAs($this->staff)->postJson("/api/orders/{$order->id}/attend", [
        'attended' => true,
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['attended' => true]);
});
