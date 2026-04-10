<?php

namespace App\Services\Booking;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Events\BookingConfirmed;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\SeatLock;
use App\Models\VenueProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class BookingService
{
    public function __construct(
        private readonly SeatLockService $seatLockService,
    ) {}

    public function confirm(SeatLock $lock, array $data): Order
    {
        return DB::transaction(function () use ($lock, $data) {
            $lock->refresh();

            if (! $this->seatLockService->isValid($lock)) {
                throw new RuntimeException('Seat lock is no longer valid.');
            }

            $timeSlot = $lock->timeSlot;
            $requestKey = $data['request_key'] ?? Str::uuid()->toString();

            // Check for idempotent request
            $existing = Order::where('request_key', $requestKey)->first();
            if ($existing) {
                return $existing;
            }

            // Determine amount from reward tier if not explicitly provided
            $amount = $data['amount'] ?? 0;
            if ($amount <= 0 && $timeSlot->programable_type === Campaign::class) {
                $campaign = Campaign::find($timeSlot->programable_id);
                if ($campaign) {
                    $tier = $campaign->rewardTiers()->orderBy('price')->first();
                    if ($tier && $tier->price > 0) {
                        $amount = $tier->price;
                    }
                }
            }

            // Determine related campaign/program
            $campaignId = null;
            $venueProgramId = null;
            if ($timeSlot->programable_type === Campaign::class) {
                $campaignId = $timeSlot->programable_id;
            } elseif ($timeSlot->programable_type === VenueProgram::class) {
                $venueProgramId = $timeSlot->programable_id;
            }

            $order = Order::create([
                'user_id' => $lock->user_id,
                'campaign_id' => $campaignId,
                'venue_program_id' => $venueProgramId,
                'time_slot_id' => $timeSlot->id,
                'request_key' => $requestKey,
                'confirmation_number' => $this->generateConfirmationNumber(),
                'order_type' => OrderType::Reservation,
                'seat_quantity' => $lock->quantity,
                'amount' => $amount,
                'currency' => $data['currency'] ?? 'USD',
                'status' => OrderStatus::Confirmed,
            ]);

            $timeSlot->increment('seats_booked', $lock->quantity);

            $this->seatLockService->release($lock);

            BookingConfirmed::dispatch($order, $lock);

            return $order;
        });
    }

    public function generateConfirmationNumber(): string
    {
        $datePart = now()->format('ymd');
        $randomPart = strtoupper(Str::random(8));

        return "CC-{$datePart}-{$randomPart}";
    }
}
