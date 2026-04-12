<?php

use App\Services\ApiClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

test('ApiClient sends bearer token from session', function () {
    Session::put('api_token', 'my-bearer-token');

    Http::fake([
        '*' => Http::response(['data' => 'ok']),
    ]);

    $client = new ApiClient();
    $client->get('/api/test');

    Http::assertSent(fn ($request) =>
        $request->hasHeader('Authorization', 'Bearer my-bearer-token')
    );
});

test('ApiClient get sends query parameters', function () {
    Http::fake([
        '*' => Http::response(['data' => []]),
    ]);

    $client = new ApiClient();
    $client->get('/api/campaigns', ['status' => 'fundraising', 'page' => 2]);

    Http::assertSent(fn ($request) =>
        str_contains($request->url(), 'status=fundraising')
        && str_contains($request->url(), 'page=2')
    );
});

test('ApiClient post sends JSON body', function () {
    Http::fake([
        '*' => Http::response(['id' => 1]),
    ]);

    $client = new ApiClient();
    $client->post('/api/orders', ['campaign_id' => 5, 'request_key' => 'test-key']);

    Http::assertSent(fn ($request) =>
        $request['campaign_id'] === 5
        && $request['request_key'] === 'test-key'
    );
});

test('ApiClient throws RuntimeException on 4xx errors', function () {
    Http::fake([
        '*' => Http::response(['msg' => 'Validation failed'], 422),
    ]);

    $client = new ApiClient();

    expect(fn () => $client->post('/api/test', []))->toThrow(
        RuntimeException::class,
        'Validation failed'
    );
});

test('ApiClient clears session on 401', function () {
    Session::put('api_token', 'expired-token');
    Session::put('api_user', ['id' => 1]);

    Http::fake([
        '*' => Http::response(['msg' => 'Unauthenticated'], 401),
    ]);

    $client = new ApiClient();

    expect(fn () => $client->get('/api/me'))->toThrow(RuntimeException::class);
    expect(Session::get('api_token'))->toBeNull();
    expect(Session::get('api_user'))->toBeNull();
});

test('ApiClient post forwards explicit X-Idempotency-Key header', function () {
    Http::fake([
        '*' => Http::response(['id' => 1]),
    ]);

    $client = new ApiClient();
    $client->post('/api/orders', ['campaign_id' => 5], [
        'X-Idempotency-Key' => 'my-stable-key-123',
    ]);

    Http::assertSent(fn ($request) =>
        $request->hasHeader('X-Idempotency-Key', 'my-stable-key-123')
    );
});

test('ApiClient post does not auto-generate X-Idempotency-Key when none provided', function () {
    Http::fake([
        '*' => Http::response(['ok' => true]),
    ]);

    $client = new ApiClient();
    $client->post('/api/campaigns/1/approve', []);

    Http::assertSent(fn ($request) =>
        ! $request->hasHeader('X-Idempotency-Key')
    );
});

test('ApiClient postWithFile forwards explicit X-Idempotency-Key header', function () {
    Http::fake([
        '*' => Http::response(['id' => 1]),
    ]);

    $client = new ApiClient();
    $client->postWithFile('/api/orders/1/after-sales', ['type' => 'complaint'], 'attachment', null, [
        'X-Idempotency-Key' => 'aftersales-stable-key',
    ]);

    Http::assertSent(fn ($request) =>
        $request->hasHeader('X-Idempotency-Key', 'aftersales-stable-key')
    );
});

test('ApiClient login does not require bearer token', function () {
    Http::fake([
        '*/api/auth/login' => Http::response([
            'user' => ['id' => 1, 'username' => 'test'],
            'token' => 'new-token',
        ]),
    ]);

    $client = new ApiClient();
    $result = $client->login('test', 'password');

    expect($result)->toHaveKey('token');
    Http::assertSent(fn ($request) =>
        ! $request->hasHeader('Authorization')
        && $request['username'] === 'test'
    );
});
