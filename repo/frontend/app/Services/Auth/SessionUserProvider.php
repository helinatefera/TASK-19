<?php

namespace App\Services\Auth;

use App\Services\ApiUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Session;

class SessionUserProvider implements UserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        $userData = Session::get('api_user');
        if ($userData && ($userData['id'] ?? null) == $identifier) {
            return new ApiUser($userData);
        }
        return null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void {}

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return false;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void {}
}
