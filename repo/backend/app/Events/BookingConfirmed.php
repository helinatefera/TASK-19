<?php

namespace App\Events;

use App\Models\Order;
use App\Models\SeatLock;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public SeatLock $seatLock,
    ) {}
}
