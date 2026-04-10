<?php

namespace App\DTOs;

class NotificationData
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $body,
        public readonly array $data,
        public readonly ?string $read_at,
        public readonly string $created_at,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            type: $data['type'],
            title: $data['title'],
            body: $data['body'] ?? null,
            data: $data['data'] ?? [],
            read_at: $data['read_at'] ?? null,
            created_at: $data['created_at'],
        );
    }
}
