<?php

use App\Services\Voucher\VoucherService;

test('generateCode returns a 12 character string', function () {
    $service = app(VoucherService::class);

    $code = $service->generateCode();

    expect($code)->toBeString();
    expect(strlen($code))->toBe(12);
});

test('generateCode returns alphanumeric characters only', function () {
    $service = app(VoucherService::class);

    $code = $service->generateCode();

    expect($code)->toMatch('/^[A-Z0-9]+$/');
});

test('two calls to generateCode return different codes', function () {
    $service = app(VoucherService::class);

    $code1 = $service->generateCode();
    $code2 = $service->generateCode();

    expect($code1)->not->toBe($code2);
});
