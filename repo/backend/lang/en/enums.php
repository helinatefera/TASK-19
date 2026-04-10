<?php

return [
    'campaign_status' => [
        'draft' => 'Draft',
        'pending_review' => 'Pending Review',
        'published' => 'Published',
        'fundraising' => 'Fundraising',
        'success' => 'Successful',
        'failure' => 'Failed',
        'closed' => 'Closed',
    ],
    'campaign_visibility' => [
        'online' => 'Online',
        'offline' => 'Offline',
    ],
    'order_status' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'fulfilled' => 'Fulfilled',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
        'after_sales' => 'After-Sales',
    ],
    'order_type' => [
        'contribution' => 'Contribution',
        'reservation' => 'Reservation',
    ],
    'payment_method' => [
        'cash' => 'Cash',
        'card_on_file' => 'Card on File',
    ],
    'payment_status' => [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'failed' => 'Failed',
    ],
    'voucher_status' => [
        'active' => 'Active',
        'redeemed' => 'Redeemed',
        'expired' => 'Expired',
        'revoked' => 'Revoked',
    ],
    'review_side' => [
        'user_to_creator' => 'User to Creator',
        'creator_to_user' => 'Creator to User',
    ],
    'notification_type' => [
        'inbox' => 'Inbox',
        'alert' => 'Alert',
    ],
    'refund_status' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],
    'after_sales_status' => [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],
    'after_sales_type' => [
        'damaged' => 'Damaged',
        'defective' => 'Defective',
        'missing' => 'Missing',
        'unsatisfactory' => 'Unsatisfactory',
    ],
    'dispute_status' => [
        'open' => 'Open',
        'under_review' => 'Under Review',
        'resolved' => 'Resolved',
        'escalated' => 'Escalated',
    ],
    'restriction_level' => [
        'none' => 'None',
        'gray' => 'Graylist',
        'black' => 'Blacklist',
    ],
    'milestone_status' => [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ],
    'fulfillment_type' => [
        'digital' => 'Digital',
        'physical' => 'Physical',
        'event' => 'Event',
    ],
    'user_role' => [
        'user' => 'User',
        'creator' => 'Creator',
        'moderator' => 'Moderator',
        'staff' => 'Staff',
        'admin' => 'Administrator',
    ],
];
