<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'type',
        'file_path',
        'file_size_bytes',
        'checksum',
        'is_encrypted',
        'started_at',
        'completed_at',
        'is_successful',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
            'is_successful' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
