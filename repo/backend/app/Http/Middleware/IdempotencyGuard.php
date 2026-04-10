<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyGuard
{
    private const CACHE_PREFIX = 'idempotency:';

    private const CACHE_TTL_MINUTES = 10;

    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = $request->header('X-Idempotency-Key');

        if (! $idempotencyKey) {
            return $next($request);
        }

        $userId = $request->user()?->id ?? 'anonymous';
        $cacheKey = self::CACHE_PREFIX . $userId . ':' . $request->method() . ':' . $request->path() . ':' . $idempotencyKey;

        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return new JsonResponse(
                $cached['data'],
                $cached['status'],
                array_merge($cached['headers'] ?? [], ['X-Idempotent-Replayed' => 'true']),
            );
        }

        /** @var Response $response */
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            Cache::put($cacheKey, [
                'data' => $response->getData(true),
                'status' => $response->getStatusCode(),
                'headers' => [],
            ], now()->addMinutes(self::CACHE_TTL_MINUTES));
        }

        return $response;
    }
}
