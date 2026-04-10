<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'campaign_id',
        'venue_program_id',
        'reward_tier_id',
        'time_slot_id',
        'request_key',
        'confirmation_number',
        'order_type',
        'seat_quantity',
        'amount',
        'currency',
        'status',
        'fulfilled_at',
        'cancelled_at',
        'cancellation_reason',
        'refund_deadline',
        'attended',
        'has_pending_refund',
        'has_pending_after_sales',
    ];

    protected $appends = ['can_cancel', 'can_refund'];

    protected function casts(): array
    {
        return [
            'order_type' => OrderType::class,
            'status' => OrderStatus::class,
            'amount' => 'integer',
            'fulfilled_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refund_deadline' => 'date',
            'attended' => 'boolean',
            'has_pending_refund' => 'boolean',
            'has_pending_after_sales' => 'boolean',
        ];
    }

    public function getCanCancelAttribute(): bool
    {
        return $this->canCancel();
    }

    public function getCanRefundAttribute(): bool
    {
        return $this->canRefund();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function venueProgram(): BelongsTo
    {
        return $this->belongsTo(VenueProgram::class);
    }

    public function rewardTier(): BelongsTo
    {
        return $this->belongsTo(RewardTier::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function afterSalesRequests(): HasMany
    {
        return $this->hasMany(AfterSalesRequest::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    public function logisticsMilestones(): HasMany
    {
        return $this->hasMany(LogisticsMilestone::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function canCancel(): bool
    {
        if ($this->status !== OrderStatus::Confirmed || $this->cancelled_at !== null) {
            return false;
        }

        // Check cancellation window from business parameters
        $windowHours = \App\Models\BusinessParameter::where('key', 'cancellation_window_hours')
            ->first()?->getTypedValue() ?? 2;

        // For reservation orders with a time slot, check if we're within the window before the event
        if ($this->time_slot_id && $this->timeSlot) {
            $eventStart = $this->timeSlot->starts_at;
            if ($eventStart && now()->diffInHours($eventStart, false) < $windowHours) {
                return false;
            }
        }

        return true;
    }

    public function canRefund(): bool
    {
        return $this->status === OrderStatus::Fulfilled
            && $this->refund_deadline !== null
            && $this->refund_deadline->isFuture()
            && ! $this->has_pending_refund;
    }
}
