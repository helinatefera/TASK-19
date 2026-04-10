<?php

namespace App\Services\Auth;

use App\Models\BusinessParameter;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthenticationService
{
    private const CACHE_PREFIX = 'login_attempts:';

    private const LOCKOUT_PREFIX = 'account_locked:';

    private const DEFAULT_MAX_ATTEMPTS = 5;

    private const LOCKOUT_DURATION_MINUTES = 30;

    private const ATTEMPT_DECAY_MINUTES = 15;

    public function attempt(string $username, string $password, ?string $ip = null): array
    {
        if ($this->isLockedOut($username)) {
            return [
                'success' => false,
                'user' => null,
                'message' => 'Account is temporarily locked due to too many failed attempts.',
            ];
        }

        if (Auth::attempt(['username' => $username, 'password' => $password])) {
            /** @var User $user */
            $user = Auth::user();

            Cache::forget(self::CACHE_PREFIX . $username);

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Authentication successful.',
            ];
        }

        $this->recordFailedAttempt($username, $ip ?? '0.0.0.0');

        return [
            'success' => false,
            'user' => null,
            'message' => 'Invalid credentials.',
        ];
    }

    public function recordFailedAttempt(string $username, string $ip): void
    {
        $cacheKey = self::CACHE_PREFIX . $username;

        $attempts = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $attempts, now()->addMinutes(self::ATTEMPT_DECAY_MINUTES));

        $maxAttempts = $this->getMaxAttempts();

        if ($attempts >= $maxAttempts) {
            $this->lockAccount($username);
        }
    }

    public function isLockedOut(string $username): bool
    {
        return Cache::has(self::LOCKOUT_PREFIX . $username);
    }

    public function lockAccount(string $username): void
    {
        $lockoutMinutes = $this->getLockoutDurationMinutes();

        Cache::put(
            self::LOCKOUT_PREFIX . $username,
            true,
            now()->addMinutes($lockoutMinutes)
        );
    }

    private function getLockoutDurationMinutes(): int
    {
        $param = BusinessParameter::where('key', 'login_lockout_minutes')->first();

        return $param ? (int) $param->getTypedValue() : self::LOCKOUT_DURATION_MINUTES;
    }

    private function getMaxAttempts(): int
    {
        $param = BusinessParameter::where('key', 'login_max_attempts')->first();

        return $param ? (int) $param->getTypedValue() : self::DEFAULT_MAX_ATTEMPTS;
    }
}
