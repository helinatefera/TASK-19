<?php

beforeEach(function () {
    $this->seed();
});

test('invalid route returns 404 with JSON', function () {
    $response = $this->getJson('/api/nonexistent-endpoint');

    $response->assertStatus(404)
        ->assertJsonStructure(['code', 'msg']);

    expect($response->json('code'))->toBe(404);
});

test('method not allowed returns 405 with JSON', function () {
    // Try to PATCH on a GET-only route
    $response = $this->patchJson('/api/campaigns');

    $response->assertStatus(405)
        ->assertJsonStructure(['code', 'msg']);

    expect($response->json('code'))->toBe(405);
});

test('all error responses have code and msg fields for 404', function () {
    $response = $this->getJson('/api/does-not-exist');

    $response->assertStatus(404);

    $data = $response->json();
    expect($data)->toHaveKey('code');
    expect($data)->toHaveKey('msg');
});

test('all error responses have code and msg fields for 401', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401);

    $data = $response->json();
    expect($data)->toHaveKey('code');
    expect($data)->toHaveKey('msg');
});

test('accessing protected route without auth returns 401 with code and msg', function () {
    $response = $this->getJson('/api/orders');

    $response->assertStatus(401)
        ->assertJsonStructure(['code', 'msg']);
});

test('non-api 404 path returns JSON when Accept header is set', function () {
    $response = $this->getJson('/api/completely/invalid/path/here');

    $response->assertStatus(404)
        ->assertJsonStructure(['code', 'msg']);
});
