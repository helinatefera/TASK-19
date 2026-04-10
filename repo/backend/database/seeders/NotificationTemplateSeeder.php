<?php

namespace Database\Seeders;

use App\Enums\NotificationType;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'campaign.approved',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Campaign Approved', 'body' => "Your campaign '{{campaign_title}}' has been approved."],
                    'es' => ['title' => 'Campaña Aprobada', 'body' => "Su campaña '{{campaign_title}}' ha sido aprobada."],
                ],
            ],
            [
                'key' => 'campaign.rejected',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Campaign Rejected', 'body' => "Your campaign '{{campaign_title}}' has been rejected. Reason: {{reason}}"],
                    'es' => ['title' => 'Campaña Rechazada', 'body' => "Su campaña '{{campaign_title}}' ha sido rechazada. Razón: {{reason}}"],
                ],
            ],
            [
                'key' => 'campaign.failed',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Campaign Failed', 'body' => "The campaign '{{campaign_title}}' did not reach its funding goal."],
                    'es' => ['title' => 'Campaña Fallida', 'body' => "La campaña '{{campaign_title}}' no alcanzó su meta de financiamiento."],
                ],
            ],
            [
                'key' => 'campaign.failed.contributor',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Campaign You Supported Did Not Reach Its Goal', 'body' => "The campaign '{{campaign_title}}' that you contributed to did not reach its funding goal."],
                    'es' => ['title' => 'Campaña que Apoyó No Alcanzó su Meta', 'body' => "La campaña '{{campaign_title}}' a la que contribuyó no alcanzó su meta."],
                ],
            ],
            [
                'key' => 'order.created',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Order Created', 'body' => 'Your order #{{confirmation_number}} has been created.'],
                    'es' => ['title' => 'Pedido Creado', 'body' => 'Su pedido #{{confirmation_number}} ha sido creado.'],
                ],
            ],
            [
                'key' => 'order.paid',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Payment Confirmed', 'body' => 'Payment of {{amount}} has been recorded for order #{{confirmation_number}}.'],
                    'es' => ['title' => 'Pago Confirmado', 'body' => 'Se ha registrado un pago de {{amount}} para el pedido #{{confirmation_number}}.'],
                ],
            ],
            [
                'key' => 'order.fulfilled',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Order Fulfilled', 'body' => 'Your order #{{confirmation_number}} has been fulfilled.'],
                    'es' => ['title' => 'Pedido Completado', 'body' => 'Su pedido #{{confirmation_number}} ha sido completado.'],
                ],
            ],
            [
                'key' => 'order.cancelled',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Order Cancelled', 'body' => 'Your order #{{confirmation_number}} has been cancelled.'],
                    'es' => ['title' => 'Pedido Cancelado', 'body' => 'Su pedido #{{confirmation_number}} ha sido cancelado.'],
                ],
            ],
            [
                'key' => 'order.refunded',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Refund Processed', 'body' => 'A refund of {{amount}} has been processed for order #{{confirmation_number}}.'],
                    'es' => ['title' => 'Reembolso Procesado', 'body' => 'Se ha procesado un reembolso de {{amount}} para el pedido #{{confirmation_number}}.'],
                ],
            ],
            [
                'key' => 'booking.confirmed',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Booking Confirmed', 'body' => 'Your booking #{{confirmation_number}} is confirmed for {{event_date}}.'],
                    'es' => ['title' => 'Reserva Confirmada', 'body' => 'Su reserva #{{confirmation_number}} está confirmada para {{event_date}}.'],
                ],
            ],
            [
                'key' => 'booking.reminder',
                'type' => NotificationType::Alert,
                'locales' => [
                    'en' => ['title' => 'Upcoming Event', 'body' => 'Reminder: Your event is starting at {{event_time}}.'],
                    'es' => ['title' => 'Evento Próximo', 'body' => 'Recordatorio: Su evento comienza a las {{event_time}}.'],
                ],
            ],
            [
                'key' => 'voucher.generated',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Voucher Ready', 'body' => 'Your voucher code {{voucher_code}} is ready.'],
                    'es' => ['title' => 'Voucher Listo', 'body' => 'Su código de voucher {{voucher_code}} está listo.'],
                ],
            ],
            [
                'key' => 'refund.approved',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Refund Approved', 'body' => 'Your refund request for order #{{confirmation_number}} has been approved.'],
                    'es' => ['title' => 'Reembolso Aprobado', 'body' => 'Su solicitud de reembolso para el pedido #{{confirmation_number}} ha sido aprobada.'],
                ],
            ],
            [
                'key' => 'refund.rejected',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Refund Rejected', 'body' => 'Your refund request for order #{{confirmation_number}} has been rejected.'],
                    'es' => ['title' => 'Reembolso Rechazado', 'body' => 'Su solicitud de reembolso para el pedido #{{confirmation_number}} ha sido rechazada.'],
                ],
            ],
            [
                'key' => 'anomaly.detected',
                'type' => NotificationType::Alert,
                'locales' => [
                    'en' => ['title' => 'Review Needed', 'body' => 'An anomaly has been detected for user {{username}}.'],
                    'es' => ['title' => 'Revisión Necesaria', 'body' => 'Se ha detectado una anomalía para el usuario {{username}}.'],
                ],
            ],
            [
                'key' => 'arbitration.decided',
                'type' => NotificationType::Inbox,
                'locales' => [
                    'en' => ['title' => 'Dispute Decision', 'body' => 'A decision has been made on your dispute #{{dispute_id}}.'],
                    'es' => ['title' => 'Decisión de Disputa', 'body' => 'Se ha tomado una decisión sobre su disputa #{{dispute_id}}.'],
                ],
            ],
        ];

        foreach ($templates as $template) {
            foreach ($template['locales'] as $locale => $content) {
                NotificationTemplate::updateOrCreate(
                    ['key' => $template['key'], 'locale' => $locale],
                    [
                        'title_template' => $content['title'],
                        'body_template' => $content['body'],
                        'type' => $template['type'],
                        'is_active' => true,
                        'requires_approval' => false,
                    ],
                );
            }
        }
    }
}
