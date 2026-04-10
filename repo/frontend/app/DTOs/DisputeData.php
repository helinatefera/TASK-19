<?php

namespace App\DTOs;

class DisputeData
{
    public function __construct(
        public readonly int $id,
        public readonly string $status,
        public readonly int $initiated_by,
        public readonly int $against_user_id,
        public readonly ?int $assigned_to,
        public readonly array $decisions,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            status: $data['status'],
            initiated_by: $data['initiated_by'],
            against_user_id: $data['against_user_id'],
            assigned_to: $data['assigned_to'] ?? null,
            decisions: $data['decisions'] ?? [],
        );
    }
}
