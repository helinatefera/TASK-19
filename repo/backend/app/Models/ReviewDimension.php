<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewDimension extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'review_id',
        'dimension',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
