<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        App::setLocale($locale);
        Carbon::setLocale($locale);

        $user = $request->user();
        $originalTimezone = date_default_timezone_get();

        if ($user && $user->timezone) {
            date_default_timezone_set($user->timezone);
        }

        $response = $next($request);

        // Restore process-wide timezone to prevent leaking into other requests
        date_default_timezone_set($originalTimezone);

        return $response;
    }

    private function resolveLocale(Request $request): string
    {
        $user = $request->user();

        if ($user && $user->locale) {
            return $user->locale;
        }

        $acceptLanguage = $request->header('Accept-Language');

        if ($acceptLanguage) {
            $locale = $this->parseAcceptLanguage($acceptLanguage);

            if ($locale) {
                return $locale;
            }
        }

        return config('app.locale', 'en');
    }

    private function parseAcceptLanguage(string $header): ?string
    {
        $parts = explode(',', $header);
        $firstPart = trim($parts[0]);

        $locale = explode(';', $firstPart)[0];
        $locale = str_replace('-', '_', trim($locale));

        if (! empty($locale)) {
            return $locale;
        }

        return null;
    }
}
