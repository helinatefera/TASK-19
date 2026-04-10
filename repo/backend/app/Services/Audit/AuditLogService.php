<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogService
{
    public function log(
        string $action,
        ?User $actor,
        string $auditableType,
        int $auditableId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
    ): AuditLog {
        return AuditLog::create([
            'action' => $action,
            'actor_id' => $actor?->id,
            'actor_ip' => request()->ip(),
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
        ]);
    }
}
