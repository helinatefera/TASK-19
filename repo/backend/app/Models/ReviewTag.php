<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewTag extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'review_id',
        'tag',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
