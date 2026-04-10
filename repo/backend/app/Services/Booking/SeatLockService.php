<?php

namespace App\Services\Booking;

use App\Models\SeatLock;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SeatLockService
{
    public function __construct(
        private readonly SeatInventoryService $inventoryService,
    ) {}

    public function lock(TimeSlot $timeSlot, User $user, int $quantity = 1): SeatLock
    {
        return DB::transaction(function () use ($timeSlot, $user, $quantity) {
            if (! $this->inventoryService->isAvailable($timeSlot, $quantity)) {
                throw new RuntimeException('Not enough seats available for this time slot.');
            }

            return SeatLock::create([
                'time_slot_id' => $timeSlot->id,
                'user_id' => $user->id,
                'quantity' => $quantity,
                'locked_until' => now()->addMinutes(SeatLock::getTtlMinutes()),
            ]);
        });
    }

    public function release(SeatLock $lock): void
    {
        $lock->update([
            'released_at' => now(),
        ]);
    }

    public function releaseExpired(): int
    {
        return SeatLock::expired()->update([
            'released_at' => now(),
        ]);
    }

    public function isValid(SeatLock $lock): bool
    {
        return $lock->released_at === null
            && $lock->locked_until->isFuture();
    }
}
