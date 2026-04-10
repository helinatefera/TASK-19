<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticsMilestone extends Model
{
    protected $fillable = [
        'order_id',
        'title',
        'description',
        'status',
        'completed_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => MilestoneStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
