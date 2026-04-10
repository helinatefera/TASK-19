<?php

use App\Livewire\Notification\NotificationBell;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

beforeEach(function () {
    Session::put('api_token', 'test-token');
    Session::put('api_user', [
        'id' => 1,
        'username' => 'testuser',
        'roles' => ['user'],
        'permissions' => [],
    ]);
});

test('notification bell fetches unread count from API', function () {
    Http::fake([
        '*/api/notifications/unread-count*' => Http::response([
            'unread_count' => 5,
        ]),
    ]);

    Livewire::test(NotificationBell::class)
        ->assertStatus(200);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/notifications/unread-count'));
});
