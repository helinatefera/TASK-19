<?php

namespace App\DTOs;

class ReviewData
{
    public function __construct(
        public readonly int $id,
        public readonly string $side,
        public readonly int $overall_rating,
        public readonly ?string $body,
        public readonly ?string $public_alias,
        public readonly bool $is_visible,
        public readonly ?string $visible_after,
        public readonly array $dimensions,
        public readonly array $tags,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            side: $data['side'],
            overall_rating: $data['overall_rating'],
            body: $data['body'] ?? null,
            public_alias: $data['public_alias'] ?? null,
            is_visible: $data['is_visible'] ?? true,
            visible_after: $data['visible_after'] ?? null,
            dimensions: $data['dimensions'] ?? [],
            tags: $data['tags'] ?? [],
        );
    }
}
