<?php

namespace App\Models;

use App\Enums\DisputeStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispute extends Model
{
    protected $fillable = [
        'order_id',
        'initiated_by',
        'against_user_id',
        'status',
        'reason',
        'assigned_to',
        'assigned_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DisputeStatus::class,
            'assigned_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'against_user_id');
    }

    public function arbitrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(DisputeDecision::class);
    }

    public function scopeInQueue(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }
}
