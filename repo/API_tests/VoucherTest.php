<?php

use App\Enums\CampaignStatus;
use App\Enums\VoucherStatus;
use App\Models\Campaign;
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

test('after payment, voucher is created', function () {
    // Create an order
    $requestKey = 'voucher-test-order-' . uniqid();
    $createResponse = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ]);

    $orderId = $createResponse->json('id');

    // Record payment (this triggers voucher creation)
    $this->actingAs($this->staff)->postJson("/api/orders/{$orderId}/payments", [
        'method' => 'cash',
        'amount' => $this->rewardTier->price,
    ]);

    // Check that a voucher was created
    $voucher = Voucher::where('order_id', $orderId)->first();
    expect($voucher)->not->toBeNull();
    expect($voucher->status)->toBe(VoucherStatus::Active);
    expect($voucher->code)->toHaveLength(12);
});

test('POST /api/vouchers/{id}/redeem by staff redeems voucher', function () {
    // Create order and pay to get a voucher
    $requestKey = 'redeem-test-order-' . uniqid();
    $createResponse = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ]);

    $orderId = $createResponse->json('id');

    $this->actingAs($this->staff)->postJson("/api/orders/{$orderId}/payments", [
        'method' => 'cash',
        'amount' => $this->rewardTier->price,
    ]);

    $voucher = Voucher::where('order_id', $orderId)->first();

    // Redeem voucher
    $response = $this->actingAs($this->staff)->postJson("/api/vouchers/{$voucher->id}/redeem");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'redeemed',
        ]);
});

test('POST /api/vouchers/{id}/redeem twice returns error', function () {
    // Create order and pay to get a voucher
    $requestKey = 'double-redeem-test-' . uniqid();
    $createResponse = $this->actingAs($this->user)->postJson('/api/orders', [
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => $requestKey,
    ]);

    $orderId = $createResponse->json('id');

    $this->actingAs($this->staff)->postJson("/api/orders/{$orderId}/payments", [
        'method' => 'cash',
        'amount' => $this->rewardTier->price,
    ]);

    $voucher = Voucher::where('order_id', $orderId)->first();

    // First redemption
    $this->actingAs($this->staff)->postJson("/api/vouchers/{$voucher->id}/redeem");

    // Second redemption should fail
    $response = $this->actingAs($this->staff)->postJson("/api/vouchers/{$voucher->id}/redeem");

    $response->assertStatus(403);
});
