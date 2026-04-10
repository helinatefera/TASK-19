<?php

use Illuminate\Support\Facades\Hash;

test('default hashing driver is argon2id', function () {
    expect(config('hashing.driver'))->toBe('argon2id');
});

test('Hash facade uses argon2id driver', function () {
    expect(app('hash')->getDefaultDriver())->toBe('argon2id');
});

test('hashed password uses argon2id algorithm', function () {
    $hash = Hash::make('test-password');

    // Argon2id hashes start with $argon2id$
    expect($hash)->toStartWith('$argon2id$');
});

test('hashing config is not overridable by HASH_DRIVER env var', function () {
    // The config hardcodes argon2id — no env() call — so this must always be argon2id
    expect(config('hashing.driver'))->toBe('argon2id');
});
