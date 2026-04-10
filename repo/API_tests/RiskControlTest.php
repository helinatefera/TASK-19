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
    $this->moderator = User::where('username', 'mod1')->first();
    $this->staff = User::where('username', 'staff1')->first();
    $this->user = User::where('username', 'user1')->first();
    $this->regularUser = User::where('username', 'user2')->first();
});

test('GET /api/risk/credit-scores/{user} returns score for moderator', function () {
    $response = $this->actingAs($this->moderator)->getJson("/api/risk/credit-scores/{$this->user->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'user_id',
            'score',
            'restriction_level',
        ]);
});

test('GET /api/risk/credit-scores/{user} returns 403 for regular user', function () {
    $response = $this->actingAs($this->regularUser)->getJson("/api/risk/credit-scores/{$this->user->id}");

    $response->assertStatus(403);
});

test('POST /api/risk/chargebacks by staff records chargeback', function () {
    $campaign = Campaign::where('status', CampaignStatus::Fundraising)->first();
    $rewardTier = RewardTier::where('campaign_id', $campaign->id)->first();

    // Create an order for the target user
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $campaign->id,
        'reward_tier_id' => $rewardTier->id,
        'request_key' => 'chargeback-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-CHRG0001',
        'order_type' => OrderType::Contribution,
        'amount' => $rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    $response = $this->actingAs($this->staff)->postJson('/api/risk/chargebacks', [
        'order_id' => $order->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'credit_score' => [
                'id',
                'user_id',
                'score',
                'chargeback_count',
            ],
        ]);

    expect($response->json('credit_score.chargeback_count'))->toBeGreaterThanOrEqual(1);
});
