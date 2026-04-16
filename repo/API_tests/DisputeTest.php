<?php

use App\Enums\CampaignStatus;
use App\Enums\DisputeStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Campaign;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\RewardTier;
use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('username', 'user1')->first();
    $this->moderator = User::where('username', 'mod1')->first();
    $this->campaign = Campaign::where('status', CampaignStatus::Fundraising)->first();
    $this->rewardTier = RewardTier::where('campaign_id', $this->campaign->id)->first();
});

test('POST /api/orders/{id}/disputes creates a dispute', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'dispute-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-DISP01',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/orders/{$order->id}/disputes", [
        'reason' => 'Item not as described',
    ], ['X-Idempotency-Key' => 'dispute-create-' . uniqid()]);

    $response->assertStatus(201)
        ->assertJsonStructure(['id', 'order_id', 'initiated_by', 'reason', 'status'])
        ->assertJsonFragment([
            'reason' => 'Item not as described',
            'status' => 'open',
            'order_id' => $order->id,
            'initiated_by' => $this->user->id,
        ]);
});

test('GET /api/disputes returns list for moderator', function () {
    $response = $this->actingAs($this->moderator)->getJson('/api/disputes');

    $response->assertStatus(200)
        ->assertJsonStructure(['data', 'current_page', 'per_page', 'total']);
});

test('GET /api/disputes/{id} returns dispute detail', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'dispute-show-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-DISP02',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    $dispute = Dispute::create([
        'order_id' => $order->id,
        'initiated_by' => $this->user->id,
        'against_user_id' => $this->campaign->creator_id,
        'reason' => 'Test dispute detail',
        'status' => DisputeStatus::Open,
    ]);

    $response = $this->actingAs($this->user)->getJson("/api/disputes/{$dispute->id}");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'reason' => 'Test dispute detail',
            'status' => 'open',
            'initiated_by' => $this->user->id,
        ]);
});

test('POST /api/disputes/{id}/assign assigns moderator', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'dispute-assign-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-DISP03',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    $dispute = Dispute::create([
        'order_id' => $order->id,
        'initiated_by' => $this->user->id,
        'against_user_id' => $this->campaign->creator_id,
        'reason' => 'Needs assignment',
        'status' => DisputeStatus::Open,
    ]);

    $response = $this->actingAs($this->moderator)->postJson("/api/disputes/{$dispute->id}/assign", [
        'assigned_to' => $this->moderator->id,
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['assigned_to' => $this->moderator->id]);

    $dispute->refresh();
    expect($dispute->assigned_to)->toBe($this->moderator->id);
});

test('POST /api/disputes/{id}/decide resolves dispute', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'dispute-decide-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-DISP04',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    $dispute = Dispute::create([
        'order_id' => $order->id,
        'initiated_by' => $this->user->id,
        'against_user_id' => $this->campaign->creator_id,
        'reason' => 'Needs decision',
        'status' => DisputeStatus::UnderReview,
        'assigned_to' => $this->moderator->id,
    ]);

    $response = $this->actingAs($this->moderator)->postJson("/api/disputes/{$dispute->id}/decide", [
        'decision' => 'refund_buyer',
        'reasoning' => 'Seller did not deliver on time',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['status' => 'resolved']);

    $dispute->refresh();
    expect($dispute->status)->toBe(DisputeStatus::Resolved);
});
