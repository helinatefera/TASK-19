<?php

use App\Enums\AfterSalesStatus;
use App\Enums\CampaignStatus;
use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\RefundStatus;
use App\Enums\RestrictionLevel;
use App\Enums\UserRole;
use App\Enums\VoucherStatus;

test('UserRole enum has exactly 5 cases', function () {
    expect(UserRole::cases())->toHaveCount(5);
});

test('UserRole enum has expected values', function () {
    expect(UserRole::User->value)->toBe('user');
    expect(UserRole::Creator->value)->toBe('creator');
    expect(UserRole::Moderator->value)->toBe('moderator');
    expect(UserRole::Staff->value)->toBe('staff');
    expect(UserRole::Admin->value)->toBe('admin');
});

test('CampaignStatus enum has exactly 7 cases', function () {
    expect(CampaignStatus::cases())->toHaveCount(7);
});

test('CampaignStatus enum has expected values', function () {
    expect(CampaignStatus::Draft->value)->toBe('draft');
    expect(CampaignStatus::PendingReview->value)->toBe('pending_review');
    expect(CampaignStatus::Published->value)->toBe('published');
    expect(CampaignStatus::Fundraising->value)->toBe('fundraising');
    expect(CampaignStatus::Success->value)->toBe('success');
    expect(CampaignStatus::Failure->value)->toBe('failure');
    expect(CampaignStatus::Closed->value)->toBe('closed');
});

test('OrderStatus enum has exactly 6 cases', function () {
    expect(OrderStatus::cases())->toHaveCount(6);
});

test('OrderStatus enum has expected values', function () {
    expect(OrderStatus::Pending->value)->toBe('pending');
    expect(OrderStatus::Confirmed->value)->toBe('confirmed');
    expect(OrderStatus::Fulfilled->value)->toBe('fulfilled');
    expect(OrderStatus::Cancelled->value)->toBe('cancelled');
    expect(OrderStatus::Refunded->value)->toBe('refunded');
    expect(OrderStatus::AfterSales->value)->toBe('after_sales');
});

test('OrderType enum has exactly 2 cases', function () {
    expect(OrderType::cases())->toHaveCount(2);
});

test('OrderType enum has expected values', function () {
    expect(OrderType::Contribution->value)->toBe('contribution');
    expect(OrderType::Reservation->value)->toBe('reservation');
});

test('PaymentMethod enum has exactly 2 cases', function () {
    expect(PaymentMethod::cases())->toHaveCount(2);
});

test('PaymentMethod enum has expected values', function () {
    expect(PaymentMethod::Cash->value)->toBe('cash');
    expect(PaymentMethod::CardOnFile->value)->toBe('card_on_file');
});

test('VoucherStatus enum has exactly 4 cases', function () {
    expect(VoucherStatus::cases())->toHaveCount(4);
});

test('VoucherStatus enum has expected values', function () {
    expect(VoucherStatus::Active->value)->toBe('active');
    expect(VoucherStatus::Redeemed->value)->toBe('redeemed');
    expect(VoucherStatus::Expired->value)->toBe('expired');
    expect(VoucherStatus::Revoked->value)->toBe('revoked');
});

test('RestrictionLevel enum has exactly 3 cases', function () {
    expect(RestrictionLevel::cases())->toHaveCount(3);
});

test('RestrictionLevel enum has expected values', function () {
    expect(RestrictionLevel::None->value)->toBe('none');
    expect(RestrictionLevel::Gray->value)->toBe('gray');
    expect(RestrictionLevel::Black->value)->toBe('black');
});

test('RefundStatus enum has exactly 3 cases', function () {
    expect(RefundStatus::cases())->toHaveCount(3);
});

test('RefundStatus enum has expected values', function () {
    expect(RefundStatus::Pending->value)->toBe('pending');
    expect(RefundStatus::Approved->value)->toBe('approved');
    expect(RefundStatus::Rejected->value)->toBe('rejected');
});

test('AfterSalesStatus enum has exactly 4 cases', function () {
    expect(AfterSalesStatus::cases())->toHaveCount(4);
});

test('AfterSalesStatus enum has expected values', function () {
    expect(AfterSalesStatus::Submitted->value)->toBe('submitted');
    expect(AfterSalesStatus::UnderReview->value)->toBe('under_review');
    expect(AfterSalesStatus::Approved->value)->toBe('approved');
    expect(AfterSalesStatus::Rejected->value)->toBe('rejected');
});

test('FulfillmentType enum has exactly 3 cases', function () {
    expect(FulfillmentType::cases())->toHaveCount(3);
});

test('FulfillmentType enum has expected values', function () {
    expect(FulfillmentType::Digital->value)->toBe('digital');
    expect(FulfillmentType::Physical->value)->toBe('physical');
    expect(FulfillmentType::Event->value)->toBe('event');
});
