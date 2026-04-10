<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationLineItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'payment_id',
        'order_id',
        'expected_amount',
        'actual_amount',
        'is_matched',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_matched' => 'boolean',
            'expected_amount' => 'integer',
            'actual_amount' => 'integer',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(ReconciliationReport::class, 'report_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
