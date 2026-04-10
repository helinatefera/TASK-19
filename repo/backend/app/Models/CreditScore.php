<?php

namespace App\Models;

use App\Enums\RestrictionLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditScore extends Model
{
    protected $fillable = [
        'user_id',
        'score',
        'no_show_count',
        'chargeback_count',
        'refund_count',
        'violation_count',
        'restriction_level',
        'restriction_until',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'no_show_count' => 'integer',
            'chargeback_count' => 'integer',
            'refund_count' => 'integer',
            'violation_count' => 'integer',
            'restriction_level' => RestrictionLevel::class,
            'restriction_until' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isBlacklisted(): bool
    {
        if ($this->restriction_level !== RestrictionLevel::Black) {
            return false;
        }

        // Blacklist has a time limit — if restriction_until has passed, the restriction has expired
        if ($this->restriction_until && $this->restriction_until->isPast()) {
            return false;
        }

        return true;
    }

    public function isGraylisted(): bool
    {
        return $this->restriction_level === RestrictionLevel::Gray;
    }

    public function isRestricted(): bool
    {
        return $this->isBlacklisted() || $this->isGraylisted();
    }

    public function canPlaceOrders(): bool
    {
        // Blacklisted users cannot place orders at all
        if ($this->isBlacklisted()) {
            return false;
        }

        // Graylisted users can place orders but need staff approval (handled at booking level)
        // Only fully blocked (blacklisted) users are prevented here
        return true;
    }

    public function requiresStaffApproval(): bool
    {
        return $this->isGraylisted();
    }
}
