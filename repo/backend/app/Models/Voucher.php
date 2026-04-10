<?php

namespace App\Models;

use App\Enums\VoucherStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $fillable = [
        'order_id',
        'code',
        'status',
        'redeemed_at',
        'redeemed_by',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => VoucherStatus::class,
            'redeemed_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function redeemedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }
}
