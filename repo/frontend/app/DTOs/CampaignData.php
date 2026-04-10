<?php

namespace App\DTOs;

class CampaignData
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $slug,
        public readonly ?string $description,
        public readonly string $status,
        public readonly string $visibility,
        public readonly ?string $target_amount,
        public readonly ?string $pledged_amount,
        public readonly ?int $duration_days,
        public readonly ?string $starts_at,
        public readonly ?string $ends_at,
        public readonly ?array $creator,
        public readonly array $reward_tiers,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            title: $data['title'],
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
            status: $data['status'],
            visibility: $data['visibility'],
            target_amount: $data['target_amount'] ?? null,
            pledged_amount: $data['pledged_amount'] ?? null,
            duration_days: $data['duration_days'] ?? null,
            starts_at: $data['starts_at'] ?? null,
            ends_at: $data['ends_at'] ?? null,
            creator: $data['creator'] ?? null,
            reward_tiers: $data['reward_tiers'] ?? [],
        );
    }
}
