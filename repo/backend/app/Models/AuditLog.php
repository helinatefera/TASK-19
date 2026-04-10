<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'actor_ip',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new LogicException('Audit logs are immutable and cannot be updated.');
        }

        return parent::save($options);
    }

    public function delete(): ?bool
    {
        throw new LogicException('Audit logs are immutable and cannot be deleted.');
    }
}
