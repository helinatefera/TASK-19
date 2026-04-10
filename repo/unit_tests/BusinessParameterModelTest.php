<?php

use App\Models\BusinessParameter;

test('getTypedValue returns integer for type integer', function () {
    $param = BusinessParameter::create([
        'key' => 'test_int_param_' . uniqid(),
        'value' => '42',
        'type' => 'integer',
        'description' => 'Test integer parameter',
    ]);

    $typedValue = $param->getTypedValue();

    expect($typedValue)->toBe(42);
    expect($typedValue)->toBeInt();
});

test('getTypedValue returns boolean for type boolean', function () {
    $param = BusinessParameter::create([
        'key' => 'test_bool_param_' . uniqid(),
        'value' => 'true',
        'type' => 'boolean',
        'description' => 'Test boolean parameter',
    ]);

    $typedValue = $param->getTypedValue();

    expect($typedValue)->toBe(true);
    expect($typedValue)->toBeBool();
});

test('getTypedValue returns false for boolean false string', function () {
    $param = BusinessParameter::create([
        'key' => 'test_bool_false_param_' . uniqid(),
        'value' => 'false',
        'type' => 'boolean',
        'description' => 'Test boolean false parameter',
    ]);

    $typedValue = $param->getTypedValue();

    expect($typedValue)->toBe(false);
    expect($typedValue)->toBeBool();
});

test('getTypedValue returns string for type string', function () {
    $param = BusinessParameter::create([
        'key' => 'test_str_param_' . uniqid(),
        'value' => 'hello world',
        'type' => 'string',
        'description' => 'Test string parameter',
    ]);

    $typedValue = $param->getTypedValue();

    expect($typedValue)->toBe('hello world');
    expect($typedValue)->toBeString();
});

test('getTypedValue returns float for type float', function () {
    $param = BusinessParameter::create([
        'key' => 'test_float_param_' . uniqid(),
        'value' => '3.14',
        'type' => 'float',
        'description' => 'Test float parameter',
    ]);

    $typedValue = $param->getTypedValue();

    expect($typedValue)->toBe(3.14);
    expect($typedValue)->toBeFloat();
});

test('getTypedValue returns array for type json', function () {
    $param = BusinessParameter::create([
        'key' => 'test_json_param_' . uniqid(),
        'value' => '{"foo":"bar"}',
        'type' => 'json',
        'description' => 'Test JSON parameter',
    ]);

    $typedValue = $param->getTypedValue();

    expect($typedValue)->toBe(['foo' => 'bar']);
    expect($typedValue)->toBeArray();
});
