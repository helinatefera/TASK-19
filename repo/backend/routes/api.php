<?php

use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Booking\BookingController;
use App\Http\Controllers\Api\Campaign\CampaignController;
use App\Http\Controllers\Api\Campaign\VenueProgramController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\Order\AfterSalesController;
use App\Http\Controllers\Api\Order\OrderController;
use App\Http\Controllers\Api\Order\PaymentController;
use App\Http\Controllers\Api\Order\LogisticsMilestoneController;
use App\Http\Controllers\Api\Order\RefundController;
use App\Http\Controllers\Api\Review\ReviewController;
use App\Http\Controllers\Api\RiskControl\DisputeController;
use App\Http\Controllers\Api\RiskControl\RiskControlController;
use App\Http\Controllers\Api\Admin\IntegrationStubController;
use App\Http\Controllers\Api\Admin\WebhookDefinitionController;
use App\Http\Controllers\Api\Voucher\VoucherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('rate.login')
    ->name('auth.login');

// Public campaign browsing.
Route::get('/campaigns', [CampaignController::class, 'index'])
    ->name('campaigns.index');
Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])
    ->name('campaigns.show');
Route::get('/campaigns/{campaign}/reviews', [ReviewController::class, 'index'])
    ->name('campaigns.reviews.index');

// Public venue program browsing.
Route::get('/programs', [VenueProgramController::class, 'index'])
    ->name('programs.index');
Route::get('/programs/{program}', [VenueProgramController::class, 'show'])
    ->name('programs.show');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // ── Auth ─────────────────────────────────────────────────────────
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');
    Route::get('/auth/me', [AuthController::class, 'me'])
        ->name('auth.me');

    // ── Campaigns (creator+) ─────────────────────────────────────────
    Route::middleware(['enforce.role:creator,moderator,admin'])->group(function () {
        Route::post('/campaigns', [CampaignController::class, 'store'])
            ->middleware('idempotency')
            ->name('campaigns.store');
        Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])
            ->name('campaigns.update');
        Route::post('/campaigns/{campaign}/submit', [CampaignController::class, 'submit'])
            ->name('campaigns.submit');
    });

    // ── Campaigns (moderator+) ───────────────────────────────────────
    Route::middleware(['enforce.role:moderator,admin'])->group(function () {
        Route::post('/campaigns/{campaign}/approve', [CampaignController::class, 'approve'])
            ->name('campaigns.approve');
        Route::post('/campaigns/{campaign}/reject', [CampaignController::class, 'reject'])
            ->name('campaigns.reject');
        Route::post('/campaigns/{campaign}/visibility', [CampaignController::class, 'visibility'])
            ->name('campaigns.visibility');
        Route::post('/campaigns/{campaign}/close', [CampaignController::class, 'close'])
            ->name('campaigns.close');
    });

    // ── Venue Programs (moderator/admin) ─────────────────────────────
    Route::middleware(['enforce.role:moderator,admin'])->group(function () {
        Route::post('/programs', [VenueProgramController::class, 'store'])
            ->middleware('idempotency')
            ->name('programs.store');
        Route::put('/programs/{program}', [VenueProgramController::class, 'update'])
            ->name('programs.update');
        Route::post('/programs/{program}/submit', [VenueProgramController::class, 'submit'])
            ->name('programs.submit');
        Route::post('/programs/{program}/approve', [VenueProgramController::class, 'approve'])
            ->name('programs.approve');
        Route::post('/programs/{program}/reject', [VenueProgramController::class, 'reject'])
            ->name('programs.reject');
        Route::post('/programs/{program}/visibility', [VenueProgramController::class, 'visibility'])
            ->name('programs.visibility');
    });

    // ── Booking / Time Slots ─────────────────────────────────────────
    Route::get('/time-slots/{timeSlot}', [BookingController::class, 'showTimeSlot'])
        ->name('time-slots.show');
    Route::post('/time-slots/{timeSlot}/lock', [BookingController::class, 'lock'])
        ->middleware('idempotency')
        ->name('time-slots.lock');
    Route::delete('/seat-locks/{seatLock}', [BookingController::class, 'releaseLock'])
        ->name('seat-locks.release');
    Route::post('/seat-locks/{seatLock}/confirm', [BookingController::class, 'confirm'])
        ->middleware('idempotency')
        ->name('seat-locks.confirm');

    // ── Orders ───────────────────────────────────────────────────────
    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->name('orders.show');
    Route::post('/orders', [OrderController::class, 'store'])
        ->middleware('idempotency')
        ->name('orders.store');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])
        ->name('orders.cancel');

    // ── Orders (staff+) ──────────────────────────────────────────────
    Route::middleware(['enforce.role:staff,moderator,admin'])->group(function () {
        Route::post('/orders/{order}/fulfill', [OrderController::class, 'fulfill'])
            ->name('orders.fulfill');
        Route::post('/orders/{order}/attend', [OrderController::class, 'attend'])
            ->name('orders.attend');
    });

    // ── Logistics Milestones ─────────────────────────────────────────
    Route::get('/orders/{order}/milestones', [LogisticsMilestoneController::class, 'index'])
        ->name('orders.milestones.index');
    Route::middleware(['enforce.role:staff,moderator,admin'])->group(function () {
        Route::post('/orders/{order}/milestones', [LogisticsMilestoneController::class, 'store'])
            ->name('orders.milestones.store');
        Route::put('/milestones/{milestone}', [LogisticsMilestoneController::class, 'update'])
            ->name('milestones.update');
    });

    // ── Payments (staff+) ────────────────────────────────────────────
    Route::middleware(['enforce.role:staff,moderator,admin'])->group(function () {
        Route::post('/orders/{order}/payments', [PaymentController::class, 'store'])
            ->middleware('idempotency')
            ->name('orders.payments.store');
    });

    // ── Refunds ──────────────────────────────────────────────────────
    Route::post('/orders/{order}/refunds', [RefundController::class, 'store'])
        ->middleware('idempotency')
        ->name('orders.refunds.store');
    Route::middleware(['enforce.role:staff,moderator,admin'])->group(function () {
        Route::post('/refund-requests/{refundRequest}/approve', [RefundController::class, 'approve'])
            ->name('refund-requests.approve');
        Route::post('/refund-requests/{refundRequest}/reject', [RefundController::class, 'reject'])
            ->name('refund-requests.reject');
    });

    // ── After-Sales ──────────────────────────────────────────────────
    Route::post('/orders/{order}/after-sales', [AfterSalesController::class, 'store'])
        ->middleware('idempotency')
        ->name('orders.after-sales.store');
    Route::middleware(['enforce.role:staff,moderator,admin'])->group(function () {
        Route::post('/after-sales/{afterSalesRequest}/review', [AfterSalesController::class, 'review'])
            ->name('after-sales.review');
        Route::post('/after-sales/{afterSalesRequest}/resolve', [AfterSalesController::class, 'resolve'])
            ->name('after-sales.resolve');
    });

    // ── Vouchers ─────────────────────────────────────────────────────
    Route::get('/vouchers', [VoucherController::class, 'index'])
        ->name('vouchers.index');
    Route::get('/vouchers/{voucher}', [VoucherController::class, 'show'])
        ->name('vouchers.show');
    Route::middleware(['enforce.role:staff,moderator,admin'])->group(function () {
        Route::post('/vouchers/{voucher}/redeem', [VoucherController::class, 'redeem'])
            ->name('vouchers.redeem');
    });

    // ── Reviews ──────────────────────────────────────────────────────
    Route::post('/orders/{order}/reviews', [ReviewController::class, 'store'])
        ->middleware('idempotency')
        ->name('orders.reviews.store');

    // ── Notifications ────────────────────────────────────────────────
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unread-count');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    // ── Disputes ─────────────────────────────────────────────────────
    Route::get('/disputes', [DisputeController::class, 'index'])
        ->name('disputes.index');
    Route::get('/disputes/{dispute}', [DisputeController::class, 'show'])
        ->name('disputes.show');
    Route::post('/orders/{order}/disputes', [DisputeController::class, 'store'])
        ->middleware('idempotency')
        ->name('orders.disputes.store');
    Route::middleware(['enforce.role:moderator,admin'])->group(function () {
        Route::post('/disputes/{dispute}/assign', [DisputeController::class, 'assign'])
            ->name('disputes.assign');
        Route::post('/disputes/{dispute}/decide', [DisputeController::class, 'decide'])
            ->name('disputes.decide');
    });

    // ── Risk Control (moderator+) ────────────────────────────────────
    Route::middleware(['enforce.role:moderator,admin'])->group(function () {
        Route::get('/risk/credit-scores', [RiskControlController::class, 'creditScoreList'])
            ->name('risk.credit-scores.index');
        Route::get('/risk/credit-scores/{user}', [RiskControlController::class, 'creditScore'])
            ->name('risk.credit-scores.show');
        Route::get('/risk/anomalies', [RiskControlController::class, 'anomalies'])
            ->name('risk.anomalies.index');
        Route::post('/risk/anomalies/{anomalyFlag}/resolve', [RiskControlController::class, 'resolveAnomaly'])
            ->name('risk.anomalies.resolve');
    });

    // ── Risk Control — chargebacks (staff+) ──────────────────────────
    Route::middleware(['enforce.role:staff,moderator,admin'])->group(function () {
        Route::post('/risk/chargebacks', [RiskControlController::class, 'recordChargeback'])
            ->middleware('idempotency')
            ->name('risk.chargebacks.store');
    });

    // ── Admin ────────────────────────────────────────────────────────
    Route::middleware(['enforce.role:admin'])->prefix('admin')->group(function () {
        Route::get('/roles', [AdminController::class, 'listRoles'])
            ->name('admin.roles.index');
        Route::get('/users', [AdminController::class, 'listUsers'])
            ->name('admin.users.index');
        Route::post('/users', [AdminController::class, 'createUser'])
            ->middleware('idempotency')
            ->name('admin.users.store');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])
            ->name('admin.users.update');
        Route::get('/business-parameters', [AdminController::class, 'listBusinessParameters'])
            ->name('admin.business-parameters.index');
        Route::put('/business-parameters/{key}', [AdminController::class, 'updateBusinessParameter'])
            ->name('admin.business-parameters.update');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])
            ->name('admin.audit-logs.index');

        // Integration stubs
        Route::get('/integration-stubs', [IntegrationStubController::class, 'index'])
            ->name('admin.integration-stubs.index');
        Route::get('/integration-stubs/{integrationStub}', [IntegrationStubController::class, 'show'])
            ->name('admin.integration-stubs.show');
        Route::put('/integration-stubs/{integrationStub}', [IntegrationStubController::class, 'update'])
            ->name('admin.integration-stubs.update');

        // Webhook definitions
        Route::get('/webhook-definitions', [WebhookDefinitionController::class, 'index'])
            ->name('admin.webhook-definitions.index');
        Route::post('/webhook-definitions', [WebhookDefinitionController::class, 'store'])
            ->middleware('idempotency')
            ->name('admin.webhook-definitions.store');
        Route::put('/webhook-definitions/{webhookDefinition}', [WebhookDefinitionController::class, 'update'])
            ->name('admin.webhook-definitions.update');
        Route::delete('/webhook-definitions/{webhookDefinition}', [WebhookDefinitionController::class, 'destroy'])
            ->name('admin.webhook-definitions.destroy');
    });
});
