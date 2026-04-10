<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

Schedule::command('seats:clean-expired-locks')->everyMinute();
Schedule::command('campaigns:transition-expired')->everyFiveMinutes();
Schedule::command('reviews:publish-pending')->everyFifteenMinutes();
Schedule::command('bookings:mark-no-shows')->everyFifteenMinutes();
Schedule::command('bookings:send-reminders')->everyFifteenMinutes();
Schedule::command('notifications:prune')->dailyAt('03:00');
Schedule::command('vouchers:expire')->dailyAt('01:00');
Schedule::command('risk:detect-duplicate-devices')->hourly();
Schedule::command('db:backup')->dailyAt('02:00');
Schedule::command('risk:recalculate-credit-scores')->dailyAt('04:00');
