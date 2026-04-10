<?php

namespace App\Providers;

use App\Services\Auth\SessionGuard;
use App\Services\Auth\SessionUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::provider('session-api', function ($app, array $config) {
            return new SessionUserProvider();
        });

        Auth::extend('session-api', function ($app, $name, array $config) {
            return new SessionGuard();
        });
    }
}
