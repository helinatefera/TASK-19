<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceFingerprint extends Model
{
    protected $fillable = [
        'user_id',
        'fingerprint_hash',
        'fingerprint_encrypted',
        'ip_address_encrypted',
        'user_agent',
        'last_seen_at',
    ];

    protected $hidden = [
        'fingerprint_encrypted',
        'ip_address_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'fingerprint_encrypted' => 'encrypted',
            'ip_address_encrypted' => 'encrypted',
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
