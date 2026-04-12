<?php

namespace App\Livewire\Booking;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class SeatMap extends Component
{
    protected ApiClient $apiClient;

    public int $timeSlotId;
    public array $timeSlot = [];
    public int $availableSeats = 0;
    public int $totalCapacity = 0;
    public int $seatsBooked = 0;
    public int $lockedByOthers = 0;
    public ?array $myLock = null;
    public int $quantity = 1;
    public string $confirmationNumber = '';
    public string $errorMessage = '';

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function mount(int $timeSlotId): void
    {
        $this->timeSlotId = $timeSlotId;
        $this->refreshAvailability();
    }

    public function refreshAvailability(): void
    {
        try {
            $response = $this->apiClient->get("/api/time-slots/{$this->timeSlotId}");

            $this->timeSlot = $response['time_slot'] ?? [];
            $this->availableSeats = $response['available_seats'] ?? 0;
            $this->totalCapacity = $this->timeSlot['seat_capacity'] ?? 0;
            $this->seatsBooked = $this->timeSlot['seats_booked'] ?? 0;

            $locks = $response['locks'] ?? [];

            // Calculate locks by others and check for my lock
            $this->lockedByOthers = 0;
            $foundMyLock = false;

            foreach ($locks as $lock) {
                if (!empty($lock['held_by_me'])) {
                    $this->myLock = [
                        'id' => $lock['id'],
                        'quantity' => $lock['quantity'],
                        'locked_until' => $lock['locked_until'],
                    ];
                    $foundMyLock = true;
                } elseif (!empty($lock['held_by_other'])) {
                    $this->lockedByOthers += $lock['quantity'];
                }
            }

            // If we had a lock but it's no longer in the active list, clear it
            if ($this->myLock && !$foundMyLock) {
                $this->myLock = null;
            }
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function lockSeats(): void
    {
        $this->errorMessage = '';

        if ($this->quantity < 1) {
            $this->errorMessage = 'Quantity must be at least 1.';
            return;
        }

        $scope = "idempotency:lock:{$this->timeSlotId}";
        $key = session($scope);
        if (! $key) {
            $key = 'lock-' . $this->timeSlotId . '-' . uniqid();
            session()->put($scope, $key);
        }

        try {
            $response = $this->apiClient->post("/api/time-slots/{$this->timeSlotId}/lock", [
                'quantity' => $this->quantity,
            ], ['X-Idempotency-Key' => $key]);

            session()->forget($scope);

            $lock = $response['lock'] ?? [];
            $this->myLock = [
                'id' => $lock['id'],
                'quantity' => $lock['quantity'],
                'locked_until' => $lock['locked_until'],
            ];

            $this->refreshAvailability();
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function releaseLock(): void
    {
        $this->errorMessage = '';

        if (!$this->myLock) {
            return;
        }

        try {
            $lockId = $this->myLock['id'];
            $this->apiClient->delete("/api/seat-locks/{$lockId}");
        } catch (RuntimeException $e) {
            // Lock may have already expired
        }

        $this->myLock = null;
        $this->refreshAvailability();
    }

    public function confirmBooking(): mixed
    {
        $this->errorMessage = '';

        if (!$this->myLock) {
            $this->errorMessage = 'You must lock seats before confirming.';
            return null;
        }

        try {
            $lockId = $this->myLock['id'];
            $idempotencyKey = 'confirm-' . $lockId . '-' . session()->getId();
            $response = $this->apiClient->post("/api/seat-locks/{$lockId}/confirm", [], [
                'X-Idempotency-Key' => $idempotencyKey,
            ]);

            $this->confirmationNumber = $response['confirmation_number'] ?? '';
            $orderId = $response['order']['id'] ?? null;
            $this->myLock = null;

            if ($orderId) {
                return $this->redirect(
                    route('orders.detail', ['orderId' => $orderId]),
                    navigate: true,
                );
            }

            return null;
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
            $this->myLock = null;
            $this->refreshAvailability();
            return null;
        }
    }

    public function getAvailabilityPercentProperty(): float
    {
        if ($this->totalCapacity === 0) {
            return 0;
        }

        return ($this->availableSeats / $this->totalCapacity) * 100;
    }

    public function render(): View
    {
        return view('livewire.booking.seat-map');
    }
}
