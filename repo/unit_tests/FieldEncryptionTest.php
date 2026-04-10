<?php

use App\Services\Encryption\FieldEncryptionService;

test('encrypt then decrypt returns original value', function () {
    $service = app(FieldEncryptionService::class);

    $original = 'sensitive-data-12345';
    $encrypted = $service->encrypt($original);
    $decrypted = $service->decrypt($encrypted);

    expect($decrypted)->toBe($original);
});

test('encrypted value differs from original', function () {
    $service = app(FieldEncryptionService::class);

    $original = 'sensitive-data-12345';
    $encrypted = $service->encrypt($original);

    expect($encrypted)->not->toBe($original);
});

test('hash returns consistent results for same input', function () {
    $service = app(FieldEncryptionService::class);

    $value = 'test-value-for-hashing';
    $hash1 = $service->hash($value);
    $hash2 = $service->hash($value);

    expect($hash1)->toBe($hash2);
});

test('hash returns different results for different inputs', function () {
    $service = app(FieldEncryptionService::class);

    $hash1 = $service->hash('value-one');
    $hash2 = $service->hash('value-two');

    expect($hash1)->not->toBe($hash2);
});

test('hash returns 64 character hex string', function () {
    $service = app(FieldEncryptionService::class);

    $hash = $service->hash('any-value');

    expect($hash)->toBeString();
    expect(strlen($hash))->toBe(64);
    expect($hash)->toMatch('/^[a-f0-9]{64}$/');
});
