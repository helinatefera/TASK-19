<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(Tests\TestCase::class, RefreshDatabase::class)->in(
    __DIR__ . '/../unit_tests',
    __DIR__ . '/../API_tests',
);
