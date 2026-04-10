<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Web\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\Web\AuthController::class, 'login']);
});

// Public browsing
Route::get('/campaigns', App\Livewire\Campaign\CampaignList::class)->name('campaigns.list');
Route::get('/campaigns/{campaignId}', App\Livewire\Campaign\CampaignDetail::class)->name('campaigns.detail');
Route::get('/programs', App\Livewire\Campaign\VenueProgramList::class)->name('programs.list');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Web\AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => view('pages.dashboard'))->name('dashboard');

    // Campaign management
    Route::get('/campaigns/create', App\Livewire\Campaign\CampaignForm::class)->name('campaigns.create');
    Route::get('/campaigns/{campaignId}/edit', App\Livewire\Campaign\CampaignForm::class)->name('campaigns.edit');

    // Booking
    Route::get('/booking/{timeSlotId}', App\Livewire\Booking\SeatMap::class)->name('booking.seat-map');

    // Orders
    Route::get('/orders', App\Livewire\Order\OrderList::class)->name('orders.list');
    Route::get('/orders/{orderId}', App\Livewire\Order\OrderDetail::class)->name('orders.detail');

    // Vouchers
    Route::get('/vouchers', App\Livewire\Voucher\VoucherList::class)->name('vouchers.list');
    Route::get('/vouchers/{voucherId}', App\Livewire\Voucher\VoucherDisplay::class)->name('vouchers.detail');

    // Notifications
    Route::get('/notifications', App\Livewire\Notification\NotificationInbox::class)->name('notifications.inbox');

    // Reviews
    Route::get('/orders/{orderId}/review', App\Livewire\Review\ReviewForm::class)->name('reviews.create');

    // Moderation
    Route::middleware('enforce.role:moderator,admin')->group(function () {
        Route::get('/moderation/campaigns', App\Livewire\Campaign\CampaignApprovalQueue::class)->name('moderation.campaigns');
        Route::get('/moderation/arbitration', App\Livewire\RiskControl\ArbitrationQueue::class)->name('moderation.arbitration');
    });

    // Admin
    Route::middleware('enforce.role:admin')->prefix('admin')->group(function () {
        Route::get('/users', App\Livewire\Admin\UserManager::class)->name('admin.users');
        Route::get('/parameters', App\Livewire\Admin\BusinessParameterEditor::class)->name('admin.parameters');
        Route::get('/audit-logs', App\Livewire\Admin\AuditLogViewer::class)->name('admin.audit-logs');
        Route::get('/risk', App\Livewire\RiskControl\CreditScorePanel::class)->name('admin.risk');
    });
});
