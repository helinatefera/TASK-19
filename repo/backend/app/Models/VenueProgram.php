<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\CampaignVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class VenueProgram extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'visibility',
        'location',
        'created_by',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'visibility' => CampaignVisibility::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function timeSlots(): MorphMany
    {
        return $this->morphMany(TimeSlot::class, 'programable');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'venue_program_id');
    }
}
