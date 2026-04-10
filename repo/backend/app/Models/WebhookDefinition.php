<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookDefinition extends Model
{
    protected $fillable = [
        'name',
        'url',
        'events',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
