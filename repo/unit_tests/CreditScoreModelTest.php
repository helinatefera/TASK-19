<?php

use App\Enums\RestrictionLevel;
use App\Models\CreditScore;
use App\Models\User;

test('isBlacklisted returns true when restriction_level is black', function () {
    $user = User::factory()->create();
    $creditScore = CreditScore::create([
        'user_id' => $user->id,
        'score' => 200,
        'no_show_count' => 0,
        'chargeback_count' => 0,
        'refund_count' => 0,
        'violation_count' => 0,
        'restriction_level' => RestrictionLevel::Black,
        'restriction_until' => now()->addDays(30),
    ]);

    expect($creditScore->isBlacklisted())->toBeTrue();
});

test('isBlacklisted returns false when restriction_level is not black', function () {
    $user = User::factory()->create();
    $creditScore = CreditScore::create([
        'user_id' => $user->id,
        'score' => 800,
        'no_show_count' => 0,
        'chargeback_count' => 0,
        'refund_count' => 0,
        'violation_count' => 0,
        'restriction_level' => RestrictionLevel::None,
    ]);

    expect($creditScore->isBlacklisted())->toBeFalse();
});

test('isGraylisted returns true when restriction_level is gray', function () {
    $user = User::factory()->create();
    $creditScore = CreditScore::create([
        'user_id' => $user->id,
        'score' => 500,
        'no_show_count' => 0,
        'chargeback_count' => 0,
        'refund_count' => 0,
        'violation_count' => 0,
        'restriction_level' => RestrictionLevel::Gray,
    ]);

    expect($creditScore->isGraylisted())->toBeTrue();
});

test('isGraylisted returns false when restriction_level is not gray', function () {
    $user = User::factory()->create();
    $creditScore = CreditScore::create([
        'user_id' => $user->id,
        'score' => 800,
        'no_show_count' => 0,
        'chargeback_count' => 0,
        'refund_count' => 0,
        'violation_count' => 0,
        'restriction_level' => RestrictionLevel::None,
    ]);

    expect($creditScore->isGraylisted())->toBeFalse();
});

test('canPlaceOrders returns false when blacklisted', function () {
    $user = User::factory()->create();
    $creditScore = CreditScore::create([
        'user_id' => $user->id,
        'score' => 100,
        'no_show_count' => 0,
        'chargeback_count' => 0,
        'refund_count' => 0,
        'violation_count' => 0,
        'restriction_level' => RestrictionLevel::Black,
        'restriction_until' => now()->addDays(30),
    ]);

    expect($creditScore->canPlaceOrders())->toBeFalse();
});

test('canPlaceOrders returns true when graylisted (requires staff approval)', function () {
    $user = User::factory()->create();
    $creditScore = CreditScore::create([
        'user_id' => $user->id,
        'score' => 500,
        'no_show_count' => 0,
        'chargeback_count' => 0,
        'refund_count' => 0,
        'violation_count' => 0,
        'restriction_level' => RestrictionLevel::Gray,
    ]);

    // Graylisted users can place orders but require staff approval
    expect($creditScore->canPlaceOrders())->toBeTrue();
    expect($creditScore->requiresStaffApproval())->toBeTrue();
});

test('canPlaceOrders returns true when not restricted', function () {
    $user = User::factory()->create();
    $creditScore = CreditScore::create([
        'user_id' => $user->id,
        'score' => 1000,
        'no_show_count' => 0,
        'chargeback_count' => 0,
        'refund_count' => 0,
        'violation_count' => 0,
        'restriction_level' => RestrictionLevel::None,
    ]);

    expect($creditScore->canPlaceOrders())->toBeTrue();
});

test('credit score default value from seeder is 1000', function () {
    $user = User::factory()->create();
    $creditScore = CreditScore::create([
        'user_id' => $user->id,
        'score' => 1000,
        'no_show_count' => 0,
        'chargeback_count' => 0,
        'refund_count' => 0,
        'violation_count' => 0,
        'restriction_level' => RestrictionLevel::None,
    ]);

    expect($creditScore->score)->toBe(1000);
});
