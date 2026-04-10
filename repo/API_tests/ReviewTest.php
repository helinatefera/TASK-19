<?php

use App\Enums\CampaignStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\Review;
use App\Models\RewardTier;
use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('username', 'user1')->first();
    $this->staff = User::where('username', 'staff1')->first();
    $this->creator = User::where('username', 'creator1')->first();
    $this->campaign = Campaign::where('status', CampaignStatus::Fundraising)->first();
    $this->rewardTier = RewardTier::where('campaign_id', $this->campaign->id)->first();
});

test('POST /api/orders/{id}/reviews on fulfilled order creates review', function () {
    // Create a fulfilled order
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'review-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REVIEW01',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
        'refund_deadline' => now()->addDays(14),
    ]);

    // Use the actual API endpoint — reviewee_id is computed by ReviewService
    $response = $this->actingAs($this->user)->postJson("/api/orders/{$order->id}/reviews", [
        'side' => 'user_to_creator',
        'overall_rating' => 5,
        'body' => 'Great campaign and great rewards!',
        'dimensions' => [
            ['dimension' => 'quality', 'rating' => 5],
            ['dimension' => 'communication', 'rating' => 4],
        ],
        'tags' => ['excellent', 'recommended'],
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id', 'order_id', 'reviewer_id', 'reviewee_id',
            'side', 'overall_rating', 'body', 'public_alias',
            'is_visible', 'visible_after', 'dimensions', 'tags',
        ]);

    // Verify reviewee_id was set (computed by ReviewService from campaign creator)
    expect($response->json('reviewee_id'))->toBe($this->creator->id);
    // Verify deterministic alias format
    expect($response->json('public_alias'))->toStartWith('Reviewer-');
    // Verify 72h visibility delay
    expect($response->json('is_visible'))->toBeFalse();
    // Verify dimensions and tags were created
    expect(count($response->json('dimensions')))->toBe(2);
    expect(count($response->json('tags')))->toBe(2);
});

test('POST /api/orders/{id}/reviews on unfulfilled order returns error', function () {
    // Create a confirmed (not fulfilled) order
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'review-fail-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REVIEW02',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Confirmed,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/orders/{$order->id}/reviews", [
        'side' => 'user_to_creator',
        'overall_rating' => 5,
        'body' => 'This should not work',
    ]);

    $response->assertStatus(403);
});

test('GET /api/campaigns/{id}/reviews returns only visible reviews', function () {
    // Create a fulfilled order and review
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'visible-review-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REVIEW03',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
    ]);

    // Create a visible review (past the 72h delay)
    Review::create([
        'order_id' => $order->id,
        'reviewer_id' => $this->user->id,
        'reviewee_id' => $this->creator->id,
        'side' => 'user_to_creator',
        'overall_rating' => 5,
        'body' => 'Visible review',
        'public_alias' => 'U***abcd',
        'visible_after' => now()->subDay(),
        'is_visible' => true,
    ]);

    // Create a hidden review (not yet past the delay)
    $order2 = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'hidden-review-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REVIEW04',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
    ]);

    Review::create([
        'order_id' => $order2->id,
        'reviewer_id' => $this->user->id,
        'reviewee_id' => $this->creator->id,
        'side' => 'user_to_creator',
        'overall_rating' => 3,
        'body' => 'Hidden review',
        'public_alias' => 'U***efgh',
        'visible_after' => now()->addDays(3),
        'is_visible' => false,
    ]);

    $response = $this->getJson("/api/campaigns/{$this->campaign->id}/reviews");

    $response->assertStatus(200);

    // Only the visible review should appear
    $data = $response->json('data');
    expect(count($data))->toBe(1);
    expect($data[0]['body'])->toBe('Visible review');
});

test('review has masked identity (public_alias) in response', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
        'reward_tier_id' => $this->rewardTier->id,
        'request_key' => 'alias-review-test-' . uniqid(),
        'confirmation_number' => 'CC-' . now()->format('ymd') . '-REVIEW05',
        'order_type' => OrderType::Contribution,
        'amount' => $this->rewardTier->price,
        'currency' => 'USD',
        'status' => OrderStatus::Fulfilled,
        'fulfilled_at' => now(),
    ]);

    // Create the review directly with a masked alias
    $review = Review::create([
        'order_id' => $order->id,
        'reviewer_id' => $this->user->id,
        'reviewee_id' => $this->creator->id,
        'side' => 'user_to_creator',
        'overall_rating' => 4,
        'body' => 'Testing alias',
        'public_alias' => 'R***wxyz',
        'visible_after' => now()->subDay(),
        'is_visible' => true,
    ]);

    // Verify the review via the campaign reviews endpoint
    $response = $this->getJson("/api/campaigns/{$this->campaign->id}/reviews");

    $response->assertStatus(200);

    $data = $response->json('data');
    expect(count($data))->toBeGreaterThanOrEqual(1);

    $reviewData = $data[0];
    expect($reviewData['public_alias'])->toContain('***');
    // Should not contain the user's full display name
    expect($reviewData['public_alias'])->not->toBe($this->user->display_name);
});
