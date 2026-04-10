<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'payment_id',
        'rendered_locale',
        'rendered_timezone',
        'receipt_number',
        'content',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
