<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatLock extends Model
{
    public const LOCK_TTL_MINUTES = 5;

    public const UPDATED_AT = null;

    protected $fillable = [
        'time_slot_id',
        'user_id',
        'quantity',
        'locked_until',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'locked_until' => 'datetime',
            'released_at' => 'datetime',
            'quantity' => 'integer',
        ];
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('released_at')
            ->where('locked_until', '>', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNull('released_at')
            ->where('locked_until', '<=', now());
    }

    public static function getTtlMinutes(): int
    {
        $param = \App\Models\BusinessParameter::where('key', 'seat_lock_ttl_minutes')->first();
        return $param ? (int) $param->getTypedValue() : self::LOCK_TTL_MINUTES;
    }
}
