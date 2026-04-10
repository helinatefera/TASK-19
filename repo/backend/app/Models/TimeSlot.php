<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TimeSlot extends Model
{
    protected $fillable = [
        'programable_type',
        'programable_id',
        'starts_at',
        'ends_at',
        'seat_capacity',
        'seats_booked',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'seat_capacity' => 'integer',
            'seats_booked' => 'integer',
        ];
    }

    public function programable(): MorphTo
    {
        return $this->morphTo();
    }

    public function seatLocks(): HasMany
    {
        return $this->hasMany(SeatLock::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function availableSeats(): int
    {
        return $this->seat_capacity - $this->seats_booked;
    }
}
