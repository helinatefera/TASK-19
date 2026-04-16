<?php

use App\Livewire\Booking\SeatMap;
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
        'locale' => 'en',
        'timezone' => 'UTC',
    ]);
});

test('seat map loads availability from API', function () {
    Http::fake([
        '*/api/time-slots/1' => Http::response([
            'time_slot' => [
                'id' => 1,
                'starts_at' => now()->addWeek()->toIso8601String(),
                'ends_at' => now()->addWeek()->addHours(3)->toIso8601String(),
                'seat_capacity' => 50,
                'seats_booked' => 10,
            ],
            'available_seats' => 40,
            'locks' => [],
        ]),
    ]);

    Livewire::test(SeatMap::class, ['timeSlotId' => 1])
        ->assertStatus(200)
        ->assertSet('availableSeats', 40)
        ->assertSet('totalCapacity', 50);
});

test('lockSeats sends session-backed idempotency key', function () {
    Http::fake([
        '*/api/time-slots/1/lock' => Http::response(['msg' => 'No seats'], 409),
        '*/api/time-slots/1' => Http::response([
            'time_slot' => ['id' => 1, 'seat_capacity' => 50, 'seats_booked' => 50],
            'available_seats' => 0,
            'locks' => [],
        ]),
    ]);

    $component = Livewire::test(SeatMap::class, ['timeSlotId' => 1])
        ->set('quantity', 2)
        ->call('lockSeats');

    $key = session('idempotency:lock:1');
    expect($key)->not->toBeNull()->toStartWith('lock-1-');

    // Retry reuses same key
    $component->call('lockSeats');
    expect(session('idempotency:lock:1'))->toBe($key);
});

test('confirmBooking uses stable session-derived key', function () {
    Http::fake([
        '*/api/seat-locks/42/confirm' => Http::response([
            'order' => ['id' => 100, 'confirmation_number' => 'CC-260416-TEST01'],
            'confirmation_number' => 'CC-260416-TEST01',
        ], 201),
        '*/api/time-slots/1' => Http::response([
            'time_slot' => ['id' => 1, 'seat_capacity' => 50, 'seats_booked' => 0],
            'available_seats' => 50,
            'locks' => [['id' => 42, 'quantity' => 2, 'locked_until' => now()->addMinutes(5)->toIso8601String(), 'held_by_me' => true, 'held_by_other' => false]],
        ]),
    ]);

    Livewire::test(SeatMap::class, ['timeSlotId' => 1])
        ->call('confirmBooking');

    Http::assertSent(fn ($request) =>
        str_contains($request->url(), '/api/seat-locks/42/confirm')
        && $request->hasHeader('X-Idempotency-Key')
    );
});
