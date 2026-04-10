<?php

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('username', 'user1')->first();
});

test('GET /api/notifications returns users notifications', function () {
    // Create some notifications for the user
    Notification::create([
        'user_id' => $this->user->id,
        'type' => NotificationType::Inbox,
        'title' => 'Test Notification 1',
        'body' => 'This is test notification 1',
        'rendered_locale' => 'en',
        'expires_at' => now()->addDays(90),
    ]);

    Notification::create([
        'user_id' => $this->user->id,
        'type' => NotificationType::Alert,
        'title' => 'Test Notification 2',
        'body' => 'This is test notification 2',
        'rendered_locale' => 'en',
        'expires_at' => now()->addDays(90),
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/notifications');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'current_page',
            'per_page',
            'total',
        ]);

    expect(count($response->json('data')))->toBeGreaterThanOrEqual(2);
});

test('GET /api/notifications/unread-count returns count', function () {
    // Create unread notifications
    Notification::create([
        'user_id' => $this->user->id,
        'type' => NotificationType::Inbox,
        'title' => 'Unread Notification',
        'body' => 'This is unread',
        'rendered_locale' => 'en',
        'expires_at' => now()->addDays(90),
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/notifications/unread-count');

    $response->assertStatus(200)
        ->assertJsonStructure(['unread_count']);

    expect($response->json('unread_count'))->toBeGreaterThanOrEqual(1);
});

test('POST /api/notifications/{id}/read marks as read', function () {
    $notification = Notification::create([
        'user_id' => $this->user->id,
        'type' => NotificationType::Inbox,
        'title' => 'Notification To Read',
        'body' => 'Mark me as read',
        'rendered_locale' => 'en',
        'expires_at' => now()->addDays(90),
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/notifications/{$notification->id}/read");

    $response->assertStatus(200);

    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();
});

test('POST /api/notifications/read-all marks all as read', function () {
    // Create multiple unread notifications
    Notification::create([
        'user_id' => $this->user->id,
        'type' => NotificationType::Inbox,
        'title' => 'Notification A',
        'body' => 'Body A',
        'rendered_locale' => 'en',
        'expires_at' => now()->addDays(90),
    ]);

    Notification::create([
        'user_id' => $this->user->id,
        'type' => NotificationType::Alert,
        'title' => 'Notification B',
        'body' => 'Body B',
        'rendered_locale' => 'en',
        'expires_at' => now()->addDays(90),
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/notifications/read-all');

    $response->assertStatus(200);

    // Check unread count is now 0
    $unreadCount = Notification::where('user_id', $this->user->id)
        ->whereNull('read_at')
        ->count();

    expect($unreadCount)->toBe(0);
});

test('GET /api/notifications with read=false returns only unread', function () {
    Notification::create([
        'user_id' => $this->user->id,
        'type' => NotificationType::Inbox,
        'title' => 'Unread One',
        'body' => 'Still unread',
        'rendered_locale' => 'en',
        'expires_at' => now()->addDays(90),
    ]);

    Notification::create([
        'user_id' => $this->user->id,
        'type' => NotificationType::Inbox,
        'title' => 'Read One',
        'body' => 'Already read',
        'rendered_locale' => 'en',
        'read_at' => now(),
        'expires_at' => now()->addDays(90),
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/notifications?read=false');

    $response->assertStatus(200);
    $data = $response->json('data');
    foreach ($data as $n) {
        expect($n['read_at'])->toBeNull();
    }
});

test('GET /api/notifications with read=true returns only read', function () {
    Notification::create([
        'user_id' => $this->user->id,
        'type' => NotificationType::Inbox,
        'title' => 'Read Notification',
        'body' => 'This was read',
        'rendered_locale' => 'en',
        'read_at' => now(),
        'expires_at' => now()->addDays(90),
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/notifications?read=true');

    $response->assertStatus(200);
    $data = $response->json('data');
    foreach ($data as $n) {
        expect($n['read_at'])->not->toBeNull();
    }
});

test('GET /api/notifications returns top-level pagination fields', function () {
    $response = $this->actingAs($this->user)->getJson('/api/notifications');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'current_page',
            'last_page',
            'per_page',
            'total',
        ]);
});
