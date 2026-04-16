<?php

use App\Enums\AfterSalesStatus;
use App\Enums\CampaignStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\AfterSalesRequest;
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

test('POST /api/after-sales/{id}/review moves request to under_review', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'as-review-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-ASRV01',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
        'has_pending_after_sales' => true,
    ]);

    $afterSales = AfterSalesRequest::create([
        'order_id' => $order->id,
        'user_id' => $this->user->id,
        'type' => 'complaint',
        'reason' => 'Product quality issue',
        'status' => AfterSalesStatus::Submitted,
    ]);

    $response = $this->actingAs($this->staff)->postJson("/api/after-sales/{$afterSales->id}/review", [
        'staff_notes' => 'Reviewing the complaint',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['status' => 'under_review']);
});

test('POST /api/after-sales/{id}/review by regular user returns 403', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'as-review-unauth-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-ASRV02',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
    ]);

    $afterSales = AfterSalesRequest::create([
        'order_id' => $order->id,
        'user_id' => $this->user->id,
        'type' => 'complaint',
        'reason' => 'Test',
        'status' => AfterSalesStatus::Submitted,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/after-sales/{$afterSales->id}/review", [
        'staff_notes' => 'Should fail',
    ]);

    $response->assertStatus(403);
});
