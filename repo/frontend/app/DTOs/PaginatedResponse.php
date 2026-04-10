<?php

namespace App\DTOs;

class PaginatedResponse
{
    public function __construct(
        public readonly array $data,
        public readonly int $current_page,
        public readonly int $last_page,
        public readonly int $per_page,
        public readonly int $total,
        public readonly array $links,
    ) {}

    public static function fromArray(array $data): static
    {
        $meta = $data['meta'] ?? $data;

        return new static(
            data: $data['data'] ?? [],
            current_page: $meta['current_page'] ?? 1,
            last_page: $meta['last_page'] ?? 1,
            per_page: $meta['per_page'] ?? 15,
            total: $meta['total'] ?? 0,
            links: $data['links'] ?? [],
        );
    }

    public function hasMorePages(): bool
    {
        return $this->current_page < $this->last_page;
    }
}
