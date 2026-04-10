<?php

use App\Enums\CampaignStatus;
use App\Enums\CampaignVisibility;
use App\Enums\DisputeStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Campaign;
use App\Models\Dispute;
use App\Models\DisputeDecision;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->campaign = Campaign::create([
        'creator_id' => $this->user->id,
        'title' => 'Test',
        'slug' => 'test-' . uniqid(),
        'description' => 'Test',
        'risk_disclosure' => 'Test',
        'target_amount' => 1000,
        'pledged_amount' => 0,
        'currency' => 'USD',
        'status' => CampaignStatus::Fundraising,
        'visibility' => CampaignVisibility::Online,
        'duration_days' => 30,
    ]);
    $this->order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'request_key' => 'dk-' . uniqid(),
        'confirmation_number' => 'CC-000000-TEST0001',
        'order_type' => OrderType::Contribution,
        'amount' => 1000,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);
    $this->dispute = Dispute::create([
        'order_id' => $this->order->id,
        'initiated_by' => $this->user->id,
        'against_user_id' => $this->user->id,
        'status' => DisputeStatus::Open,
        'reason' => 'Test dispute',
    ]);
});

test('creating a DisputeDecision works and generates checksum', function () {
    $decision = DisputeDecision::create([
        'dispute_id' => $this->dispute->id,
        'decided_by' => $this->user->id,
        'decision' => 'refund_buyer',
        'reasoning' => 'Seller did not deliver.',
    ]);

    expect($decision->id)->not->toBeNull();
    expect($decision->checksum)->not->toBeEmpty();
    expect(strlen($decision->checksum))->toBe(64);
});

test('checksum verifies record integrity', function () {
    $decision = DisputeDecision::create([
        'dispute_id' => $this->dispute->id,
        'decided_by' => $this->user->id,
        'decision' => 'refund_buyer',
        'reasoning' => 'Seller did not deliver.',
    ]);

    expect($decision->verifyIntegrity())->toBeTrue();
});

test('attempting to update a DisputeDecision via ORM throws LogicException', function () {
    $decision = DisputeDecision::create([
        'dispute_id' => $this->dispute->id,
        'decided_by' => $this->user->id,
        'decision' => 'refund_buyer',
        'reasoning' => 'Seller did not deliver.',
    ]);

    $decision->reasoning = 'Tampered reasoning';
    $decision->save();
})->throws(LogicException::class, 'Dispute decisions are immutable and cannot be updated.');

test('attempting to delete a DisputeDecision via ORM throws LogicException', function () {
    $decision = DisputeDecision::create([
        'dispute_id' => $this->dispute->id,
        'decided_by' => $this->user->id,
        'decision' => 'refund_buyer',
        'reasoning' => 'Seller did not deliver.',
    ]);

    $decision->delete();
})->throws(LogicException::class, 'Dispute decisions are immutable and cannot be deleted.');

test('database trigger denies raw SQL update on dispute_decisions', function () {
    $decision = DisputeDecision::create([
        'dispute_id' => $this->dispute->id,
        'decided_by' => $this->user->id,
        'decision' => 'refund_buyer',
        'reasoning' => 'Original reasoning.',
    ]);

    try {
        DB::table('dispute_decisions')
            ->where('id', $decision->id)
            ->update(['reasoning' => 'Tampered via raw SQL']);
        $this->fail('Expected DB exception for raw update on immutable table');
    } catch (\Illuminate\Database\QueryException $e) {
        expect($e->getMessage())->toContain('immutable');
    }
});

test('database trigger denies raw SQL delete on dispute_decisions', function () {
    $decision = DisputeDecision::create([
        'dispute_id' => $this->dispute->id,
        'decided_by' => $this->user->id,
        'decision' => 'refund_buyer',
        'reasoning' => 'Original reasoning.',
    ]);

    try {
        DB::table('dispute_decisions')
            ->where('id', $decision->id)
            ->delete();
        $this->fail('Expected DB exception for raw delete on immutable table');
    } catch (\Illuminate\Database\QueryException $e) {
        expect($e->getMessage())->toContain('immutable');
    }
});
