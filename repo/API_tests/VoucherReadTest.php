<?php

use App\Enums\CampaignStatus;
use App\Enums\VoucherStatus;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\RewardTier;
use App\Models\User;
use App\Models\Voucher;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('username', 'user1')->first();
    $this->staff = User::where('username', 'staff1')->first();
    $this->campaign = Campaign::where('status', CampaignStatus::Fundraising)->first();
    $this->rewardTier = RewardTier::where('campaign_id', $this->campaign->id)->first();
});

test('GET /api/vouchers returns user voucher list', function () {
    $response = $this->actingAs($this->user)->getJson('/api/vouchers');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('GET /api/vouchers/{id} returns voucher detail', function () {
    // Create order + voucher
    $requestKey = 'voucher-detail-' . uniqid();
    $createResponse = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ], ['X-Idempotency-Key' => $requestKey]);

    $orderId = $createResponse->json('id');

    $this->actingAs($this->staff)->postJson("/api/orders/{$orderId}/payments", [
        'method' => 'cash',
        'amount' => $this->rewardTier->price,
    ], ['X-Idempotency-Key' => 'pay-voucher-detail-' . uniqid()]);

    $voucher = Voucher::where('order_id', $orderId)->first();

    $response = $this->actingAs($this->user)->getJson("/api/vouchers/{$voucher->id}");

    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $voucher->id]);
});
