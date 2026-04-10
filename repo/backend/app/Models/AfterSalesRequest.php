<?php

namespace App\Models;

use App\Enums\AfterSalesStatus;
use App\Enums\AfterSalesType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfterSalesRequest extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'reason',
        'staff_notes',
        'status',
        'attachment_path',
        'attachment_mime',
        'attachment_checksum',
        'attachment_size',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => AfterSalesType::class,
            'status' => AfterSalesStatus::class,
            'attachment_size' => 'integer',
            'resolved_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
