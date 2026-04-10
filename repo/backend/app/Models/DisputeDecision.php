<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class DisputeDecision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'dispute_id',
        'decided_by',
        'decision',
        'reasoning',
        'action_taken',
        'checksum',
    ];

    /**
     * Compute integrity checksum before first save.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->checksum = self::computeChecksum($model);
        });
    }

    public static function computeChecksum(self $model): string
    {
        return hash('sha256', implode('|', [
            $model->dispute_id,
            $model->decided_by,
            $model->decision,
            $model->reasoning,
            $model->action_taken ?? '',
        ]));
    }

    public function verifyIntegrity(): bool
    {
        return $this->checksum === self::computeChecksum($this);
    }

    /**
     * Arbitration decision logs are immutable. Updates are prohibited.
     */
    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new LogicException('Dispute decisions are immutable and cannot be updated.');
        }

        return parent::save($options);
    }

    /**
     * Arbitration decision logs are immutable. Deletes are prohibited.
     */
    public function delete(): ?bool
    {
        throw new LogicException('Dispute decisions are immutable and cannot be deleted.');
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
