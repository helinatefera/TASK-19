<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'CivicCrowd API',
        'version' => '1.0.0',
        'status' => 'running',
    ]);
})->name('home');
