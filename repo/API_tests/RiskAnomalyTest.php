<?php

use App\Enums\AnomalyType;
use App\Models\AnomalyFlag;
use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->moderator = User::where('username', 'mod1')->first();
    $this->user = User::where('username', 'user1')->first();
});

test('GET /api/risk/credit-scores returns list for moderator', function () {
    $response = $this->actingAs($this->moderator)->getJson('/api/risk/credit-scores');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [['id', 'user_id', 'score', 'restriction_level']],
        ]);

    expect(count($response->json('data')))->toBeGreaterThanOrEqual(1);
});

test('GET /api/risk/credit-scores returns 403 for regular user', function () {
    $response = $this->actingAs($this->user)->getJson('/api/risk/credit-scores');

    $response->assertStatus(403);
});

test('GET /api/risk/anomalies returns list for moderator', function () {
    $response = $this->actingAs($this->moderator)->getJson('/api/risk/anomalies');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('GET /api/risk/anomalies returns 403 for regular user', function () {
    $response = $this->actingAs($this->user)->getJson('/api/risk/anomalies');

    $response->assertStatus(403);
});

test('POST /api/risk/anomalies/{id}/resolve resolves anomaly', function () {
    $flag = AnomalyFlag::create([
        'user_id' => $this->user->id,
        'type' => AnomalyType::ExcessiveRefunds,
        'description' => 'Test anomaly',
        'evidence' => ['refund_count' => 5],
    ]);

    $response = $this->actingAs($this->moderator)->postJson("/api/risk/anomalies/{$flag->id}/resolve", [
        'resolution' => 'Reviewed and cleared',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $flag->id]);

    $flag->refresh();
    expect($flag->resolved_at)->not->toBeNull();
    expect($flag->resolved_by)->toBe($this->moderator->id);
});
