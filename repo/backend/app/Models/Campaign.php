<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\CampaignVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Campaign extends Model
{
    protected $fillable = [
        'creator_id',
        'title',
        'slug',
        'description',
        'risk_disclosure',
        'target_amount',
        'pledged_amount',
        'currency',
        'status',
        'visibility',
        'duration_days',
        'starts_at',
        'ends_at',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'visibility' => CampaignVisibility::class,
            'target_amount' => 'integer',
            'pledged_amount' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function rewardTiers(): HasMany
    {
        return $this->hasMany(RewardTier::class);
    }

    public function timeSlots(): MorphMany
    {
        return $this->morphMany(TimeSlot::class, 'programable');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
