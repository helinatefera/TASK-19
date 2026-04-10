<?php

namespace App\Models;

use App\Enums\AnomalyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnomalyFlag extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'description',
        'evidence',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => AnomalyType::class,
            'evidence' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }
}
