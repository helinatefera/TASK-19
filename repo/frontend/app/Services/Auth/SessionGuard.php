<?php

namespace App\Services\Auth;

use App\Services\ApiUser;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Session;

class SessionGuard implements Guard
{
    protected ?ApiUser $user = null;

    public function check(): bool
    {
        return Session::has('api_user') && Session::has('api_token');
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): ?Authenticatable
    {
        if ($this->user) {
            return $this->user;
        }

        $userData = Session::get('api_user');
        if ($userData) {
            $this->user = new ApiUser($userData);
        }

        return $this->user;
    }

    public function id(): int|string|null
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        return false; // Validation happens via backend API
    }

    public function hasUser(): bool
    {
        return $this->user !== null || Session::has('api_user');
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function login(array $userData, string $token): void
    {
        Session::put('api_user', $userData);
        Session::put('api_token', $token);
        $this->user = new ApiUser($userData);
    }

    public function logout(): void
    {
        Session::forget(['api_user', 'api_token']);
        $this->user = null;
        Session::invalidate();
        Session::regenerateToken();
    }
}
