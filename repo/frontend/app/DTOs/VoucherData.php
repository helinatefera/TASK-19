<?php

namespace App\DTOs;

class VoucherData
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $status,
        public readonly ?string $expires_at,
        public readonly ?string $redeemed_at,
        public readonly ?array $order,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            code: $data['code'],
            status: $data['status'],
            expires_at: $data['expires_at'] ?? null,
            redeemed_at: $data['redeemed_at'] ?? null,
            order: $data['order'] ?? null,
        );
    }
}
