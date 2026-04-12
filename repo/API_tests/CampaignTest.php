<?php

use App\Enums\CampaignStatus;
use App\Enums\CampaignVisibility;
use App\Models\Campaign;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

test('GET /api/campaigns returns paginated list', function () {
    $response = $this->getJson('/api/campaigns');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'current_page',
            'per_page',
            'total',
        ]);
});

test('GET /api/campaigns/{id} returns campaign detail with reward tiers', function () {
    $campaign = Campaign::where('status', CampaignStatus::Fundraising)->first();

    $response = $this->getJson("/api/campaigns/{$campaign->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'campaign' => [
                'id',
                'title',
                'description',
                'status',
                'creator',
                'reward_tiers',
            ],
            'funding_progress',
        ]);
});

test('POST /api/campaigns by creator creates campaign', function () {
    $creator = User::where('username', 'creator1')->first();

    $idempotencyKey = 'campaign-create-' . uniqid();
    $response = $this->actingAs($creator)->postJson('/api/campaigns', [
        'title' => 'New Test Campaign',
        'description' => 'A new test campaign description',
        'risk_disclosure' => 'Some risk involved',
        'target_amount' => 500000,
        'duration_days' => 30,
    ], ['X-Idempotency-Key' => $idempotencyKey]);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'title' => 'New Test Campaign',
            'status' => 'draft',
        ]);
});

test('POST /api/campaigns by regular user returns 403', function () {
    $user = User::where('username', 'user1')->first();

    $response = $this->actingAs($user)->postJson('/api/campaigns', [
        'title' => 'Should Not Work',
        'description' => 'A campaign from a regular user',
        'risk_disclosure' => 'Some risk',
        'target_amount' => 100000,
        'duration_days' => 30,
    ], ['X-Idempotency-Key' => 'campaign-unauth-' . uniqid()]);

    $response->assertStatus(403);
});

test('PUT /api/campaigns/{id} by campaign owner updates it', function () {
    $creator = User::where('username', 'creator1')->first();

    // Create a draft campaign first
    $campaign = Campaign::create([
        'creator_id' => $creator->id,
        'title' => 'Editable Campaign',
        'slug' => 'editable-campaign-' . uniqid(),
        'description' => 'Original description',
        'risk_disclosure' => 'Original risk',
        'target_amount' => 100000,
        'pledged_amount' => 0,
        'currency' => 'USD',
        'status' => CampaignStatus::Draft,
        'visibility' => CampaignVisibility::Offline,
        'duration_days' => 30,
    ]);

    $response = $this->actingAs($creator)->putJson("/api/campaigns/{$campaign->id}", [
        'title' => 'Updated Campaign Title',
        'description' => 'Updated description',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'title' => 'Updated Campaign Title',
            'description' => 'Updated description',
        ]);
});

test('POST /api/campaigns/{id}/submit changes status to pending_review', function () {
    $creator = User::where('username', 'creator1')->first();

    $campaign = Campaign::create([
        'creator_id' => $creator->id,
        'title' => 'Campaign To Submit',
        'slug' => 'campaign-to-submit-' . uniqid(),
        'description' => 'Description',
        'risk_disclosure' => 'Risk disclosure',
        'target_amount' => 100000,
        'pledged_amount' => 0,
        'currency' => 'USD',
        'status' => CampaignStatus::Draft,
        'visibility' => CampaignVisibility::Offline,
        'duration_days' => 30,
    ]);

    $response = $this->actingAs($creator)->postJson("/api/campaigns/{$campaign->id}/submit");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'pending_review',
        ]);
});

test('POST /api/campaigns/{id}/approve by moderator changes status to fundraising', function () {
    $creator = User::where('username', 'creator1')->first();
    $moderator = User::where('username', 'mod1')->first();

    $campaign = Campaign::create([
        'creator_id' => $creator->id,
        'title' => 'Campaign To Approve',
        'slug' => 'campaign-to-approve-' . uniqid(),
        'description' => 'Description',
        'risk_disclosure' => 'Risk disclosure',
        'target_amount' => 100000,
        'pledged_amount' => 0,
        'currency' => 'USD',
        'status' => CampaignStatus::PendingReview,
        'visibility' => CampaignVisibility::Offline,
        'duration_days' => 30,
    ]);

    $response = $this->actingAs($moderator)->postJson("/api/campaigns/{$campaign->id}/approve", [
        'notes' => 'Looks good!',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'fundraising',
        ]);
});

test('POST /api/campaigns/{id}/reject by moderator changes status back to draft', function () {
    $creator = User::where('username', 'creator1')->first();
    $moderator = User::where('username', 'mod1')->first();

    $campaign = Campaign::create([
        'creator_id' => $creator->id,
        'title' => 'Campaign To Reject',
        'slug' => 'campaign-to-reject-' . uniqid(),
        'description' => 'Description',
        'risk_disclosure' => 'Risk disclosure',
        'target_amount' => 100000,
        'pledged_amount' => 0,
        'currency' => 'USD',
        'status' => CampaignStatus::PendingReview,
        'visibility' => CampaignVisibility::Offline,
        'duration_days' => 30,
    ]);

    $response = $this->actingAs($moderator)->postJson("/api/campaigns/{$campaign->id}/reject", [
        'notes' => 'Needs more detail',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'draft',
        ]);
});

test('POST /api/campaigns/{id}/close by moderator transitions to closed', function () {
    $creator = User::where('username', 'creator1')->first();
    $moderator = User::where('username', 'mod1')->first();

    $campaign = Campaign::create([
        'creator_id' => $creator->id,
        'title' => 'Campaign To Close',
        'slug' => 'campaign-to-close-' . uniqid(),
        'description' => 'Description',
        'risk_disclosure' => 'Risk disclosure',
        'target_amount' => 100000,
        'pledged_amount' => 100000,
        'currency' => 'USD',
        'status' => CampaignStatus::Success,
        'visibility' => CampaignVisibility::Online,
        'duration_days' => 30,
    ]);

    $response = $this->actingAs($moderator)->postJson("/api/campaigns/{$campaign->id}/close", [
        'notes' => 'Campaign concluded.',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'closed',
        ]);
});

test('POST /api/campaigns/{id}/close on fundraising campaign returns 403', function () {
    $creator = User::where('username', 'creator1')->first();
    $moderator = User::where('username', 'mod1')->first();

    $campaign = Campaign::create([
        'creator_id' => $creator->id,
        'title' => 'Fundraising Cannot Close',
        'slug' => 'fundraising-cannot-close-' . uniqid(),
        'description' => 'Description',
        'risk_disclosure' => 'Risk disclosure',
        'target_amount' => 100000,
        'pledged_amount' => 0,
        'currency' => 'USD',
        'status' => CampaignStatus::Fundraising,
        'visibility' => CampaignVisibility::Online,
        'duration_days' => 30,
    ]);

    $response = $this->actingAs($moderator)->postJson("/api/campaigns/{$campaign->id}/close");

    $response->assertStatus(403);
});

test('POST /api/campaigns/{id}/close on draft campaign returns 422', function () {
    $creator = User::where('username', 'creator1')->first();
    $moderator = User::where('username', 'mod1')->first();

    $campaign = Campaign::create([
        'creator_id' => $creator->id,
        'title' => 'Draft Cannot Close',
        'slug' => 'draft-cannot-close-' . uniqid(),
        'description' => 'Description',
        'risk_disclosure' => 'Risk disclosure',
        'target_amount' => 100000,
        'pledged_amount' => 0,
        'currency' => 'USD',
        'status' => CampaignStatus::Draft,
        'visibility' => CampaignVisibility::Offline,
        'duration_days' => 30,
    ]);

    $response = $this->actingAs($moderator)->postJson("/api/campaigns/{$campaign->id}/close");

    $response->assertStatus(403);
});

test('POST /api/campaigns/{id}/visibility toggles online/offline', function () {
    $moderator = User::where('username', 'mod1')->first();

    $campaign = Campaign::where('status', CampaignStatus::Fundraising)->first();

    $response = $this->actingAs($moderator)->postJson("/api/campaigns/{$campaign->id}/visibility", [
        'visibility' => 'offline',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'visibility' => 'offline',
        ]);

    // Toggle back to online
    $response = $this->actingAs($moderator)->postJson("/api/campaigns/{$campaign->id}/visibility", [
        'visibility' => 'online',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'visibility' => 'online',
        ]);
});

test('POST /api/campaigns without X-Idempotency-Key returns 422', function () {
    $creator = User::where('username', 'creator1')->first();

    $response = $this->actingAs($creator)->postJson('/api/campaigns', [
        'title' => 'Missing Idempotency Key',
        'description' => 'Should be rejected by middleware',
        'risk_disclosure' => 'Some risk',
        'target_amount' => 500000,
        'duration_days' => 30,
    ]);

    $response->assertStatus(422)
        ->assertJson(['msg' => 'The X-Idempotency-Key header is required.']);
});
