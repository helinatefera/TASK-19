<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

class ApiUser implements Authenticatable
{
    public int $id;
    public string $username;
    public ?string $display_name;
    public ?string $email;
    public ?string $locale;
    public ?string $timezone;
    public array $roles;
    public array $permissions;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->display_name = $data['display_name'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->locale = $data['locale'] ?? 'en';
        $this->timezone = $data['timezone'] ?? 'UTC';
        $this->roles = array_column($data['roles'] ?? [], 'name');
        $this->permissions = $data['permissions'] ?? [];
    }

    public function hasRole(string ...$roles): bool
    {
        foreach ($roles as $role) {
            if (in_array($role, $this->roles, true)) {
                return true;
            }
        }
        return false;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function getAuthIdentifierName(): string { return 'id'; }
    public function getAuthIdentifier(): mixed { return $this->id; }
    public function getAuthPassword(): string { return ''; }
    public function getAuthPasswordName(): string { return 'password'; }
    public function getRememberToken(): ?string { return null; }
    public function setRememberToken($value): void {}
    public function getRememberTokenName(): string { return ''; }
}
