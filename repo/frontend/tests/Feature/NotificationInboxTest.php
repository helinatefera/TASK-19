<?php

use App\Livewire\Notification\NotificationInbox;
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
    ]);
});

test('notification inbox renders notifications from API', function () {
    Http::fake([
        '*/api/notifications*' => Http::response([
            'data' => [
                ['id' => 'abc', 'title' => 'Order Created', 'body' => 'Your order was placed.', 'read_at' => null, 'type' => 'inbox', 'created_at' => now()->toIso8601String()],
            ],
            'current_page' => 1,
            'last_page' => 1,
            'total' => 1,
            'per_page' => 20,
        ]),
    ]);

    Livewire::test(NotificationInbox::class)
        ->assertStatus(200)
        ->assertSee('Order Created');
});

test('notification inbox markAsRead calls backend', function () {
    Http::fake([
        '*/api/notifications/abc/read' => Http::response(['ok' => true]),
        '*/api/notifications*' => Http::response([
            'data' => [],
            'current_page' => 1,
            'last_page' => 1,
            'total' => 0,
            'per_page' => 20,
        ]),
    ]);

    Livewire::test(NotificationInbox::class)
        ->call('markAsRead', 'abc');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/notifications/abc/read'));
});

test('notification inbox does not expose raw exception messages', function () {
    Http::fake([
        '*/api/notifications*' => Http::response(['msg' => 'Internal: pgsql connection refused'], 500),
    ]);

    Livewire::test(NotificationInbox::class)
        ->assertSessionHas('error', 'Something went wrong. Please try again.')
        ->assertDontSee('pgsql connection refused');
});
