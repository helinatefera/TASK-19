<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RotateSession
{
    private const ROTATION_INTERVAL_MINUTES = 15;

    public function handle(Request $request, Closure $next): Response
    {
        $lastRotation = $request->session()->get('last_rotation');

        if (! $lastRotation || now()->diffInMinutes($lastRotation) >= self::ROTATION_INTERVAL_MINUTES) {
            $request->session()->regenerate();
            $request->session()->put('last_rotation', now());
        }

        return $next($request);
    }
}
