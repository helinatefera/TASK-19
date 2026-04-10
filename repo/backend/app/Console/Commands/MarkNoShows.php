<?php

namespace App\Console\Commands;

use App\Enums\OrderType;
use App\Models\Order;
use App\Services\RiskControl\CreditScoringService;
use Illuminate\Console\Command;

class MarkNoShows extends Command
{
    protected $signature = 'bookings:mark-no-shows';

    protected $description = 'Mark reservation orders as no-show when the event has ended and attendance was not recorded';

    public function handle(CreditScoringService $creditScoringService): int
    {
        $noShowOrders = Order::query()
            ->where('order_type', OrderType::Reservation)
            ->whereNull('attended')
            ->whereHas('timeSlot', function ($query) {
                $query->where('ends_at', '<', now());
            })
            ->with(['user', 'timeSlot'])
            ->get();

        $count = 0;

        foreach ($noShowOrders as $order) {
            $order->update(['attended' => false]);
            $creditScoringService->recordNoShow($order->user);
            \App\Events\NoShowDetected::dispatch($order);
            $count++;
        }

        $this->info("Marked {$count} order(s) as no-show.");

        return self::SUCCESS;
    }
}
