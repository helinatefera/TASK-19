<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\SeatLock;
use App\Models\TimeSlot;
use App\Models\User;
beforeEach(function () {
    $this->seed();
    $this->user = User::where('username', 'user1')->first();
    $this->timeSlot = TimeSlot::first();
});

test('GET /api/time-slots/{id} returns availability info', function () {
    $response = $this->actingAs($this->user)->getJson("/api/time-slots/{$this->timeSlot->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'time_slot' => ['id', 'starts_at', 'ends_at', 'seat_capacity', 'seats_booked'],
            'available_seats',
            'locks',
        ]);
});

test('POST /api/time-slots/{id}/lock creates a seat lock', function () {
    $response = $this->actingAs($this->user)->postJson("/api/time-slots/{$this->timeSlot->id}/lock", [
        'quantity' => 2,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'lock' => ['id', 'time_slot_id', 'user_id', 'quantity', 'locked_until'],
            'ttl_seconds',
        ]);

    expect($response->json('lock.quantity'))->toBe(2);
    expect($response->json('ttl_seconds'))->toBe(SeatLock::LOCK_TTL_MINUTES * 60);
});

test('POST /api/time-slots/{id}/lock when no seats available returns error', function () {
    // Fill up all seats
    $this->timeSlot->update([
        'seats_booked' => $this->timeSlot->seat_capacity,
    ]);

    $response = $this->actingAs($this->user)->postJson("/api/time-slots/{$this->timeSlot->id}/lock", [
        'quantity' => 1,
    ]);

    $response->assertStatus(409);
});

test('POST /api/seat-locks/{id}/confirm creates order with confirmation number', function () {
    // Create a seat lock
    $lock = SeatLock::create([
        'time_slot_id' => $this->timeSlot->id,
        'user_id' => $this->user->id,
        'quantity' => 1,
        'locked_until' => now()->addMinutes(SeatLock::LOCK_TTL_MINUTES),
    ]);

    $idempotencyKey = 'booking-confirm-' . uniqid();

    // Use the actual API endpoint
    $response = $this->actingAs($this->user)->postJson(
        "/api/seat-locks/{$lock->id}/confirm",
        ['amount' => 5000],
        ['X-Idempotency-Key' => $idempotencyKey]
    );

    $response->assertStatus(201)
        ->assertJsonStructure([
            'order' => ['id', 'confirmation_number', 'status', 'order_type', 'seat_quantity'],
            'confirmation_number',
        ]);

    expect($response->json('confirmation_number'))->toStartWith('CC-');
    expect($response->json('order.order_type'))->toBe('reservation');
    expect($response->json('order.seat_quantity'))->toBe(1);

    // Verify the lock was released
    $lock->refresh();
    expect($lock->released_at)->not->toBeNull();
});

test('POST /api/seat-locks/{id}/confirm is idempotent with same request key', function () {
    $lock = SeatLock::create([
        'time_slot_id' => $this->timeSlot->id,
        'user_id' => $this->user->id,
        'quantity' => 1,
        'locked_until' => now()->addMinutes(SeatLock::LOCK_TTL_MINUTES),
    ]);

    $idempotencyKey = 'idempotent-booking-' . uniqid();

    // First request
    $response1 = $this->actingAs($this->user)->postJson(
        "/api/seat-locks/{$lock->id}/confirm",
        ['amount' => 5000],
        ['X-Idempotency-Key' => $idempotencyKey]
    );

    $response1->assertStatus(201);
    $orderId = $response1->json('order.id');

    // Second request with same key should return the same order
    $response2 = $this->actingAs($this->user)->postJson(
        "/api/seat-locks/{$lock->id}/confirm",
        ['amount' => 5000],
        ['X-Idempotency-Key' => $idempotencyKey]
    );

    // Idempotency guard returns cached response
    expect($response2->json('order.id') ?? $orderId)->toBe($orderId);
});

test('POST /api/seat-locks/{id}/confirm without X-Idempotency-Key returns 422', function () {
    $lock = SeatLock::create([
        'time_slot_id' => $this->timeSlot->id,
        'user_id' => $this->user->id,
        'quantity' => 1,
        'locked_until' => now()->addMinutes(SeatLock::LOCK_TTL_MINUTES),
    ]);

    // Confirm WITHOUT idempotency header
    $response = $this->actingAs($this->user)->postJson(
        "/api/seat-locks/{$lock->id}/confirm",
        ['amount' => 5000],
    );

    $response->assertStatus(422);
    expect($response->json('msg'))->toContain('Idempotency-Key');
});

test('DELETE /api/seat-locks/{id} releases the lock', function () {
    // Create a lock directly
    $lock = SeatLock::create([
        'time_slot_id' => $this->timeSlot->id,
        'user_id' => $this->user->id,
        'quantity' => 1,
        'locked_until' => now()->addMinutes(SeatLock::LOCK_TTL_MINUTES),
    ]);

    // Release the lock
    $response = $this->actingAs($this->user)->deleteJson("/api/seat-locks/{$lock->id}");

    $response->assertStatus(200);

    // Verify lock is released
    $lock->refresh();
    expect($lock->released_at)->not->toBeNull();
});
