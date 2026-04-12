<?php

use App\Enums\AfterSalesStatus;
use App\Enums\CampaignStatus;
use App\Enums\CampaignVisibility;
use App\Enums\DisputeStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\AfterSalesRequest;
use App\Models\Campaign;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\RewardTier;
use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->user1 = User::where('username', 'user1')->first();
    $this->user2 = User::where('username', 'user2')->first();
    $this->staff = User::where('username', 'staff1')->first();
    $this->campaign = Campaign::where('status', CampaignStatus::Fundraising)->first();
    $this->rewardTier = RewardTier::where('campaign_id', $this->campaign->id)->first();
});

// ── Dispute ownership isolation ─────────────────────────────────────

test('user cannot create dispute on another users order', function () {
    // Order belongs to user1
    $order = Order::create([
        'user_id' => $this->user1->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'authz-dispute-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-AUTHZ01',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    // user2 tries to create a dispute on user1's order
    $response = $this->actingAs($this->user2)->postJson("/api/orders/{$order->id}/disputes", [
        'reason' => 'Trying to dispute someone elses order',
    ], ['X-Idempotency-Key' => 'dispute-unauth-' . uniqid()]);

    $response->assertStatus(403);
});

test('user can create dispute on their own order', function () {
    $order = Order::create([
        'user_id' => $this->user1->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'authz-dispute-own-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-AUTHZ02',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    $response = $this->actingAs($this->user1)->postJson("/api/orders/{$order->id}/disputes", [
        'reason' => 'Legitimate dispute on my own order',
    ], ['X-Idempotency-Key' => 'dispute-own-' . uniqid()]);

    $response->assertStatus(201);
});

// ── After-sales resolve isolation ───────────────────────────────────

test('regular user cannot resolve after-sales requests', function () {
    $order = Order::create([
        'user_id' => $this->user1->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'authz-aftersales-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-AUTHZ03',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
    ]);

    $afterSales = AfterSalesRequest::create([
        'order_id' => $order->id,
        'user_id' => $this->user1->id,
        'type' => 'complaint',
        'reason' => 'Test complaint',
        'status' => AfterSalesStatus::Submitted,
    ]);

    // Regular user tries to resolve — should be denied
    $response = $this->actingAs($this->user1)->postJson("/api/after-sales/{$afterSales->id}/resolve", [
        'status' => 'approved',
        'staff_notes' => 'Trying to self-approve',
    ]);

    $response->assertStatus(403);
});

test('staff can resolve after-sales requests', function () {
    $order = Order::create([
        'user_id' => $this->user1->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'authz-aftersales-staff-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-AUTHZ04',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
        'has_pending_after_sales' => true,
    ]);

    $afterSales = AfterSalesRequest::create([
        'order_id' => $order->id,
        'user_id' => $this->user1->id,
        'type' => 'complaint',
        'reason' => 'Test complaint',
        'status' => AfterSalesStatus::Submitted,
    ]);

    $response = $this->actingAs($this->staff)->postJson("/api/after-sales/{$afterSales->id}/resolve", [
        'status' => 'approved',
        'staff_notes' => 'Approved by staff',
    ]);

    $response->assertStatus(200);
});

// ── After-sales checksum contract ──────────────────────────────────

test('after-sales submit with valid checksum succeeds', function () {
    $order = Order::create([
        'user_id' => $this->user1->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'authz-checksum-valid-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-CSUM01',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
    ]);

    $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
    $checksum = hash_file('sha256', $file->getRealPath());

    $response = $this->actingAs($this->user1)->postJson(
        "/api/orders/{$order->id}/after-sales",
        [
            'type' => 'complaint',
            'reason' => 'Testing valid checksum',
            'attachment' => $file,
            'client_checksum' => $checksum,
        ],
        ['X-Idempotency-Key' => 'aftersales-valid-checksum-' . uniqid()],
    );

    $response->assertStatus(201);
});

test('after-sales submit without client_checksum returns 422', function () {
    $order = Order::create([
        'user_id' => $this->user1->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'authz-checksum-missing-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-CSUM02',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
    ]);

    $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->actingAs($this->user1)->postJson(
        "/api/orders/{$order->id}/after-sales",
        [
            'type' => 'complaint',
            'reason' => 'Testing missing checksum',
            'attachment' => $file,
        ],
        ['X-Idempotency-Key' => 'aftersales-no-checksum-' . uniqid()],
    );

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['client_checksum']);
});

test('after-sales submit with mismatched checksum returns 422', function () {
    $order = Order::create([
        'user_id' => $this->user1->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'authz-checksum-mismatch-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-CSUM03',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
    ]);

    $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->actingAs($this->user1)->postJson(
        "/api/orders/{$order->id}/after-sales",
        [
            'type' => 'complaint',
            'reason' => 'Testing mismatched checksum',
            'attachment' => $file,
            'client_checksum' => 'deadbeef00000000000000000000000000000000000000000000000000000000',
        ],
        ['X-Idempotency-Key' => 'aftersales-bad-checksum-' . uniqid()],
    );

    $response->assertStatus(422)
        ->assertJsonFragment(['msg' => 'Attachment checksum mismatch. The file may have been corrupted during upload.']);
});
