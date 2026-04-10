<?php

return [
    'booking_confirmation' => [
        'title' => 'Booking Confirmed',
        'body' => 'Your booking #:confirmation_number for :event has been confirmed for :date.',
    ],
    'order_paid' => [
        'title' => 'Payment Received',
        'body' => 'Payment of :amount has been recorded for order #:confirmation_number.',
    ],
    'order_cancelled' => [
        'title' => 'Order Cancelled',
        'body' => 'Your order #:confirmation_number has been cancelled. Reason: :reason',
    ],
    'order_refunded' => [
        'title' => 'Order Refunded',
        'body' => 'A refund of :amount has been processed for order #:confirmation_number.',
    ],
    'order_fulfilled' => [
        'title' => 'Order Fulfilled',
        'body' => 'Your order #:confirmation_number has been fulfilled.',
    ],
    'campaign_approved' => [
        'title' => 'Campaign Approved',
        'body' => 'Your campaign ":title" has been approved and is now live.',
    ],
    'campaign_rejected' => [
        'title' => 'Campaign Rejected',
        'body' => 'Your campaign ":title" has been rejected. Reason: :reason',
    ],
    'campaign_failed' => [
        'title' => 'Campaign Did Not Reach Goal',
        'body' => 'The campaign ":title" did not reach its funding goal. Your contribution is eligible for a refund.',
    ],
    'refund_approved' => [
        'title' => 'Refund Approved',
        'body' => 'Your refund request for order #:confirmation_number has been approved.',
    ],
    'refund_rejected' => [
        'title' => 'Refund Rejected',
        'body' => 'Your refund request for order #:confirmation_number has been rejected.',
    ],
    'voucher_ready' => [
        'title' => 'Voucher Ready',
        'body' => 'Your voucher for order #:confirmation_number is now available. Code: :code',
    ],
    'booking_reminder' => [
        'title' => 'Upcoming Booking Reminder',
        'body' => 'Reminder: Your booking #:confirmation_number is in :hours hours at :venue.',
    ],
    'dispute_decided' => [
        'title' => 'Dispute Decision',
        'body' => 'A decision has been made on your dispute: :decision',
    ],
    'anomaly_detected' => [
        'title' => 'Anomaly Detected',
        'body' => 'An anomaly has been flagged for user :username. Type: :type. Review required.',
    ],
];
