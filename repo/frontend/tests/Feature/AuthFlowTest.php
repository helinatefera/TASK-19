<?php

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('login page is accessible', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('login proxies credentials to backend API', function () {
    Http::fake([
        '*/api/auth/login' => Http::response([
            'user' => [
                'id' => 1,
                'username' => 'admin1',
                'display_name' => 'Admin User',
                'email' => null,
                'locale' => 'en',
                'timezone' => 'UTC',
                'roles' => [['name' => 'admin']],
            ],
            'token' => 'test-bearer-token-123',
        ]),
    ]);

    $response = $this->post('/login', [
        'username' => 'admin1',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');

    Http::assertSent(fn ($request) =>
        str_contains($request->url(), '/api/auth/login')
        && $request['username'] === 'admin1'
    );
});

test('login with invalid credentials shows error', function () {
    Http::fake([
        '*/api/auth/login' => Http::response(['msg' => 'Invalid credentials'], 401),
    ]);

    $response = $this->post('/login', [
        'username' => 'wrong',
        'password' => 'wrong',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('username');
});

test('authenticated routes redirect to login when not authenticated', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('logout clears session and redirects to login', function () {
    Http::fake([
        '*/api/auth/logout' => Http::response([]),
    ]);

    $response = $this->withSession([
        'api_token' => 'test-token',
        'api_user' => [
            'id' => 1,
            'username' => 'testuser',
            'roles' => ['user'],
            'permissions' => [],
        ],
    ])->post('/logout');

    $response->assertRedirect('/login');
});
