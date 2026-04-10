<?php

namespace App\DTOs;

class SeatLockData
{
    public function __construct(
        public readonly int $id,
        public readonly int $time_slot_id,
        public readonly int $quantity,
        public readonly string $locked_until,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            time_slot_id: $data['time_slot_id'],
            quantity: $data['quantity'],
            locked_until: $data['locked_until'],
        );
    }
}
