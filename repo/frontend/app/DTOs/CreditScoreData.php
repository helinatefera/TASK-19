<?php

namespace App\DTOs;

class CreditScoreData
{
    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public readonly int $score,
        public readonly string $restriction_level,
        public readonly ?string $restriction_until,
        public readonly ?array $user,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            user_id: $data['user_id'],
            score: $data['score'],
            restriction_level: $data['restriction_level'],
            restriction_until: $data['restriction_until'] ?? null,
            user: $data['user'] ?? null,
        );
    }
}
