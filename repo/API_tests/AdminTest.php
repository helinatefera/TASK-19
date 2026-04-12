<?php

use App\Models\AuditLog;
use App\Models\BusinessParameter;
use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->admin = User::where('username', 'admin')->first();
    $this->user = User::where('username', 'user1')->first();
});

test('GET /api/admin/users by admin lists users', function () {
    $response = $this->actingAs($this->admin)->getJson('/api/admin/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'display_name', 'roles'],
            ],
            'current_page',
            'per_page',
            'total',
        ]);
});

test('GET /api/admin/users by non-admin returns 403', function () {
    $response = $this->actingAs($this->user)->getJson('/api/admin/users');

    $response->assertStatus(403);
});

test('POST /api/admin/users creates new user', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/admin/users', [
        'username' => 'newuser_' . uniqid(),
        'password' => 'NewUser123!@#',
        'display_name' => 'New User',
        'roles' => ['user'],
    ], ['X-Idempotency-Key' => 'admin-create-user-' . uniqid()]);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'display_name' => 'New User',
        ]);
});

test('GET /api/admin/business-parameters lists parameters', function () {
    $response = $this->actingAs($this->admin)->getJson('/api/admin/business-parameters');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'key', 'value', 'type'],
            ],
        ]);

    expect(count($response->json('data')))->toBeGreaterThanOrEqual(1);
});

test('PUT /api/admin/business-parameters/{key} updates a parameter', function () {
    $response = $this->actingAs($this->admin)->putJson('/api/admin/business-parameters/cancellation_window_hours', [
        'value' => '4',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'key' => 'cancellation_window_hours',
            'value' => '4',
        ]);
});

test('GET /api/admin/audit-logs lists logs', function () {
    // Create an audit log entry
    AuditLog::create([
        'actor_id' => $this->admin->id,
        'actor_ip' => '127.0.0.1',
        'action' => 'test.action',
        'auditable_type' => User::class,
        'auditable_id' => $this->admin->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/audit-logs');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'actor_id', 'action'],
            ],
        ]);
});

test('mutation requests generate audit log entries automatically', function () {
    $initialCount = AuditLog::count();

    // Perform a real mutation: update a business parameter
    $this->actingAs($this->admin)->putJson('/api/admin/business-parameters/cancellation_window_hours', [
        'value' => '3',
    ]);

    // The AuditRequest middleware should have generated an audit entry
    expect(AuditLog::count())->toBeGreaterThan($initialCount);

    $latestLog = AuditLog::latest('id')->first();
    expect($latestLog->actor_id)->toBe($this->admin->id);
    expect($latestLog->action)->not->toBeEmpty();
});
