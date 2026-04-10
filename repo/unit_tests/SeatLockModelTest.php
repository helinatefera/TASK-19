<?php

use App\Models\SeatLock;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\VenueProgram;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->venueProgram = VenueProgram::create([
        'title' => 'Test Program',
        'slug' => 'test-program-' . uniqid(),
        'description' => 'Test description',
        'status' => 'published',
        'visibility' => 'online',
        'location' => 'Test Location',
        'created_by' => $this->user->id,
    ]);
    $this->timeSlot = TimeSlot::create([
        'programable_type' => VenueProgram::class,
        'programable_id' => $this->venueProgram->id,
        'starts_at' => now()->addWeek(),
        'ends_at' => now()->addWeek()->addHours(3),
        'seat_capacity' => 50,
        'seats_booked' => 0,
    ]);
});

test('LOCK_TTL_MINUTES constant is 5', function () {
    expect(SeatLock::LOCK_TTL_MINUTES)->toBe(5);
});

test('active scope excludes released locks', function () {
    SeatLock::create([
        'time_slot_id' => $this->timeSlot->id,
        'user_id' => $this->user->id,
        'quantity' => 1,
        'locked_until' => now()->addMinutes(5),
        'released_at' => now(),
    ]);

    $activeLocks = SeatLock::active()->count();

    expect($activeLocks)->toBe(0);
});

test('active scope excludes expired locks', function () {
    SeatLock::create([
        'time_slot_id' => $this->timeSlot->id,
        'user_id' => $this->user->id,
        'quantity' => 1,
        'locked_until' => now()->subMinute(),
    ]);

    $activeLocks = SeatLock::active()->count();

    expect($activeLocks)->toBe(0);
});

test('active scope includes valid locks', function () {
    SeatLock::create([
        'time_slot_id' => $this->timeSlot->id,
        'user_id' => $this->user->id,
        'quantity' => 1,
        'locked_until' => now()->addMinutes(5),
    ]);

    $activeLocks = SeatLock::active()->count();

    expect($activeLocks)->toBe(1);
});

test('expired scope includes locks past locked_until', function () {
    SeatLock::create([
        'time_slot_id' => $this->timeSlot->id,
        'user_id' => $this->user->id,
        'quantity' => 1,
        'locked_until' => now()->subMinute(),
    ]);

    $expiredLocks = SeatLock::expired()->count();

    expect($expiredLocks)->toBe(1);
});

test('expired scope excludes active locks', function () {
    SeatLock::create([
        'time_slot_id' => $this->timeSlot->id,
        'user_id' => $this->user->id,
        'quantity' => 1,
        'locked_until' => now()->addMinutes(5),
    ]);

    $expiredLocks = SeatLock::expired()->count();

    expect($expiredLocks)->toBe(0);
});

test('expired scope excludes released locks', function () {
    SeatLock::create([
        'time_slot_id' => $this->timeSlot->id,
        'user_id' => $this->user->id,
        'quantity' => 1,
        'locked_until' => now()->subMinute(),
        'released_at' => now()->subMinute(),
    ]);

    $expiredLocks = SeatLock::expired()->count();

    expect($expiredLocks)->toBe(0);
});
