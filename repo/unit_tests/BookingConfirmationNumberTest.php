<?php

use App\Services\Booking\BookingService;

test('generateConfirmationNumber returns string starting with CC-', function () {
    $service = app(BookingService::class);

    $number = $service->generateConfirmationNumber();

    expect($number)->toStartWith('CC-');
});

test('generateConfirmationNumber has correct format CC-YYMMDD-XXXXXXXX', function () {
    $service = app(BookingService::class);

    $number = $service->generateConfirmationNumber();

    // Format: CC-YYMMDD-XXXXXXXX (CC- + 6 digit date + - + 8 alphanumeric chars)
    expect($number)->toMatch('/^CC-\d{6}-[A-Z0-9]{8}$/');
});

test('generateConfirmationNumber includes current date', function () {
    $service = app(BookingService::class);

    $number = $service->generateConfirmationNumber();

    $expectedDatePart = now()->format('ymd');

    expect($number)->toContain($expectedDatePart);
});

test('two calls to generateConfirmationNumber return different numbers', function () {
    $service = app(BookingService::class);

    $number1 = $service->generateConfirmationNumber();
    $number2 = $service->generateConfirmationNumber();

    expect($number1)->not->toBe($number2);
});
