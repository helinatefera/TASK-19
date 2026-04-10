<?php

use App\Models\User;

beforeEach(function () {
    $this->seed();
});

test('POST /api/auth/login with valid credentials returns 200 with token and user', function () {
    $response = $this->postJson('/api/auth/login', [
        'username' => 'user1',
        'password' => 'User123!@#',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => ['id', 'username', 'display_name', 'roles'],
            'token',
        ]);
});

test('POST /api/auth/login with invalid password returns 401', function () {
    $response = $this->postJson('/api/auth/login', [
        'username' => 'user1',
        'password' => 'WrongPassword123',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'code' => 401,
            'msg' => 'Invalid credentials.',
        ]);
});

test('POST /api/auth/login with nonexistent user returns 401', function () {
    $response = $this->postJson('/api/auth/login', [
        'username' => 'nonexistent_user',
        'password' => 'SomePassword123',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'code' => 401,
            'msg' => 'Invalid credentials.',
        ]);
});

test('POST /api/auth/login without username returns 422', function () {
    $response = $this->postJson('/api/auth/login', [
        'password' => 'SomePassword123',
    ]);

    $response->assertStatus(422);
});

test('GET /api/auth/me without token returns 401', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401)
        ->assertJson([
            'code' => 401,
            'msg' => 'Unauthenticated.',
        ]);
});

test('GET /api/auth/me with valid token returns user data', function () {
    $user = User::where('username', 'user1')->first();

    $response = $this->actingAs($user)->getJson('/api/auth/me');

    $response->assertStatus(200)
        ->assertJsonStructure(['id', 'username', 'display_name', 'roles']);
});

test('POST /api/auth/logout invalidates token', function () {
    // Login to get a real token
    $loginResponse = $this->postJson('/api/auth/login', [
        'username' => 'user1',
        'password' => 'User123!@#',
    ]);

    $token = $loginResponse->json('token');

    // Logout using the real token
    $logoutResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/auth/logout');

    $logoutResponse->assertStatus(200);

    // Verify the user has no tokens left in the database
    $user = User::where('username', 'user1')->first();
    expect($user->tokens()->count())->toBe(0);
});
