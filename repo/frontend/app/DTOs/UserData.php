<?php

namespace App\DTOs;

class UserData
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly ?string $display_name,
        public readonly ?string $email,
        public readonly array $roles,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            username: $data['username'],
            display_name: $data['display_name'] ?? null,
            email: $data['email'] ?? null,
            roles: $data['roles'] ?? [],
        );
    }
}
