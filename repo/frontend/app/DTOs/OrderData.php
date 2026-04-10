<?php

namespace App\DTOs;

class OrderData
{
    public function __construct(
        public readonly int $id,
        public readonly string $order_type,
        public readonly string $status,
        public readonly string $amount,
        public readonly ?string $confirmation_number,
        public readonly ?int $seat_quantity,
        public readonly bool $attended,
        public readonly bool $has_pending_refund,
        public readonly bool $has_pending_after_sales,
        public readonly ?array $campaign,
        public readonly ?array $voucher,
        public readonly ?array $payment,
        public readonly array $refund_requests,
        public readonly array $after_sales_requests,
        public readonly array $logistics_milestones,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            order_type: $data['order_type'],
            status: $data['status'],
            amount: $data['amount'],
            confirmation_number: $data['confirmation_number'] ?? null,
            seat_quantity: $data['seat_quantity'] ?? null,
            attended: $data['attended'] ?? false,
            has_pending_refund: $data['has_pending_refund'] ?? false,
            has_pending_after_sales: $data['has_pending_after_sales'] ?? false,
            campaign: $data['campaign'] ?? null,
            voucher: $data['voucher'] ?? null,
            payment: $data['payment'] ?? null,
            refund_requests: $data['refund_requests'] ?? [],
            after_sales_requests: $data['after_sales_requests'] ?? [],
            logistics_milestones: $data['logistics_milestones'] ?? [],
        );
    }
}
