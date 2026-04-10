<?php

namespace App\Models;

use App\Enums\ReconciliationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReconciliationReport extends Model
{
    protected $fillable = [
        'generated_by',
        'period_start',
        'period_end',
        'total_cash',
        'total_card_on_file',
        'expected_total',
        'actual_total',
        'discrepancy',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'status' => ReconciliationStatus::class,
            'total_cash' => 'integer',
            'total_card_on_file' => 'integer',
            'expected_total' => 'integer',
            'actual_total' => 'integer',
            'discrepancy' => 'integer',
        ];
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(ReconciliationLineItem::class, 'report_id');
    }
}
