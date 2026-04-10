<?php

namespace Database\Seeders;

use App\Models\BusinessParameter;
use Illuminate\Database\Seeder;

class BusinessParametersSeeder extends Seeder
{
    public function run(): void
    {
        $parameters = [
            [
                'key' => 'cancellation_window_hours',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Hours after order creation during which cancellation is allowed',
            ],
            [
                'key' => 'refund_window_days',
                'value' => '14',
                'type' => 'integer',
                'description' => 'Days after fulfillment during which refund requests are accepted',
            ],
            [
                'key' => 'seat_lock_ttl_minutes',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Minutes a seat lock is held before automatic expiration',
            ],
            [
                'key' => 'reminder_lead_hours',
                'value' => '24,2',
                'type' => 'string',
                'description' => 'Comma-separated hours before event to send reminders',
            ],
            [
                'key' => 'notification_retention_days',
                'value' => '90',
                'type' => 'integer',
                'description' => 'Days to retain notifications before pruning',
            ],
            [
                'key' => 'session_timeout_staff_minutes',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Session timeout in minutes for staff users',
            ],
            [
                'key' => 'session_timeout_user_minutes',
                'value' => '120',
                'type' => 'integer',
                'description' => 'Session timeout in minutes for regular users',
            ],
            [
                'key' => 'login_max_attempts',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Maximum login attempts before lockout',
            ],
            [
                'key' => 'login_lockout_minutes',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Minutes to lock account after exceeding max login attempts',
            ],
            [
                'key' => 'anomaly_refund_threshold',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Anomaly flag raised when refund count within window exceeds this value (> comparison)',
            ],
            [
                'key' => 'anomaly_refund_window_days',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Rolling window in days for refund anomaly detection',
            ],
            [
                'key' => 'blacklist_duration_days',
                'value' => '90',
                'type' => 'integer',
                'description' => 'Days a blacklisted user remains restricted',
            ],
            [
                'key' => 'credit_score_no_show_penalty',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Credit score penalty for a no-show',
            ],
            [
                'key' => 'credit_score_chargeback_penalty',
                'value' => '100',
                'type' => 'integer',
                'description' => 'Credit score penalty for a chargeback',
            ],
            [
                'key' => 'credit_score_refund_penalty',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Credit score penalty for a refund',
            ],
            [
                'key' => 'credit_score_violation_penalty',
                'value' => '75',
                'type' => 'integer',
                'description' => 'Credit score penalty for a violation',
            ],
            [
                'key' => 'credit_score_gray_threshold',
                'value' => '600',
                'type' => 'integer',
                'description' => 'Credit score at or below which a user is graylisted',
            ],
            [
                'key' => 'credit_score_black_threshold',
                'value' => '300',
                'type' => 'integer',
                'description' => 'Credit score at or below which a user is blacklisted',
            ],
            [
                'key' => 'voucher_grace_period_hours',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Grace period in hours after voucher expiration during which redemption is still allowed',
            ],
            [
                'key' => 'review_visibility_delay_hours',
                'value' => '72',
                'type' => 'integer',
                'description' => 'Hours to delay before making a review publicly visible',
            ],
            [
                'key' => 'campaign_min_duration_days',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Minimum allowed campaign duration in days',
            ],
            [
                'key' => 'campaign_max_duration_days',
                'value' => '60',
                'type' => 'integer',
                'description' => 'Maximum allowed campaign duration in days',
            ],
            [
                'key' => 'max_upload_size_mb',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Maximum file upload size in megabytes',
            ],
        ];

        foreach ($parameters as $param) {
            BusinessParameter::updateOrCreate(
                ['key' => $param['key']],
                [
                    'value' => $param['value'],
                    'type' => $param['type'],
                    'description' => $param['description'],
                ],
            );
        }
    }
}
