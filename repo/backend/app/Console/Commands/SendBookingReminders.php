<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\BusinessParameter;
use App\Models\Notification;
use App\Models\Order;
use App\Models\TimeSlot;
use App\Services\Notification\NotificationService;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';

    protected $description = 'Send booking reminder notifications for upcoming time slots';

    private const DEFAULT_LEAD_HOURS = '24,2';

    private const REMINDER_TEMPLATE_KEY = 'booking.reminder';

    public function handle(NotificationService $notificationService): int
    {
        $leadHoursParam = BusinessParameter::where('key', 'reminder_lead_hours')->first();
        $leadHoursString = $leadHoursParam ? $leadHoursParam->getTypedValue() : self::DEFAULT_LEAD_HOURS;

        $leadHours = array_map('trim', explode(',', $leadHoursString));
        $leadHours = array_filter($leadHours, fn ($h) => is_numeric($h));

        $totalSent = 0;

        foreach ($leadHours as $hours) {
            $hours = (float) $hours;

            $windowStart = now()->addMinutes((int) ($hours * 60) - 7);
            $windowEnd = now()->addMinutes((int) ($hours * 60) + 8);

            $timeSlots = TimeSlot::query()
                ->where('starts_at', '>=', $windowStart)
                ->where('starts_at', '<=', $windowEnd)
                ->get();

            if ($timeSlots->isEmpty()) {
                continue;
            }

            $orders = Order::query()
                ->whereIn('time_slot_id', $timeSlots->pluck('id'))
                ->where('status', OrderStatus::Confirmed)
                ->where('order_type', OrderType::Reservation)
                ->with(['user', 'timeSlot'])
                ->get();

            foreach ($orders as $order) {
                // Check if a reminder notification was already sent for this order and lead time
                $alreadySent = Notification::query()
                    ->where('user_id', $order->user_id)
                    ->where('data->order_id', $order->id)
                    ->where('data->lead_hours', $hours)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $notificationService->dispatch($order->user, self::REMINDER_TEMPLATE_KEY, [
                    'order_id' => $order->id,
                    'confirmation_number' => $order->confirmation_number,
                    'starts_at' => $order->timeSlot->starts_at->toIso8601String(),
                    'lead_hours' => $hours,
                ]);

                $totalSent++;
            }
        }

        $this->info("Sent {$totalSent} booking reminder(s).");

        return self::SUCCESS;
    }
}
