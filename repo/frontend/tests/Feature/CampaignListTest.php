<?php

use App\Livewire\Campaign\CampaignList;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

test('campaign list renders and calls backend API', function () {
    Http::fake([
        '*/api/campaigns*' => Http::response([
            'data' => [
                [
                    'id' => 1,
                    'title' => 'Test Campaign',
                    'slug' => 'test-campaign',
                    'status' => 'fundraising',
                    'visibility' => 'online',
                    'target_amount' => 100000,
                    'pledged_amount' => 25000,
                    'creator' => ['display_name' => 'Creator One'],
                    'reward_tiers' => [],
                ],
            ],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 15, 'total' => 1],
        ]),
    ]);

    Livewire::test(CampaignList::class)
        ->assertStatus(200)
        ->assertSee('Test Campaign');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/campaigns'));
});

test('campaign list handles search filter', function () {
    Http::fake([
        '*/api/campaigns*' => Http::response([
            'data' => [],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 15, 'total' => 0],
        ]),
    ]);

    Livewire::test(CampaignList::class)
        ->set('search', 'nonexistent')
        ->assertStatus(200);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'search=nonexistent'));
});
