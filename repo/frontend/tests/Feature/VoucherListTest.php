<?php

use App\Livewire\Voucher\VoucherList;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

beforeEach(function () {
    Session::put('api_token', 'test-token');
    Session::put('api_user', [
        'id' => 1,
        'username' => 'user1',
        'display_name' => 'Test User',
        'roles' => [['name' => 'user']],
    ]);
});

test('voucher list renders and calls backend API', function () {
    Http::fake([
        '*/api/vouchers*' => Http::response([
            'data' => [
                ['id' => 1, 'code' => 'ABCD1234EFGH', 'status' => 'active', 'order' => null, 'expires_at' => null],
            ],
            'meta' => ['last_page' => 1, 'current_page' => 1, 'total' => 1],
        ]),
    ]);

    Livewire::test(VoucherList::class)
        ->assertStatus(200)
        ->assertSee('ABCD1234EFGH');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/vouchers'));
});

test('voucher list handles pagination', function () {
    Http::fake([
        '*/api/vouchers*' => Http::response([
            'data' => [],
            'meta' => ['last_page' => 3, 'current_page' => 1, 'total' => 30],
        ]),
    ]);

    $component = Livewire::test(VoucherList::class);

    expect($component->get('page'))->toBe(1);

    $component->call('nextPage');
    expect($component->get('page'))->toBe(2);

    $component->call('previousPage');
    expect($component->get('page'))->toBe(1);

    // Cannot go below 1
    $component->call('previousPage');
    expect($component->get('page'))->toBe(1);
});
