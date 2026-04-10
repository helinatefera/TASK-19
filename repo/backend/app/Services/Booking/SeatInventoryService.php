<?php

namespace App\Services\Booking;

use App\Models\SeatLock;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\DB;

class SeatInventoryService
{
    public function getAvailableSeats(TimeSlot $timeSlot): int
    {
        return DB::transaction(function () use ($timeSlot) {
            /** @var TimeSlot $slot */
            $slot = TimeSlot::query()
                ->where('id', $timeSlot->id)
                ->lockForUpdate()
                ->first();

            $activeLockSum = SeatLock::query()
                ->where('time_slot_id', $slot->id)
                ->whereNull('released_at')
                ->where('locked_until', '>', now())
                ->sum('quantity');

            return max(0, $slot->seat_capacity - $slot->seats_booked - $activeLockSum);
        });
    }

    public function isAvailable(TimeSlot $timeSlot, int $quantity): bool
    {
        return $this->getAvailableSeats($timeSlot) >= $quantity;
    }
}
