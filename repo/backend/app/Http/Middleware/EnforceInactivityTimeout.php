<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\BusinessParameter;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceInactivityTimeout
{
    private const DEFAULT_STAFF_TIMEOUT_MINUTES = 30;

    private const DEFAULT_USER_TIMEOUT_MINUTES = 120;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // For API token requests, enforce timeout via token's last_used_at
        if ($request->bearerToken() || ! $request->hasSession()) {
            $token = $user->currentAccessToken();
            if ($token && method_exists($token, 'getAttribute')) {
                $lastUsed = $token->getAttribute('last_used_at');
                if ($lastUsed) {
                    $timeoutMinutes = $this->getTimeoutForUser($user);
                    $elapsed = now()->diffInMinutes($lastUsed);

                    if ($elapsed >= $timeoutMinutes) {
                        $token->delete();

                        return new JsonResponse([
                            'code' => 401,
                            'msg' => 'Session expired due to inactivity.',
                        ], 401);
                    }
                }
            }

            return $next($request);
        }

        // For session-based requests
        $lastActivity = $request->session()->get('last_activity_at');

        if ($lastActivity) {
            $timeoutMinutes = $this->getTimeoutForUser($user);
            $elapsed = now()->diffInMinutes($lastActivity);

            if ($elapsed >= $timeoutMinutes) {
                $request->session()->invalidate();

                return new JsonResponse([
                    'code' => 401,
                    'msg' => 'Session expired due to inactivity.',
                ], 401);
            }
        }

        $request->session()->put('last_activity_at', now());

        return $next($request);
    }

    private function getTimeoutForUser($user): int
    {
        $isStaff = $user->hasRole(UserRole::Staff->value)
            || $user->hasRole(UserRole::Moderator->value)
            || $user->hasRole(UserRole::Admin->value);

        if ($isStaff) {
            return $this->getParameter(
                'session_timeout_staff_minutes',
                self::DEFAULT_STAFF_TIMEOUT_MINUTES,
            );
        }

        return $this->getParameter(
            'session_timeout_user_minutes',
            self::DEFAULT_USER_TIMEOUT_MINUTES,
        );
    }

    private function getParameter(string $key, int $default): int
    {
        $param = BusinessParameter::where('key', $key)->first();

        return $param ? (int) $param->getTypedValue() : $default;
    }
}
