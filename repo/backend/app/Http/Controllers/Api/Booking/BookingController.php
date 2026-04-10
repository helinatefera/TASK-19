<?php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Api\BaseController;
use App\Models\SeatLock;
use App\Models\TimeSlot;
use App\Services\Booking\BookingService;
use App\Services\Booking\SeatInventoryService;
use App\Services\Booking\SeatLockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class BookingController extends BaseController
{
    public function __construct(
        private readonly SeatInventoryService $inventoryService,
        private readonly SeatLockService $seatLockService,
        private readonly BookingService $bookingService,
    ) {}

    /**
     * GET /api/time-slots/{timeSlot}
     */
    public function showTimeSlot(Request $request, TimeSlot $timeSlot): JsonResponse
    {
        $availableSeats = $this->inventoryService->getAvailableSeats($timeSlot);

        $activeLocks = $timeSlot->seatLocks()
            ->whereNull('released_at')
            ->where('locked_until', '>', now())
            ->get();

        $userId = $request->user()?->id;

        $locks = $activeLocks->map(function (SeatLock $lock) use ($userId) {
            return [
                'id' => $lock->id,
                'quantity' => $lock->quantity,
                'locked_until' => $lock->locked_until,
                'held_by_me' => $userId !== null && $lock->user_id === $userId,
                'held_by_other' => $userId === null || $lock->user_id !== $userId,
            ];
        });

        return $this->success([
            'time_slot' => $timeSlot,
            'available_seats' => $availableSeats,
            'locks' => $locks,
        ]);
    }

    /**
     * POST /api/time-slots/{timeSlot}/lock
     */
    public function lock(Request $request, TimeSlot $timeSlot): JsonResponse
    {
        // Enforce blacklist restriction
        $creditScore = $request->user()->creditScore;
        if ($creditScore && ! $creditScore->canPlaceOrders()) {
            return $this->error('Your account is currently restricted from making bookings.', 403);
        }

        $request->validate([
            'quantity' => 'sometimes|integer|min:1',
        ]);

        $quantity = $request->input('quantity', 1);

        try {
            $lock = $this->seatLockService->lock($timeSlot, $request->user(), $quantity);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 409);
        }

        return $this->success([
            'lock' => $lock,
            'ttl_seconds' => SeatLock::getTtlMinutes() * 60,
        ], 201);
    }

    /**
     * DELETE /api/seat-locks/{seatLock}
     */
    public function releaseLock(Request $request, SeatLock $seatLock): JsonResponse
    {
        if ($seatLock->user_id !== $request->user()->id) {
            return $this->error('You can only release your own locks.', 403);
        }

        $this->seatLockService->release($seatLock);

        return $this->success(['message' => 'Lock released.']);
    }

    /**
     * POST /api/seat-locks/{seatLock}/confirm
     */
    public function confirm(Request $request, SeatLock $seatLock): JsonResponse
    {
        if ($seatLock->user_id !== $request->user()->id) {
            return $this->error('You can only confirm your own locks.', 403);
        }

        $idempotencyKey = $request->header('X-Idempotency-Key');
        if (! $idempotencyKey) {
            return $this->error('X-Idempotency-Key header is required.', 422);
        }

        $request->validate([
            'amount' => 'sometimes|integer|min:0',
        ]);

        try {
            $order = $this->bookingService->confirm($seatLock, [
                'request_key' => $idempotencyKey,
                'amount' => (int) $request->input('amount', 0),
            ]);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success([
            'order' => $order,
            'confirmation_number' => $order->confirmation_number,
        ], 201);
    }
}
