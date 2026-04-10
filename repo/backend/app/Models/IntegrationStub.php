<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationStub extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config' => 'array',
        ];
    }
}
