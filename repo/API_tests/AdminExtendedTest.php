<?php

use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('username', 'admin')->first();
    $this->user = User::where('username', 'user1')->first();
});

test('GET /api/admin/roles lists available roles', function () {
    $response = $this->actingAs($this->admin)->getJson('/api/admin/roles');

    $response->assertStatus(200);

    $data = $response->json('data') ?? $response->json();
    $names = array_column($data, 'name');
    expect($names)->toContain('admin')
        ->toContain('staff')
        ->toContain('moderator')
        ->toContain('creator')
        ->toContain('user');
});

test('PUT /api/admin/users/{id} updates user roles', function () {
    $target = User::where('username', 'user2')->first();

    $response = $this->actingAs($this->admin)->putJson("/api/admin/users/{$target->id}", [
        'roles' => ['user', 'creator'],
    ]);

    $response->assertStatus(200);

    // Verify roles actually changed
    $roles = array_column($response->json('roles') ?? [], 'name');
    expect($roles)->toContain('creator');
});

test('PUT /api/admin/users/{id} by non-admin returns 403', function () {
    $target = User::where('username', 'user2')->first();

    $response = $this->actingAs($this->user)->putJson("/api/admin/users/{$target->id}", [
        'roles' => ['admin'],
    ]);

    $response->assertStatus(403);
});

test('GET /api/admin/integration-stubs returns list', function () {
    $response = $this->actingAs($this->admin)->getJson('/api/admin/integration-stubs');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('GET /api/admin/webhook-definitions returns list', function () {
    $response = $this->actingAs($this->admin)->getJson('/api/admin/webhook-definitions');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('GET /api/admin/integration-stubs/{id} returns single stub', function () {
    // List stubs first to get an ID
    $listResponse = $this->actingAs($this->admin)->getJson('/api/admin/integration-stubs');
    $stubs = $listResponse->json('data') ?? $listResponse->json();

    if (empty($stubs)) {
        // Create one directly if seeder didn't
        $stub = \App\Models\IntegrationStub::create([
            'name' => 'test-stub',
            'description' => 'Test integration stub',
            'is_active' => true,
            'config' => ['key' => 'value'],
        ]);
        $stubId = $stub->id;
    } else {
        $stubId = $stubs[0]['id'];
    }

    $response = $this->actingAs($this->admin)->getJson("/api/admin/integration-stubs/{$stubId}");

    $response->assertStatus(200)
        ->assertJsonStructure(['id', 'name', 'is_active']);
});

test('PUT /api/admin/integration-stubs/{id} toggles active state', function () {
    $stub = \App\Models\IntegrationStub::first();
    if (! $stub) {
        $stub = \App\Models\IntegrationStub::create([
            'name' => 'toggle-stub',
            'description' => 'Stub to toggle',
            'is_active' => true,
            'config' => [],
        ]);
    }

    $response = $this->actingAs($this->admin)->putJson("/api/admin/integration-stubs/{$stub->id}", [
        'is_active' => false,
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['is_active' => false]);

    // Verify it persisted
    $stub->refresh();
    expect($stub->is_active)->toBeFalse();
});

test('POST /api/admin/webhook-definitions creates definition', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/admin/webhook-definitions', [
        'name' => 'Order Created Hook',
        'url' => 'http://localhost:9000/webhook',
        'events' => ['order.created'],
    ], ['X-Idempotency-Key' => 'webhook-create-' . uniqid()]);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'name' => 'Order Created Hook',
            'url' => 'http://localhost:9000/webhook',
        ]);

    expect($response->json('events'))->toContain('order.created');
    expect($response->json('id'))->not->toBeNull();
});

test('PUT /api/admin/webhook-definitions/{id} updates name and events', function () {
    // Create one first
    $createResponse = $this->actingAs($this->admin)->postJson('/api/admin/webhook-definitions', [
        'name' => 'Hook To Update',
        'url' => 'http://localhost:9001/hook',
        'events' => ['order.created'],
    ], ['X-Idempotency-Key' => 'webhook-for-update-' . uniqid()]);

    $webhookId = $createResponse->json('id');

    $response = $this->actingAs($this->admin)->putJson("/api/admin/webhook-definitions/{$webhookId}", [
        'name' => 'Updated Hook Name',
        'events' => ['order.created', 'order.cancelled'],
        'is_active' => false,
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'Updated Hook Name',
            'is_active' => false,
        ]);
    expect($response->json('events'))->toContain('order.cancelled');
});

test('DELETE /api/admin/webhook-definitions/{id} removes definition', function () {
    // Create one first
    $createResponse = $this->actingAs($this->admin)->postJson('/api/admin/webhook-definitions', [
        'name' => 'Hook To Delete',
        'url' => 'http://localhost:9002/hook',
        'events' => ['order.fulfilled'],
    ], ['X-Idempotency-Key' => 'webhook-for-delete-' . uniqid()]);

    $webhookId = $createResponse->json('id');

    $response = $this->actingAs($this->admin)->deleteJson("/api/admin/webhook-definitions/{$webhookId}");

    $response->assertStatus(204);

    // Confirm it's gone
    expect(\App\Models\WebhookDefinition::find($webhookId))->toBeNull();
});
