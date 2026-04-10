<?php

namespace App\Livewire\Booking;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class SeatLockTimer extends Component
{
    public int $lockId;
    public string $expiresAt;

    public function mount(int $lockId, string $expiresAt): void
    {
        $this->lockId = $lockId;
        $this->expiresAt = $expiresAt;
    }

    public function render(): View
    {
        return view('livewire.booking.seat-lock-timer');
    }
}
