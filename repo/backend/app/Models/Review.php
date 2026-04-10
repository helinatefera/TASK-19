<?php

namespace App\Models;

use App\Enums\ReviewSide;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $fillable = [
        'order_id',
        'reviewer_id',
        'reviewee_id',
        'side',
        'overall_rating',
        'body',
        'public_alias',
        'visible_after',
        'is_visible',
    ];


    protected function casts(): array
    {
        return [
            'side' => ReviewSide::class,
            'overall_rating' => 'integer',
            'visible_after' => 'datetime',
            'is_visible' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function dimensions(): HasMany
    {
        return $this->hasMany(ReviewDimension::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ReviewTag::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)
            ->where(function (Builder $q) {
                $q->whereNull('visible_after')
                  ->orWhere('visible_after', '<=', now());
            });
    }
}
