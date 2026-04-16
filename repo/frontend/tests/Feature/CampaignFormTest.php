<?php

use App\Livewire\Campaign\CampaignForm;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

beforeEach(function () {
    Session::put('api_token', 'test-token');
    Session::put('api_user', [
        'id' => 1,
        'username' => 'creator1',
        'display_name' => 'Campaign Creator',
        'roles' => [['name' => 'creator']],
    ]);
});

test('campaign form renders for new campaign', function () {
    Livewire::test(CampaignForm::class)
        ->assertStatus(200)
        ->assertSet('campaignId', null)
        ->assertSet('duration_days', 30);
});

test('campaign form validates duration bounds 7-60', function () {
    Livewire::test(CampaignForm::class)
        ->set('title', 'Test Campaign')
        ->set('description', 'At least ten characters long')
        ->set('target_amount', 100)
        ->set('duration_days', 3)
        ->call('save')
        ->assertHasErrors(['duration_days']);
});

test('campaign form create uses session-backed idempotency key', function () {
    Http::fake([
        '*/api/campaigns' => Http::response([
            'campaign' => ['id' => 42, 'title' => 'New Campaign', 'status' => 'draft'],
        ], 201),
    ]);

    Livewire::test(CampaignForm::class)
        ->set('title', 'New Campaign')
        ->set('description', 'A brand new campaign for testing')
        ->set('risk_disclosure', 'Some risk info')
        ->set('target_amount', 500)
        ->set('duration_days', 30)
        ->call('save');

    // Key should be cleared after success
    expect(session('idempotency:campaign:create'))->toBeNull();

    Http::assertSent(fn ($request) =>
        str_contains($request->url(), '/api/campaigns')
        && $request->hasHeader('X-Idempotency-Key')
        && $request->method() === 'POST'
    );
});
