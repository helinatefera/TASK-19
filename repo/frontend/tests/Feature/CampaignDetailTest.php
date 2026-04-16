<?php

use App\Livewire\Campaign\CampaignDetail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

beforeEach(function () {
    Session::put('api_token', 'test-token');
    Session::put('api_user', [
        'id' => 1,
        'username' => 'user1',
        'display_name' => 'Test User',
        'roles' => [['name' => 'user']],
        'locale' => 'en',
        'timezone' => 'UTC',
    ]);
});

test('campaign detail loads and renders campaign title', function () {
    Http::fake([
        '*/api/campaigns/1/reviews*' => Http::response(['data' => []]),
        '*/api/campaigns/1*' => Http::response([
            'campaign' => [
                'id' => 1,
                'title' => 'Community Garden Fund',
                'description' => 'A great campaign',
                'status' => 'fundraising',
                'visibility' => 'online',
                'target_amount' => 2500000,
                'pledged_amount' => 500000,
                'creator_id' => 99,
                'ends_at' => now()->addDays(10)->toIso8601String(),
                'reward_tiers' => [],
                'creator' => ['id' => 99, 'display_name' => 'Creator'],
            ],
        ]),
    ]);

    Livewire::test(CampaignDetail::class, ['campaignId' => 1])
        ->assertStatus(200)
        ->assertSee('Community Garden Fund');
});

test('contribute generates session-backed idempotency key and calls orders API', function () {
    Http::fake([
        '*/api/orders' => Http::response(['msg' => 'Server error'], 500),
        '*/api/campaigns/1/reviews*' => Http::response(['data' => []]),
        '*/api/campaigns/1*' => Http::response([
            'campaign' => [
                'id' => 1,
                'title' => 'Test Campaign',
                'status' => 'fundraising',
                'visibility' => 'online',
                'target_amount' => 100000,
                'pledged_amount' => 0,
                'creator_id' => 99,
                'reward_tiers' => [],
                'creator' => ['id' => 99, 'display_name' => 'Creator'],
            ],
        ]),
    ]);

    $component = Livewire::test(CampaignDetail::class, ['campaignId' => 1])
        ->call('contribute', 5);

    $key = session('idempotency:contribute:1:5');
    expect($key)->not->toBeNull()->toStartWith('contrib-1-5-');

    // Retry uses same key
    $component->call('contribute', 5);
    expect(session('idempotency:contribute:1:5'))->toBe($key);

    // Verify both calls sent the same X-Idempotency-Key
    $orderRequests = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), '/api/orders'))
        ->values();

    expect($orderRequests)->toHaveCount(2);
    expect($orderRequests[0][0]->header('X-Idempotency-Key')[0])
        ->toBe($orderRequests[1][0]->header('X-Idempotency-Key')[0]);
});

test('campaign detail does not expose raw exception messages on load error', function () {
    Http::fake([
        '*/api/campaigns/1/reviews*' => Http::response(['data' => []]),
        '*/api/campaigns/1*' => Http::response(['msg' => 'DB connection failed: host=postgres password=secret'], 500),
    ]);

    Livewire::test(CampaignDetail::class, ['campaignId' => 1])
        ->assertSessionHas('error', 'Something went wrong. Please try again.')
        ->assertDontSee('DB connection failed');
});
