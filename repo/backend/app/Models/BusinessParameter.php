<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessParameter extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            'int', 'integer' => (int) $this->value,
            'float', 'double' => (float) $this->value,
            'bool', 'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'array', 'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}
