<?php

namespace App\Listeners\Audit;

use App\Events\AnomalyDetected;
use App\Events\BookingConfirmed;
use App\Events\CampaignApproved;
use App\Events\CampaignFailed;
use App\Events\CampaignRejected;
use App\Events\ChargebackRecorded;
use App\Events\DisputeDecided;
use App\Events\NoShowDetected;
use App\Events\OrderCancelled;
use App\Events\OrderCreated;
use App\Events\OrderFulfilled;
use App\Events\OrderPaid;
use App\Events\OrderRefunded;
use App\Events\RefundApproved;
use App\Events\RefundRejected;
use App\Events\ReviewSubmitted;
use App\Events\VoucherGenerated;
use App\Services\Audit\AuditLogService;

class WriteAuditLog
{
    public function handle(object $event): void
    {
        try {
            $this->writeLog($event);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function writeLog(object $event): void
    {
        $auditLogService = app(AuditLogService::class);

        [$action, $actor, $auditableType, $auditableId, $oldValues, $newValues] = match (true) {
            $event instanceof BookingConfirmed => [
                'booking_confirmed',
                $event->order->user ?? null,
                'Order',
                $event->order->id,
                null,
                ['order_id' => $event->order->id, 'seat_lock_id' => $event->seatLock->id],
            ],
            $event instanceof OrderCreated => [
                'order_created',
                $event->order->user ?? null,
                'Order',
                $event->order->id,
                null,
                ['status' => $event->order->status],
            ],
            $event instanceof OrderPaid => [
                'order_paid',
                $event->order->user ?? null,
                'Order',
                $event->order->id,
                null,
                ['payment_id' => $event->payment->id, 'amount' => $event->payment->amount],
            ],
            $event instanceof OrderCancelled => [
                'order_cancelled',
                $event->order->user ?? null,
                'Order',
                $event->order->id,
                ['status' => 'confirmed'],
                ['status' => 'cancelled', 'reason' => $event->reason],
            ],
            $event instanceof OrderRefunded => [
                'order_refunded',
                $event->refundRequest->reviewer ?? null,
                'Order',
                $event->order->id,
                null,
                ['refund_request_id' => $event->refundRequest->id],
            ],
            $event instanceof OrderFulfilled => [
                'order_fulfilled',
                $event->order->user ?? null,
                'Order',
                $event->order->id,
                ['status' => 'confirmed'],
                ['status' => 'fulfilled'],
            ],
            $event instanceof CampaignApproved => [
                'campaign_approved',
                $event->reviewer,
                'Campaign',
                $event->campaign->id,
                ['status' => 'pending_review'],
                ['status' => 'fundraising'],
            ],
            $event instanceof CampaignRejected => [
                'campaign_rejected',
                $event->reviewer,
                'Campaign',
                $event->campaign->id,
                ['status' => 'pending_review'],
                ['status' => 'draft', 'reason' => $event->reason],
            ],
            $event instanceof CampaignFailed => [
                'campaign_failed',
                null,
                'Campaign',
                $event->campaign->id,
                ['status' => 'fundraising'],
                ['status' => 'failure'],
            ],
            $event instanceof RefundApproved => [
                'refund_approved',
                $event->reviewer,
                'RefundRequest',
                $event->refundRequest->id,
                ['status' => 'pending'],
                ['status' => 'approved'],
            ],
            $event instanceof RefundRejected => [
                'refund_rejected',
                $event->reviewer,
                'RefundRequest',
                $event->refundRequest->id,
                ['status' => 'pending'],
                ['status' => 'rejected'],
            ],
            $event instanceof ReviewSubmitted => [
                'review_submitted',
                $event->review->reviewer ?? null,
                'Review',
                $event->review->id,
                null,
                ['order_id' => $event->review->order_id, 'rating' => $event->review->rating ?? null],
            ],
            $event instanceof VoucherGenerated => [
                'voucher_generated',
                $event->order->user ?? null,
                'Voucher',
                $event->voucher->id,
                null,
                ['order_id' => $event->order->id, 'code' => $event->voucher->code],
            ],
            $event instanceof AnomalyDetected => [
                'anomaly_detected',
                null,
                'AnomalyFlag',
                $event->anomalyFlag->id,
                null,
                ['type' => $event->anomalyFlag->type, 'user_id' => $event->anomalyFlag->user_id],
            ],
            $event instanceof ChargebackRecorded => [
                'chargeback_recorded',
                $event->recorder,
                'Order',
                $event->order->id,
                null,
                ['recorded_by' => $event->recorder->id],
            ],
            $event instanceof NoShowDetected => [
                'no_show_detected',
                null,
                'Order',
                $event->order->id,
                null,
                ['order_id' => $event->order->id],
            ],
            $event instanceof DisputeDecided => [
                'dispute_decided',
                null,
                'Dispute',
                $event->dispute->id,
                null,
                ['decision_id' => $event->decision->id],
            ],
            default => [
                class_basename($event),
                null,
                'Unknown',
                0,
                null,
                null,
            ],
        };

        $auditLogService->log(
            action: $action,
            actor: $actor,
            auditableType: $auditableType,
            auditableId: $auditableId,
            oldValues: $oldValues,
            newValues: $newValues,
        );
    }
}
