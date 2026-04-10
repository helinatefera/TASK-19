<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitLogin
{
    private const MAX_ATTEMPTS_PER_MINUTE = 5;

    private const MAX_ATTEMPTS_EXTENDED = 10;

    private const LOCKOUT_MINUTES = 30;

    public function __construct(
        private readonly RateLimiter $limiter,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->input('username', '');
        $ip = $request->ip() ?? '0.0.0.0';

        $perMinuteKey = 'login_per_min:' . $ip . '|' . $username;
        $extendedKey = 'login_extended:' . $ip . '|' . $username;

        // Check 30-minute lockout (10 attempts in 30 minutes)
        if ($this->limiter->tooManyAttempts($extendedKey, self::MAX_ATTEMPTS_EXTENDED)) {
            $retryAfter = $this->limiter->availableIn($extendedKey);

            return new JsonResponse([
                'code' => 429,
                'msg' => 'Too many login attempts. Please try again in ' . ceil($retryAfter / 60) . ' minute(s).',
                'retry_after_seconds' => $retryAfter,
            ], 429);
        }

        // Check per-minute rate limit (5 attempts per minute)
        if ($this->limiter->tooManyAttempts($perMinuteKey, self::MAX_ATTEMPTS_PER_MINUTE)) {
            $retryAfter = $this->limiter->availableIn($perMinuteKey);

            return new JsonResponse([
                'code' => 429,
                'msg' => 'Too many login attempts. Please try again in ' . $retryAfter . ' second(s).',
                'retry_after_seconds' => $retryAfter,
            ], 429);
        }

        $this->limiter->hit($perMinuteKey, 60);
        $this->limiter->hit($extendedKey, self::LOCKOUT_MINUTES * 60);

        $response = $next($request);

        // Clear rate limits on successful authentication
        if ($response->getStatusCode() === 200) {
            $this->limiter->clear($perMinuteKey);
            $this->limiter->clear($extendedKey);
        }

        return $response;
    }
}
