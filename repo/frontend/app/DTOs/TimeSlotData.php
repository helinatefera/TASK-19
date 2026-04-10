<?php

namespace App\DTOs;

class TimeSlotData
{
    public function __construct(
        public readonly int $id,
        public readonly string $starts_at,
        public readonly string $ends_at,
        public readonly int $seat_capacity,
        public readonly int $seats_booked,
        public readonly int $available_seats,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            starts_at: $data['starts_at'],
            ends_at: $data['ends_at'],
            seat_capacity: $data['seat_capacity'],
            seats_booked: $data['seats_booked'] ?? 0,
            available_seats: $data['available_seats'] ?? ($data['seat_capacity'] - ($data['seats_booked'] ?? 0)),
        );
    }
}
