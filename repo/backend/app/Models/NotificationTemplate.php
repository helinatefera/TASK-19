<?php

namespace App\Models;

use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'key',
        'locale',
        'title_template',
        'body_template',
        'type',
        'is_active',
        'requires_approval',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'is_active' => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }
}
